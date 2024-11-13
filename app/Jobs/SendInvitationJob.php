<?php

namespace App\Jobs;

use App\Mail\InvitationEnqueteDeCollecteEmail;
use App\Models\EvaluationDeGouvernance;
use Exception;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Mail;

class SendInvitationJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    private $type;
    private $data;
    private $mailer;
    private $evaluationDeGouvernance;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct(EvaluationDeGouvernance $evaluationDeGouvernance, $data, $type)
    {
        $this->data = $data;
        $this->type = $type;
        $this->evaluationDeGouvernance = $evaluationDeGouvernance;
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

            if ($this->type == "invitation-enquete-de-collecte") {
                $participants = [];

                if (($evaluationOrganisation = $this->evaluationDeGouvernance->organisations($this->data["organisationId"])->first())) {

                    // Decode and merge participants from the organisation's pivot data
                    $participants = array_merge($participants, $evaluationOrganisation->pivot->participants ? json_decode($evaluationOrganisation->pivot->participants, true) : []);

                    // Filter participants for those with "email" contact type
                    $emailParticipants = array_filter($this->data["participants"], function ($participant) {
                        return $participant["type_de_contact"] === "email";
                    });

                    // Extract email addresses for Mail::to()
                    $emailAddresses = array_column($emailParticipants, 'email');

                    // Send the email if there are any email addresses
                    if (!empty($emailAddresses)) {

                        $url = config("app.url");

                        // If the URL is localhost, append the appropriate IP address and port
                        if (strpos($url, 'localhost') !== false) {
                            $url = '192.168.1.16:3000';
                        }

                        $details['view'] = "emails.auto-evaluation.invitation_enquete_de_collecte";
                        $details['subject'] = "Invitation à participer à notre enquête d'auto-évaluation de gouvernance";
                        $details['content'] = [
                            "greeting" => "Salut, Monsieur/Madame!",
                            "introduction" => "Vous êtes invité(e) à participer à l'enquête de collecte auto-evaluation de gouvernance de {$evaluationOrganisation->user->nom} de l'annee d'exercice {$this->evaluationDeGouvernance->annee_exercice}.",
                            "lien" => $url . "/dashboard/tools-perception/{$evaluationOrganisation->pivot->token}",
                        ];

                        // Create the email instance
                        $mailer = new InvitationEnqueteDeCollecteEmail($details);

                        // Send the email later after a delay
                        $when = now()->addSeconds(5);
                        Mail::to($emailAddresses)->later($when, $mailer);

                        // Remove duplicates based on the "email" field (use email as the unique key)
                        $participants = $this->removeDuplicateParticipants(array_merge($participants, $this->data["participants"]));
                    }

                    // Update the pivot table with the merged participants
                    $evaluationOrganisation->pivot->participants = $participants;
                    $evaluationOrganisation->pivot->nbreParticipants = count($participants);
                    $evaluationOrganisation->pivot->save();
                }
            }
        } catch (\Throwable $th) {
            throw new Exception("Error Processing Request : " . $details['subject'], 1);
        }
    }

    /**
     * Remove duplicate participants based on the 'email' field (or any unique field).
     */
    private function removeDuplicateParticipants($participants)
    {
        $uniqueParticipants = [];
    
        foreach ($participants as $participant) {
            // If participant doesn't exist in uniqueParticipants array, add them
            $uniqueParticipants[$participant['email']] = $participant;
        }
    
        // Return the unique participants as a re-indexed array
        return array_values($uniqueParticipants);
    }
}
