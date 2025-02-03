<?php

namespace App\Jobs;

use App\Traits\Helpers\SmsTrait;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Mail;

class SmsProviderAlertAccountBalanceJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels,SmsTrait;

    protected $phoneNumbers;
    protected $recipients;
    protected $message;
    protected $subject;

    /**
     * Create a new job instance.
     *
     * @return void
     */  
    public function __construct($message, $subject='', $recipients=[], $phoneNumbers=[])
    {
            $this->recipients = $recipients;
            $this->phoneNumbers = $phoneNumbers;
            $this->message = $message;
            $this->subject = $subject;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        if (!empty($this->emails)) {
            Mail::raw($this->message, function ($mail) {
                $mail->to($this->recipients)
                    ->subject($this->subject);
            });
        }

        if (!empty($this->phoneNumbers)) {
            $this->sendSms($this->message, $this->phoneNumbers);
        }
    }
}
