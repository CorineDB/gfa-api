<?php

namespace App\Jobs;

use App\Mail\EmailRapport;
use App\Mail\RapportEmail;
use App\Notifications\RapportNotification;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Notification;

class MailRapportJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $users, $contenu, $objet;

    public $timeout = 0;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct($users, $contenu, $objet)
    {
        $this->users = $users;
        $this->contenu = $contenu;
        $this->objet = $objet;

    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        $details = [];
        $details['subject'] = $this->objet;
        $details['contenu'] = $this->contenu;
        $mailer = new EmailRapport($details);

        foreach($this->users as $user)
        {
            $when = now()->addSeconds(15);

            Mail::to($user)->later($when, $mailer);
        }


    }


}
