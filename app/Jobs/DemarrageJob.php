<?php

namespace App\Jobs;

use App\Mail\DemarrageEmail;
use App\Mail\PasswordExpirationNotification;
use App\Notifications\DemarrageNotification;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Notification;

class DemarrageJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $user;

    protected $activite;

    protected $tache;

    protected $type;

    protected $date;

    public $timeout = 0;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct($user, $activite = null, $tache = null, $type, $date)
    {
        $this->user = $user;

        $this->activite = $activite;

        $this->tache = $tache;

        $this->type = $type;

        $this->date = $date;

    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        $details = [];

        if($this->type == "activite"){
            $details['subject'] = "ALERTE DEMARRAGE ACTIVITE";
            $details['nom'] = $this->activite->nom;
            $details['codePta'] = $this->activite->codePta;
            $details['date'] = $this->date;
            $details['type'] = 'activite';
            $mailer = new DemarrageEmail($details);

        }

        else if($this->type == "tache")
        {
            $details['subject'] = "ALERTE DEMARRAGE TACHE";
            $details['nom'] = $this->tache->nom;
            $details['codePta'] = $this->tache->codePta;
            $details['date'] = $this->date;
            $details['type'] = 'tache';
            $mailer = new DemarrageEmail($details);
        }

        $when = now()->addSeconds(15);

        Mail::to($this->user->email)->later($when, $mailer);
    }


}
