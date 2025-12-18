<?php

namespace App\Jobs;

use App\Mail\SuiviEmail;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Mail;

class SuiviJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $user;

    protected $type;

    public $timeout = 300;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct($user, $type)
    {
        $this->user = $user;

        $this->type = $type;

    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        $details = [];
        if($this->type == "indicateur"){
            $details['subject'] = "ALERTE RAPPEL SUIVI INDICATEUR";
            $details['type'] = $this->type;
            $mailer = new SuiviEmail($details);
        }

        else if($this->type == "financier")
        {
            $details['subject'] = "ALERTE RAPPEL SUIVI FINANCIER";
            $details['type'] = $this->type;
            $mailer = new SuiviEmail($details);
        }

        $when = now()->addSeconds(15);

        if(!empty($this->user->email)){
            Mail::to($this->user->email)->later($when, $mailer);
        }
    }


}
