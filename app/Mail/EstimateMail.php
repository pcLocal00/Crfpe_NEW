<?php

namespace App\Mail;

use App\Models\Estimate;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Contracts\Queue\ShouldQueue;

class EstimateMail extends Mailable
{
    use Queueable, SerializesModels;

    public $estimate;
    public $subject;
    public $attachmentFileName;
    public $attachmentFile;
    public $content;
    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct(Estimate $estimate, $subject, $attachmentFileName, $attachmentFile, $content, $from = [])
    {
        $this->estimate = $estimate;
        $this->from = $from;
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

        return $this->subject($this->subject)->view('emails.estimate.send')->attach($this->attachmentFile, [
            'as' => $this->attachmentFileName,
            'mime' => 'application/pdf',
        ]);
    }
}
