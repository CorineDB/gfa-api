<?php

namespace App\Jobs;

use App\Mail\AnoEmail;
use App\Mail\ConfirmationDeCompteEmail;
use App\Mail\ReinitialisationMotDePasseEmail;
use App\Models\Organisation;
use App\Models\UniteeDeGestion;
use App\Models\User;
use App\Notifications\AnoNotification;
use App\Traits\Helpers\HelperTrait;
use Exception;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Notification;

class SendEmailJob /*implements ShouldQueue*/
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    use HelperTrait;

    private $user;
    private $type;
    private $mailer;
    private $password;
    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct(User $user, $type, $password = null)
    {
        $this->user = $user;
        $this->type = $type;
        $this->password = $password;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        try {
\Illuminate\Support\Facades\Log::notice("HERE - SEND MAIL");
            $details = [];
            $data = [];

            $lien = $this->getUserTypeAppUrl($this->user);

            if ($this->type == "confirmation-compte") {
                $details['view'] = "emails.auth.confirmation_compte";
                $details['subject'] = "Bienvenue";
                $details['content'] = [
                    "greeting" => "Bienvenu Mr/Mme " . $this->user->nom,
                    "introduction" => "Voici vos identifiant de connexion",
                    "identifiant" => $this->user->email,
                    "password" => $this->password,
                    "lien" => $lien,
                ];
                $mailer = new ConfirmationDeCompteEmail($details);
            } elseif ($this->type == "confirmation-de-compte") {

                $details['view'] = "emails.auth.confirmation_de_compte";
                $details['subject'] = "Confirmation de compte";
                $details['content'] = [
                    "greeting" => "Bienvenu Mr/Mme " . $this->user->nom,
                    "introduction" => "Voici votre lien d'activation de votre compte",
                    "lien" => $lien . "/activation/" . $this->user->token,
                ];
                $mailer = new ConfirmationDeCompteEmail($details);
            } elseif ($this->type == "reinitialisation-mot-de-passe") {

                $details['view'] = "emails.auth.reinitialisation_mot_passe";
                $details['subject'] = "Réinitialisation de passe";
                $details['content'] = [
                    "greeting" => "Bienvenu Mr/Mme " . $this->user->nom,
                    "introduction" => "Voici votre lien de réinitialisation",
                    "lien" => $lien . "/reset_password/" . $this->user->token,
                ];

                $mailer = new ReinitialisationMotDePasseEmail($details);
            } elseif ($this->type == "rappel-ano") {
                $details['view'] = "emails.ano.rappel";
                $details['subject'] = "Rappel de traitement d'une demande d'ano";
                $details['content'] = [
                    "greeting" => "Demande d'ano",
                    "introduction" => "Une demande d'ano est entente de validation",
                ];
                $mailer = new AnoEmail($details);
            } elseif ($this->type == "demande-ano") {
                $details['view'] = "emails.ano.demande";
                $details['subject'] = "Nouvelle demande d'ano";
                $details['content'] = [
                    "greeting" => "Demande d'ano",
                    "introduction" => "Une nouvelle demande d'ano vient d'être soumis",
                ];
                $mailer = new AnoEmail($details);

            } elseif ($this->type == "reponse-ano") {
                $details['view'] = "emails.ano.reponse";
                $details['subject'] = "Reponse suite à la demande d'ano";
                $details['content'] = [
                    "greeting" => "Reponse à la demande d'ano",
                    "introduction" => "Une nouvelle demande d'ano vient d'être soumis",
                ];
                $mailer = new AnoEmail($details);
            }

            $when = now()->addSeconds(5);

            Log::notice($this->user->email);
            
           // Mail::to($this->user->email)->later($when, $mailer);
            Mail::to($this->user->email)->send($mailer);
 \Illuminate\Support\Facades\Log::notice("Email envoyé immédiatement à {$this->user->email}");
        } catch (\Throwable $th) {
            Log::error($details['subject'] . ' : ' . $th->getMessage());
            throw new Exception("Error Processing Request : ". json_encode($details['subject']. " : ". $th->getMessage()), 1);
            //throw new Exception("Error Processing Request : ". $details['subject'], 1);
        }
    }
}
