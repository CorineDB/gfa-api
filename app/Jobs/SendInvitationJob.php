<?php

namespace App\Jobs;

use App\Mail\InvitationEnqueteDeCollecteEmail;
use App\Models\enquetes_de_gouvernance\EvaluationDeGouvernance as EnqueteEvaluationDeGouvernance;
use App\Models\EvaluationDeGouvernance;
use App\Traits\Helpers\ConfigueTrait;
use App\Traits\Helpers\SmsTrait;
use Exception;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;


class SendInvitationJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    use ConfigueTrait,SmsTrait;

    private $type;
    private $data;
    private $mailer;
    private $evaluationDeGouvernance;

    /**
     * Create a new job instance.
     *
     * @return voidSendInvi
     */
    public function __construct(EvaluationDeGouvernance | EnqueteEvaluationDeGouvernance $evaluationDeGouvernance, $data, $type)
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
    \Illuminate\Support\Facades\Log::notice("Invitation Data" . json_encode($this->data) );
                if (($evaluationOrganisation = $this->evaluationDeGouvernance->organisations($this->data["organisationId"])->first())) {
    \Illuminate\Support\Facades\Log::notice("Invitation Data" . $evaluationOrganisation);
                    // Decode and merge participants from the organisation's pivot data
                    $participants = array_merge($participants, $evaluationOrganisation->pivot->participants ? json_decode($evaluationOrganisation->pivot->participants, true) : []);
    \Illuminate\Support\Facades\Log::notice("Invitation Data" . json_encode($participants) );
                    // Filter participants for those with "email" contact type
                    $emailParticipants = array_filter($this->data["participants"], function ($participant) {
                        return $participant["type_de_contact"] === "email";
                    });
    \Illuminate\Support\Facades\Log::notice("Invitation Data" . json_encode($emailParticipants) );
                    // Extract email addresses for Mail::to()
                    $emailAddresses = array_column($emailParticipants, 'email');
    \Illuminate\Support\Facades\Log::notice("Invitation Data" . json_encode($emailAddresses) );

                    // Filter participants for those with "email" contact type
                    $phoneNumberParticipants = array_filter($this->data["participants"], function ($participant) {
                        return $participant["type_de_contact"] === "contact";
                    });

                    // Extract phone numbers for https://api.e-mc.co/v3/
                    $phoneNumbers = array_column($phoneNumberParticipants, 'phone');

                    $url = config("app.url");

                    // If the URL is localhost, append the appropriate IP address and port
                    if (strpos($url, 'localhost') == false) {
                        $url = config("app.organisation_url");
                    }

                    // Send the email if there are any email addresses
                    if (!empty($emailAddresses)) {

                        $details['view'] = "emails.auto-evaluation.invitation_enquete_de_collecte";
                        $details['subject'] = "Invitation à participer à notre enquête d'auto-évaluation de gouvernance";
                        $details['content'] = [
                            "greeting" => "Salut, Monsieur/Madame!",
                            "introduction" => "Vous êtes invité(e) à participer à l'enquête d' auto-evaluation de gouvernance de {$evaluationOrganisation->user->nom} dans le cadre du programme {$this->evaluationDeGouvernance->programme->nom} - annee d'exercice {$this->evaluationDeGouvernance->annee_exercice}.",
                            "lien" => $url . "/tools-perception/{$evaluationOrganisation->pivot->token}",
                        ];

                        // Create the email instance
                        $mailer = new InvitationEnqueteDeCollecteEmail($details);

                        // Send the email later after a delay
                        $when = now()->addSeconds(5);
                        Mail::to($emailAddresses)->send($mailer);

                        // Remove duplicates based on the "email" field (use email as the unique key)
                        $participants = $this->removeDuplicateParticipants(array_merge($participants, $this->data["participants"]));
                    }

                    // Send the sms if there are any phone
                    if (!empty($phoneNumbers)) {

                        try {

                            $message = "Bonjour,\n" .
                                        "Vous etes invite(e) a participer a l'enquete d'auto-evaluation de gouvernance de {$evaluationOrganisation->user->nom} dans le cadre du programme {$this->evaluationDeGouvernance->programme->nom} ({$this->evaluationDeGouvernance->annee_exercice}).\n" .
                                        "Participez des maintenant : " .
                                        "{$url}/tools-perception/{$evaluationOrganisation->pivot->token}\n" .
                                        "Merci !";
                            Log::info('Error sending SMS invitation : ' . json_encode($phoneNumbers));

                            $this->sendSms($message, $phoneNumbers);

                            // Remove duplicates based on the "email" field (use email as the unique key)
                            $participants = $this->removeDuplicateParticipants(array_merge($participants, $this->data["participants"]), 'phone');

                        } catch (\Throwable $th) {
                            Log::error('Error sending SMS invitation : ' . $th->getMessage());
                        }
                    }

                    // Update the pivot table with the merged participants
                    $evaluationOrganisation->pivot->participants = $participants;
                    //$evaluationOrganisation->pivot->nbreParticipants = $this->data['nbreParticipants'];
                    $evaluationOrganisation->pivot->save();
                    Log::warning('Nombre de participant : ' . $this->data['nbreParticipants']);

                    //dump(json_encode($participants));

                    if(isset($this->data['nbreParticipants'])){
                        if($this->data['nbreParticipants'] > 0){

                            //dump('nbreParticipants: ' . $this->data['nbreParticipants'] ."; pivot->nbreParticipants: ". $evaluationOrganisation->pivot->nbreParticipants . "; total_soumissions_de_perception: " . $this->evaluationDeGouvernance->total_soumissions_de_perception);
                            //dump("nbreParticipants < pivot->nbreParticipants: " . $this->data['nbreParticipants'] < $evaluationOrganisation->pivot->nbreParticipants . "; nbreParticipants < total_soumissions_de_perception: " . ($this->data['nbreParticipants'] >= $this->evaluationDeGouvernance->total_soumissions_de_perception));

                           // dd("if condition: " . (($this->data['nbreParticipants'] < $evaluationOrganisation->pivot->nbreParticipants) && ($this->data['nbreParticipants'] >= $this->evaluationDeGouvernance->total_soumissions_de_perception)));

                            if(/* ($this->data['nbreParticipants'] < $evaluationOrganisation->pivot->nbreParticipants) &&  */($this->data['nbreParticipants'] > $this->evaluationDeGouvernance->total_soumissions_de_perception)){

                                Log::info('Nombre de participant : ' . $this->data['nbreParticipants']);
                                $evaluationOrganisation->pivot->nbreParticipants = $this->data['nbreParticipants'];
                                $evaluationOrganisation->pivot->save();
                            }
                        }
                    }
                }
            }
        } catch (\Throwable $th) {
            throw new Exception("Error Processing Request : " . $details['subject'] . $th->getTrace(), 1);
        }
    }

    /**
     * Remove duplicate participants based on the 'email' field (or any unique field).
     */
    private function removeDuplicateParticipants($participants, string $type = 'email')
    {
        $uniqueParticipants = [];

        foreach ($participants as $participant) {
            if($type == 'email' && isset($participant['email'])){
                // If participant doesn't exist in uniqueParticipants array, add them
                $uniqueParticipants[$participant['email']] = $participant;
            }
            elseif($type == 'phone' && isset($participant['phone'])){
                $uniqueParticipants[$participant['phone']] = $participant;
            }
        }

        // Return the unique participants as a re-indexed array
        return array_values($uniqueParticipants);
    }
}
