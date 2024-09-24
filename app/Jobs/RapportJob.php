<?php

namespace App\Jobs;

use App\Mail\RapportEmail;
use App\Notifications\RapportNotification;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Notification;

class RapportJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $user;

    public $timeout = 0;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct($user)
    {
        $this->user = $user;

    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        $details = [];
        $details['subject'] = "ALERTE RAPPEL POUR FAIRE UN RAPPORT";
        $details['type'] = $this->type;
        $mailer = new RapportEmail($details);

        $when = now()->addSeconds(15);

        Mail::to($this->user->email)->later($when, $mailer);
    }


}
