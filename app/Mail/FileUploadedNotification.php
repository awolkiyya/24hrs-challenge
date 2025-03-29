<?php

namespace App\Mail;

use App\Models\File;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class FileUploadedNotification extends Mailable
{
    use Queueable, SerializesModels;

    public $file;

    /**
     * Create a new message instance.
     *
     * @param \App\Models\File $file
     */
    public function __construct(File $file)
    {
        $this->file = $file;
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
                        'fileSize' => $this->file->size,
                        'downloadUrl' => $this->file->getDownloadUrl(),
                    ]);
    }
}
