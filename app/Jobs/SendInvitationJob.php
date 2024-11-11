<?php

namespace App\Jobs;

use App\Mail\AnoEmail;
use App\Mail\ConfirmationDeCompteEmail;
use App\Mail\InvitationEnqueteDeCollecteEmail;
use App\Mail\ReinitialisationMotDePasseEmail;
use App\Models\User;
use App\Notifications\AnoNotification;
use Exception;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Notification;

class SendInvitationJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    private $type;
    private $data;
    private $mailer;
    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct($data, $type){
        $this->data = $data;
        $this->type = $type;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        try {

            $details = [];
            $data = [];

            if ($this->type == "enquete-de-collecte") {
                $details['view'] = "emails.auth.confirmation_compte";
                $details['subject'] = "Invitation a l'enquete de collecte";
                $details['content'] = [
                    "greeting" => "Bienvenu Mr/Mme ",
                    "introduction" => "Invitation de participation a l'enquete de collecte",
                    "lien" => config("app.url") . "/invitation-enquete-de-collecte/fgfg",
                ];
                $mailer = new InvitationEnqueteDeCollecteEmail($details);
            }

            $when = now()->addSeconds(5);

            ///Mail::to($this->user)->later($when, $mailer);
        } catch (\Throwable $th) {
            
            throw new Exception("Error Processing Request : ". $details['subject'], 1);
        }
    }
}
