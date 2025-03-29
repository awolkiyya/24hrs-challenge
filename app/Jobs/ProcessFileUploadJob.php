<?php

namespace App\Jobs;

use App\Models\File;
use App\Models\Tenant;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Aws\S3\Exception\S3Exception;
use App\Mail\FileUploadedNotification;
use Illuminate\Support\Facades\Mail;

class ProcessFileUploadJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $file;

    /**
     * Create a new job instance.
     *
     * @param  \App\Models\File  $file
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
            // Simulate file processing (e.g., virus scan, file validation)
            $this->simulateFileProcessing();

            // Update file status to "processed" (this could be status, or other logic)
            $this->updateFileStatus();

            // Log file processing
            $this->logFileProcessing();

            // Optionally send a notification or email after processing
            $this->sendPostProcessingNotification();

        } catch (\Exception $e) {
            // Log any exceptions
            Log::error("Error processing file ID {$this->file->id}: {$e->getMessage()}");
            
            // You can set the file status to "failed" if necessary
            $this->file->update(['status' => 'failed']);
        }
    }

    /**
     * Simulate some processing, e.g., a virus scan or file validation.
     *
     * @return void
     */
    protected function simulateFileProcessing()
    {
        // You can simulate a virus scan or any other processing logic here
        Log::info("Starting processing for file ID {$this->file->id}");

        // Simulate a delay or processing time
        sleep(3); // Remove this in real code, it's just for simulation

        // Simulate virus scan check (just an example)
        if (rand(0, 1) === 0) {
            throw new \Exception("Virus detected in file ID {$this->file->id}.");
        }

        Log::info("File ID {$this->file->id} processed successfully.");
    }

    /**
     * Update the file status after processing.
     *
     * @return void
     */
    protected function updateFileStatus()
    {
        // Assuming the file has a status column to track processing stages
        $this->file->update([
            'status' => 'processed', // Or whatever status you use to indicate success
            'processed_at' => now(),
        ]);
    }

    /**
     * Log the file processing event.
     *
     * @return void
     */
    protected function logFileProcessing()
    {
        // Log the event to file (local logs)
        Log::info("File ID {$this->file->id} processing completed.");

        // Log the event to CloudWatch
        Log::channel('cloudwatch')->info("File ID {$this->file->id} processing completed.");

        // Optionally, you could log more detailed information, e.g., user info, tenant info, etc.
        $user = User::find($this->file->uploaded_by);
        Log::info("File uploaded by User ID {$user->id} - {$user->email}");

        // Log the user upload info to CloudWatch as well
        Log::channel('cloudwatch')->info("File uploaded by User ID {$user->id} - {$user->email}");

        // Log tenant info if relevant
        $tenant = Tenant::find($this->file->tenant_id);
        Log::info("File belongs to Tenant ID {$tenant->id} - {$tenant->name}");

        // Log the tenant info to CloudWatch
        Log::channel('cloudwatch')->info("File belongs to Tenant ID {$tenant->id} - {$tenant->name}");
    }


    /**
     * Send a post-processing notification (e.g., email).
     *
     * @return void
     */
    protected function sendPostProcessingNotification()
    {
        // Send a notification or email after the file is processed
        // This could use Laravel's Mail, Notification, or custom event.

        // Example: Send a notification to the user who uploaded the file
        $user = User::find($this->file->uploaded_by);

        // You could use a mailable, notification, or even directly use an SES service
        // For example, sending a confirmation email with the processed file details
         // Send the email notification
        Mail::to(auth()->user())->send(new FileUploadedNotification($file));
    }
}
