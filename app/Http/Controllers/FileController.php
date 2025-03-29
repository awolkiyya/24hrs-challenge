<?php

namespace App\Http\Controllers;

use App\Models\File;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Log;
use App\Jobs\ProcessFileUploadJob;
use Illuminate\Support\Str;  // Importing Str
use Aws\S3\S3Client;
use Aws\Exception\AwsException;
class FileController extends Controller
{
public function getPresignedUrl(Request $request)
{
              $fileName = $request->input('file_name');
              $fileType = $request->input('file_type');

              if (!$fileName || !$fileType) {
                     return response()->json(['error' => 'Missing parameters'], 400);
              }

              // Set S3 path based on file type
              $tenantId = auth()->user()->tenant_id;

               // Generate the file path for the S3 upload
               $filePath = "tenants/{$tenantId}/uploads/" . $fileName;


              $bucket = env('AWS_BUCKET','ghioonlaravelbucket');
              $region = env('AWS_DEFAULT_REGION', 'us-east-1');

              // Initialize S3 Client
              $s3 = new S3Client([
                     'version' => 'latest',
                     'region' => $region,
                     'credentials' => [
                     'key' => env('AWS_ACCESS_KEY_ID','AKIAWUAJFP3KTZWW7UDI'),
                     'secret' => env('AWS_SECRET_ACCESS_KEY','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'),
                     ],
              ]);

              try {
                     // Generate the pre-signed URL
                     $command = $s3->getCommand('PutObject', [
                     'Bucket' => $bucket,
                     'Key' => $filePath,
                     'ACL' => 'public-read', // Change to 'private' for security
                     'ContentType' => $fileType, // Use the provided file type
                     ]);
                     $request = $s3->createPresignedRequest($command, '+1 hour'); // URL valid for 1 hour
                     $presignedUrl = (string) $request->getUri();

                     return response()->json([
                     'presigned_url' => $presignedUrl,
                     'file_path' => $filePath // Return file path for database storage
                     ]);
              } catch (\Exception $e) {
                     return response()->json(['error' => $e->getMessage()], 500);
              }
       }



    // Store metadata after file upload
    public function storeFileMetadata(Request $request)
    {
        try {
            // Validate the incoming request
            $validator = Validator::make($request->all(), [
                'file_path' => 'required|string',
                'file_name' => 'required|string',
                'file_size' => 'required|integer',
                'mime_type' => 'required|string',
            ]);

            if ($validator->fails()) {
                return response()->json(['error' => 'Invalid file metadata.'], 400);
            }

            // Ensure the user has a valid tenant ID
            $tenantId = auth()->user()->tenant_id;
            if (!$tenantId) {
                return response()->json(['error' => 'Invalid tenant ID.'], 400);
            }

            // Store the file metadata in the database
            $fileRecord = File::create([
                'tenant_id' => $tenantId,  // Ensure tenant_id is retrieved
                'name' => $request->input('file_name'),
                'path' => $request->input('file_path'),
                'size' => $request->input('file_size'),
                'mime_type' => $request->input('mime_type'),
                'status' => 'uploaded',  // Set initial status to 'uploaded'
                'uploaded_by' => auth()->id(),  // Store user ID who uploaded
            ]);

            // Dispatch the job for background processing
            ProcessFileUploadJob::dispatch($fileRecord)->onQueue('mvp-queue');

            return response()->json([
                'message' => 'File metadata stored successfully, processing in the background.',
                'file_record' => $fileRecord,
            ]);
        } catch (\Exception $e) {
            // Log the error and send a generic response for security
            Log::error('Failed to store file metadata', [
                'error' => $e->getMessage(),
                'file_name' => $request->input('file_name'),
            ]);

            return response()->json([
                'error' => 'Failed to store file metadata',
                'details' => env('APP_ENV') === 'production' ? 'Please try again later.' : $e->getMessage(),
            ], 500);
        }
    }
}


