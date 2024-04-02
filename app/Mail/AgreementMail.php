<?php

namespace App\Mail;

use App\Models\Agreement;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Contracts\Queue\ShouldQueue;

class AgreementMail extends Mailable
{
    use Queueable, SerializesModels;

    public $agreement;
    public $subject;
    public $attachmentFileName;
    public $attachmentFile;
    public $content;
    public $from;
    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct(Agreement $agreement, $subject, $attachmentFileName, $attachmentFile, $content, $from = [])
    {
        $this->agreement = $agreement;
        $this->subject = $subject;
        $this->attachmentFileName = $attachmentFileName;
        $this->attachmentFile = $attachmentFile;
        $this->content = $content;
        $this->from = $from;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        return $this->subject($this->subject)->view('emails.agreement.send')->attach($this->attachmentFile, [
            'as' => $this->attachmentFileName,
            'mime' => 'application/pdf',
        ]);
    }
}
