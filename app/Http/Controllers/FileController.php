<?php

namespace App\Http\Controllers;

use App\Models\File;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use App\Jobs\ProcessFileUploadJob;

class FileController extends Controller
{
    /**
     * Handle file upload and return a pre-signed URL for the S3 upload.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function upload(Request $request)
    {
        // Validate the incoming request
        $validator = Validator::make($request->all(), [
            'file' => 'required|file|max:10240', // max size: 10MB
        ]);

        if ($validator->fails()) {
            return response()->json(['error' => 'Invalid file uploaded.'], 400);
        }

        $file = $request->file('file');

        // Define the S3 path for the tenant's uploads (using tenant_id as a prefix)
        $tenantId = tenant()->id; // Assuming tenant identification via stancl/tenancy package
        $filePath = "tenants/{$tenantId}/uploads/" . $file->getClientOriginalName();

        // Generate a pre-signed POST URL for the file upload to S3
        $s3 = Storage::disk('s3');
        $presignedUrl = $s3->temporaryUrl(
            $filePath, now()->addMinutes(5) // Expiration time for the URL
        );

        // Save metadata into the database
        $fileRecord = File::create([
            'tenant_id' => $tenantId,
            'name' => $file->getClientOriginalName(),
            'path' => $filePath,
            'size' => $file->getSize(),
            'mime_type' => $file->getMimeType(),
            'uploaded_by' => Auth::id(), // Assuming the user is authenticated
        ]);
        // Dispatch the job to process the file asynchronously
        ProcessFileUploadJob::dispatch($file);
        // Return the pre-signed URL to the frontend
        return response()->json([
            'presigned_url' => $presignedUrl,
            'file_id' => $fileRecord->id,
        ]);
    }

    /**
     * Handle file download by generating a pre-signed URL for download.
     *
     * @param  int  $fileId
     * @return \Illuminate\Http\Response
     */
    public function download($fileId)
    {
        $file = File::find($fileId);

        // Ensure the file exists and the current user has access
        if ($file && $file->tenant_id == tenant()->id) {
            $s3 = Storage::disk('s3');
            $downloadUrl = $s3->temporaryUrl(
                $file->path, now()->addMinutes(5) // Expiration time for the URL
            );

            return response()->json(['download_url' => $downloadUrl]);
        }

        return response()->json(['error' => 'File not found or access denied'], 403);
    }
}
