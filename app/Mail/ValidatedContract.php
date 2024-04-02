<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class ValidatedContract extends Mailable
{
    use Queueable, SerializesModels;
    public $htmlContent;
    public $subject;
    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct($htmlContent,$subject)
    {
        $this->htmlContent = $htmlContent;
        $this->subject = $subject;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        return $this->subject($this->subject)->view('emails.validated-contracts');
    }
}
