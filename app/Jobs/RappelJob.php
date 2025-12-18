<?php

namespace App\Jobs;

use App\Mail\DemarrageNotification;
use App\Mail\RappelEmail;
use App\Notifications\RappelNotification;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Notification;

class RappelJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $user;

    protected $description;


    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct($user, $description)
    {
        $this->user = $user;
        $this->description = $description;

    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        $details = [];

        $details['subject'] = "Rappel";
        $details['content'] = [
            "greeting" => "Bonjour Mr/Mme ". $this->user->nom,
            "libelle" => $this->description
        ];
        $mailer = new RappelEmail($details);

        $when = now()->addSeconds(15);

        Mail::to($this->user->email)->later($when, $mailer);
    }


}
