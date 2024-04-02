<?php

namespace App\Mail;

use App\Models\Invoice;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Contracts\Queue\ShouldQueue;

class InvoiceMail extends Mailable
{
    use Queueable, SerializesModels;

    public $invoice;
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
    public function __construct(Invoice $invoice,$subject,$attachmentFileName,$attachmentFile,$content, $from = [])
    {
        $this->invoice = $invoice;
        $this->from = $from;
        $this->subject = $subject;
        // $this->attachmentFileName = $attachmentFileName;
        // $this->attachmentFile = $attachmentFile;
        $this->content = $content;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        
        // return $this->subject($this->subject)->view('emails.invoice.send')->attach($this->attachmentFile, [
        //     'as' => $this->attachmentFileName,
        //     'mime' => 'application/pdf',
        // ]);

        return $this->subject($this->subject)->view('emails.invoice.send');

    }
}
