<?php

namespace App\Jobs;

use App\Mail\InvitationEnqueteDeCollecteEmail;
use App\Models\EvaluationDeGouvernance;
use App\Traits\Helpers\ConfigueTrait;
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

    use ConfigueTrait;

    private $type;
    private $data;
    private $mailer;
    private $evaluationDeGouvernance;

    /**
     * Create a new job instance.
     *
     * @return voidSendInvi
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


                    // Filter participants for those with "email" contact type
                    $phoneNumberParticipants = array_filter($this->data["participants"], function ($participant) {
                        return $participant["type_de_contact"] === "contact";
                    });

                    // Extract phone numbers for https://api.e-mc.co/v3/
                    $phoneNumbers = array_column($phoneNumberParticipants, 'contact');

                    // Send the email if there are any email addresses
                    if (!empty($emailAddresses)) {

                        $url = config("app.url");

                        // If the URL is localhost, append the appropriate IP address and port
                        if (strpos($url, 'localhost') !== false) {
                            $url = 'http://192.168.1.16:3000';
                        }

                        $details['view'] = "emails.auto-evaluation.invitation_enquete_de_collecte";
                        $details['subject'] = "Invitation à participer à notre enquête d'auto-évaluation de gouvernance";
                        $details['content'] = [
                            "greeting" => "Salut, Monsieur/Madame!",
                            "introduction" => "Vous êtes invité(e) à participer à l'enquête d' auto-evaluation de gouvernance de {$evaluationOrganisation->user->nom} dans le cadre du programme {$this->evaluationDeGouvernance->programme->nom} - annee d'exercice {$this->evaluationDeGouvernance->annee_exercice}.",
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

                    // Send the sms if there are any phone numbers
                    if (!empty($phoneNumbers)) {

                        $headers = [
                            'Authorization' => 'Basic ' . $this->sms_api_key
                        ];

                        $request_body = [
                            'globals' => [
                                'from' => 'GFA'
                            ],
                            'messages' => [
                                [
                                    'to' => $phoneNumbers,
                                    'content' => "Salut, Monsieur/Madame!\n\n". "Vous etes invite(e) a participer a l'enquete d'auto-evaluation de gouvernance de {$evaluationOrganisation->user->nom} dans le cadre du programme {$this->evaluationDeGouvernance->programme->nom} - annee d'exercice {$this->evaluationDeGouvernance->annee_exercice}.\n\n Cliquez des maintenant sur le lien ci-dessous pour acceder a l’enquete et partager votre precieuse opinion:\n PARTICIPEZ DES MAINTENANT A L'ENQUETE: \n\n"
                                ]
                            ]
                        ];

                        dd($headers, $request_body);

                        $request_body = [
                            'globals' => [
                                'from' => 'GFA',
                            ],
                            'messages' => [
                                [
                                    'to' => $phoneNumbers,
                                    'content' => 
                                    "Salut, Monsieur/Madame!\n\n" .
                                    "Vous etes invite(e) a participer a l'enquete d'auto-evaluation de gouvernance de {$evaluationOrganisation->user->nom} dans le cadre du programme {$this->evaluationDeGouvernance->programme->nom} - annee d'exercice {$this->evaluationDeGouvernance->annee_exercice}.\n\n" .
                                    "Cliquez des maintenant sur le lien ci-dessous pour acceder a l’enquete et partager votre precieuse opinion:\n" .
                                    "PARTICIPEZ DES MAINTENANT A L'ENQUETE:" .
                                    "{{$url}}/dashboard/tools-perception/{$evaluationOrganisation->pivot->token},\n\n" .
                                    "Merci de l'attention!",
                                ],
                            ],
                        ];

                        $response = Http::dd()->withHeaders($headers)->post($this->sms_api_url . '/sendbatch', $request_body);

                        dd([$phoneNumbers, !empty($phoneNumbers), $response]);

                        // Handle the response
                        if ($response->successful()) {

                            // Remove duplicates based on the "email" field (use email as the unique key)
                            $participants = $this->removeDuplicateParticipants(array_merge($participants, $this->data["participants"]));
                            return $response->json(); // or handle as needed
                        } else {
                            return $response->body(); // Debug or log error
                            throw new Exception("Error Processing Request", 1);
                        }
                    }

                    // Update the pivot table with the merged participants
                    $evaluationOrganisation->pivot->participants = $participants;
                    if(isset($this->data['nbreParticipants']) && $this->data['nbreParticipants'] > 0){
                        if(($this->data['nbreParticipants'] > $evaluationOrganisation->pivot->nbreParticipants) && ($this->data['nbreParticipants'] >= $this->evaluationDeGouvernance->total_soumissions_de_perception)){
                            $evaluationOrganisation->pivot->nbreParticipants = $this->data['nbreParticipants'];
                            $evaluationOrganisation->pivot->save();
                        }
                    }
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
