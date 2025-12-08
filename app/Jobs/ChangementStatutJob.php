<?php

namespace App\Jobs;

use App\Mail\ChangementStatutEmail;
use App\Mail\DemarrageNotification;
use App\Notifications\ChangementStatutNotification;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Notification;

class ChangementStatutJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $user;

    protected $activite;

    protected $tache;

    protected $type;

    protected $statut;

    public $timeout = 0;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct($user, $activite = null, $tache = null, $type, $statut)
    {
        $this->user = $user;

        $this->activite = $activite;

        $this->tache = $tache;

        $this->type = $type;

        $this->statut = $statut;

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
            $details['subject'] = "ALERTE Changement de statut";
            $details['nom'] = $this->activite->nom;
            $details['codePta'] = $this->activite->codePta;
            $details['statut'] = $this->statut;
            $details['type'] = $this->type;
            $mailer = new ChangementStatutEmail($details);
        }

        else if($this->type == "tache")
        {
            $details['subject'] = "ALERTE Changement de statut";
            $details['nom'] = $this->tache->nom;
            $details['codePta'] = $this->tache->codePta;
            $details['statut'] = $this->statut;
            $details['type'] = $this->type;
            $mailer = new ChangementStatutEmail($details);
        }

        $when = now()->addSeconds(15);

        Mail::to($this->user->email)->later($when, $mailer);
    }


}
