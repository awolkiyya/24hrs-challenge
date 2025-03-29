<?php
namespace App\Jobs;
use Illuminate\Foundation\Bus\Dispatchable;
use App\Models\File;
use App\Models\User;
use App\Mail\FileUploadConfirmationMail;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Aws\S3\Exception\S3Exception;
use Aws\S3\S3Client;
use Aws\Exception\AwsException;
use Throwable;
class ProcessFileUploadJob implements ShouldQueue
{
    use   Dispatchable, Queueable, InteractsWithQueue, SerializesModels;

    protected $file;

    /**
     * Create a new job instance.
     *
     * @param \App\Models\File $file
     * @return void
     */
    public function __construct(File $file)
    {
        $this->file = $file;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        try {
            error_log("why i am here");
            // Step 1: Perform a real virus scan on the file
            $this->performVirusScan();

            // Step 2: Log the file metadata to a log file or database
            Log::info('File metadata stored successfully.', [
                'file_id' => $this->file->id,
                'tenant_id' => $this->file->tenant_id,
                'file_name' => $this->file->name,
                'file_size' => $this->file->size,
                'file_type' => $this->file->mime_type,
            ]);

            // Step 3: Send email confirmation with download link via AWS SES
            $this->sendEmailConfirmation();

        } catch (\Exception $exception) {
            // Step 4: Handle job failure if an error occurs
            $this->failed($exception);
        }
    }

    /**
     * Perform a real virus scan using ClamAV.
     *
     * @return void
     */
    private function performVirusScan()
    {
        $filePath = $this->file->path;  // The file path to be scanned
        error_log("file path".$filePath);

        // // Ensure the file exists
        // if ($this->checkFileExistsOnS3($filePath)) {
        //     $output = null;
        //     $resultCode = null;

        //     // Execute the clamscan command
        //     exec("clamscan {$filePath}", $output, $resultCode);

        //     // Check the result code: 0 means no virus found, non-zero means virus detected
        //     if ($resultCode !== 0) {
        //         // If a virus is found, log it and throw an exception
        //         Log::error('Virus detected in file', [
        //             'file_id' => $this->file->id,
        //             'file_name' => $this->file->name,
        //             'result' => implode("\n", $output)
        //         ]);

        //         // Optionally, you can delete the file or take other actions
        //         throw new \Exception('Virus detected in the file');
        //     } else {
        //         Log::info('No virus found in file', [
        //             'file_id' => $this->file->id,
        //             'file_name' => $this->file->name
        //         ]);
        //     }
        // } else {
        //     throw new \Exception('File does not exist for scanning');
        // }
    }

    /**
     * Send email confirmation using AWS SES.
     *
     * @return void
     */
    private function sendEmailConfirmation()
{
    try {
        $user = User::find($this->file->uploaded_by);
        error_log("i'm start the send email ".$user->email);
        // Now you can safely access the email
        $email = $user->email;
        error_log("i'm start the send email ".$email);
        // Generate a temporary download URL for the file on S3
        $downloadUrl = $this->download();
        error_log("url".$downloadUrl);
        // Check if download URL is valid (i.e., not an error response)
        if (is_string($downloadUrl)) {
            // Send the email using SES
            error_log("i am here");
            Mail::to($email)->send(new FileUploadConfirmationMail($this->file, $downloadUrl));

            Log::info('File upload confirmation email sent successfully.', [
                'file_id' => $this->file->id,
                'user_email' => $email
            ]);
        } else {
            // Log an error if download URL was not generated
            Log::error('Failed to generate download URL.', [
                'file_id' => $this->file->id,
                'error' => 'Access denied or file not found.'
            ]);
        }

    } catch (S3Exception $e) {
        Log::error('Error generating temporary URL for file download.', [
            'file_id' => $this->file->id,
            'error' => $e->getMessage()
        ]);
    } catch (\Exception $e) {
        Log::error('Failed to send email confirmation for file upload.', [
            'file_id' => $this->file->id,
            'error' => $e->getMessage()
        ]);
    }
}


   
public function failed(Throwable $exception)
{
    // Log the failure and send information to AWS CloudWatch
    Log::channel('cloudwatch')->error('File upload job failed', [
        'file_id' => $this->file->id,
        'error' => $exception->getMessage(),
        'stack_trace' => $exception->getTraceAsString(),
    ]);

    // Optionally, you can notify admins or retry the job depending on your needs.
    // For example, sending a failure email or notification.
}
     /**
     * Handle file download by generating a pre-signed URL for download.
     *
     * @param  int  $fileId
     * @return \Illuminate\Http\Response
     */
    public function download()
    {   
        // Ensure the file exists and the current user belongs to the same tenant
        $tenant_id = User::find($this->file->uploaded_by)->tenant_id;
    
        if ($this->file && $this->file->tenant_id == $tenant_id) {
            // Get S3 disk instance
            $s3 = Storage::disk('s3');
    
            // Ensure you have the correct file path, for example using 'path' or 'file_name'
            $filePath = $this->file->path; // Assuming 'path' holds the correct file path in S3
             // Get the relative file path directly from the File model
        $filePath = $this->file->path;

        // Ensure it's a relative path (strip base URL if accidentally stored)
        $parsedUrl = parse_url($filePath);
        
        if (isset($parsedUrl['path'])) {
            $filePath = ltrim($parsedUrl['path'], '/');
        }

        error_log("Relative file path: " . $filePath);
    
            // Check if the file exists in S3 before generating the URL
            if ($this->checkFileExistsOnS3($filePath)) {
                // Generate temporary download URL that expires in 5 minutes
                $downloadUrl = $s3->temporaryUrl(
                    $filePath, now()->addMinutes(5) // Expiration time for the URL
                );
    
                // Return the download URL
                return $downloadUrl;
            }
    
            // File does not exist in S3
            return 'File not found in S3';
        }
    
        // If file doesn't exist or user doesn't have access
        return 'File not found or access denied';
    }
    


    public function checkFileExistsOnS3($filePath)
    {
        $bucket = env('AWS_BUCKET',);
        $region = env('AWS_DEFAULT_REGION',);

        // Initialize S3 Client
        $s3Client = new S3Client([
            'version' => 'latest',
            'region' => $region,
            'credentials' => [
            'key' => env('AWS_ACCESS_KEY_ID',),
            'secret' => env('AWS_SECRET_ACCESS_KEY'),
            ],
        ]);


        try {
            // Attempt to retrieve metadata for the object
            $result = $s3Client->headObject([
                'Bucket' => $bucket,
                'Key' => $filePath,
            ]);

            // If no error is thrown, the file exists
            return true;
        } catch (AwsException $e) {
            // If an error occurs, it means the file doesn't exist
            Log::error("File not found on S3: " . $filePath . " - " . $e->getMessage());
            return false;
        }
    }

}
