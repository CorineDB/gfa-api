<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Support\Str;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class SuiviEmail extends Mailable
{
    use Queueable, SerializesModels;

    protected $details;

    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct($details = null)
    {
        $this->details = $details;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        $details = $this->details;
        Log::info('SuiviJob dispatched for user ');
        return $this->from(config("mail.mailers.smtp.username"), config("app.name"))->subject(Str::ucfirst($this->details['subject']))->view('emails.pta.suivi', compact('details'));
    }
}
