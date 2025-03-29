<?php
namespace App\Mail;

use App\Models\File;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class FileUploadConfirmationMail extends Mailable
{
    use Queueable, SerializesModels;

    public $file;
    public $downloadUrl;

    /**
     * Create a new message instance.
     *
     * @param \App\Models\File $file
     * @param string $downloadUrl
     */
    public function __construct(File $file, $downloadUrl)
    {
        $this->file = $file;
        $this->downloadUrl = $downloadUrl;
    }

    /**
     * Build the message.
     *
     * @return \Illuminate\Mail\Mailable
     */
    public function build()
    {
        return $this->subject('File Uploaded Successfully')
                    ->view('emails.file_uploaded')
                    ->with([
                        'fileName' => $this->file->name,
                        'fileSize' => $this->formatFileSize($this->file->size),
                        'fileMimeType' => $this->file->mime_type, // Pass MIME type here
                        'downloadUrl' => $this->downloadUrl,
                    ]);
    }

    /**
     * Format file size for better readability.
     *
     * @param int $size
     * @return string
     */
    protected function formatFileSize($size)
    {
        if ($size >= 1073741824) {
            return number_format($size / 1073741824, 2) . ' GB';
        } elseif ($size >= 1048576) {
            return number_format($size / 1048576, 2) . ' MB';
        } elseif ($size >= 1024) {
            return number_format($size / 1024, 2) . ' KB';
        } else {
            return $size . ' bytes';
        }
    }
}

