<?php

namespace App\Jobs;

use App\Mail\InvitationEnqueteDeCollecteEmail;
use App\Models\enquetes_de_gouvernance\EvaluationDeGouvernance as EnqueteEvaluationDeGouvernance;
use App\Models\EvaluationDeGouvernance;
use App\Models\Organisation;
use App\Traits\Helpers\ConfigueTrait;
use App\Traits\Helpers\SmsTrait;
use Exception;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;


class SendInvitationJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    use ConfigueTrait,SmsTrait;

    private $type;
    private $data;
    private $evaluationDeGouvernance;
    private Organisation $evaluationOrganisation;
    private string $mailSubject; // New property for dynamic subject
    private string $mailView;    // New property for dynamic view/template

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct(
        EvaluationDeGouvernance | EnqueteEvaluationDeGouvernance $evaluationDeGouvernance,
        Organisation $evaluationOrganisation,
        array $data,
        string $type,
        string $mailSubject = "Invitation", // Default subject
        string $mailView = "emails.auto-evaluation.invitation_enquete_de_collecte" // Default view
    ) {
        $this->data = $data;
        $this->type = $type;
        $this->evaluationDeGouvernance = $evaluationDeGouvernance;
        $this->evaluationOrganisation = $evaluationOrganisation;
        $this->mailSubject = $mailSubject;
        $this->mailView = $mailView;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        try {
            if ($this->type == "invitation-enquete-de-collecte" || $this->type == "rappel-soumission") {
                // Use the pre-loaded evaluationOrganisation object
                $evaluationOrganisation = $this->evaluationOrganisation;

                if (!$evaluationOrganisation) {
                    Log::error("SendInvitationJob: evaluationOrganisation object was null for evaluation {$this->evaluationDeGouvernance->id}.");
                    throw new Exception("Evaluation Organisation object not passed to job correctly.");
                }

                $participantsToNotify = $this->data["participants"] ?? [];
                $token = $this->data['token'] ?? null;

                if (!$token) {
                     // Fallback attempt to fetch if not passed (though service should pass it now)
                     // Or log warning. For now, let's rely on data.
                     Log::warning("SendInvitationJob: Token not found in data, trying pivot.");
                     // If pivot is missing (serialized model), this will still fail if we don't handle it.
                     // But we expect it in data now.
                }

                $url = config("app.url");
                if (strpos($url, 'localhost') == false) {
                    $url = config("app.organisation_url");
                }

                $emailCount = 0;
                $smsCount = 0;
                $phoneNumbers = [];

                // --- Loop for Personalized Emails & Collecting Phones ---
                foreach ($participantsToNotify as $participant) {
                    if (!isset($participant['type_de_contact'])) continue;

                    // 1. EMAIL : Send individual personalized email
                    if ($participant['type_de_contact'] === 'email' && !empty($participant['email'])) {
                        
                        // Build personalized link with participant ID if available
                        $participantId = $participant['id'] ?? '';
                        $link = $url . "/tools-perception/{$token}" . ($participantId ? "/{$participantId}" : "");

                        $details = [];
                        $details['view'] = $this->mailView; // Use dynamic view
                        $details['subject'] = $this->mailSubject; // Use dynamic subject
                        $details['content'] = [
                            "greeting" => "Bonjour " . ($participant['nom'] ?? 'Monsieur/Madame') . " !",
                            "introduction" => "Vous êtes invité(e) à participer à l'enquête d' auto-evaluation de gouvernance de {$evaluationOrganisation->user->nom} dans le cadre du programme {$this->evaluationDeGouvernance->programme->nom} - année d'exercice {$this->evaluationDeGouvernance->annee_exercice}.",
                            "lien" => $link,
                        ];
                        // Adjust introduction for rappel
                        if ($this->type == "rappel-soumission") {
                             $details['content']['introduction'] = "Nous, **{$evaluationOrganisation->user->nom}**, vous rappelons votre participation à notre enquête d'auto-évaluation de gouvernance. Votre contribution est essentielle pour renforcer notre gouvernance dans le cadre du programme **{$this->evaluationDeGouvernance->programme->nom}**, année d'exercice **{$this->evaluationDeGouvernance->annee_exercice}**.";
                             $details['content']['body'] = "Votre contribution est essentielle pour finaliser cette étape cruciale. Merci de compléter votre soumission dans les plus brefs délais.";
                             $details['content']['cta_text'] = "Accéder au formulaire";
                             $details['content']['signature'] = "Cordialement, {$evaluationOrganisation->user->nom}";
                        }


                        try {
                            $mailer = new InvitationEnqueteDeCollecteEmail($details);
                            Mail::to($participant['email'])->send($mailer);
                            $emailCount++;
                        } catch (\Throwable $e) {
                            Log::error("Failed to send email to {$participant['email']}: " . $e->getMessage());
                        }
                    }
                    // 2. SMS : Collect unique phone numbers (SMS usually generic, no unique link per SMS in this bulk method)
                    elseif ($participant['type_de_contact'] === 'contact' && !empty($participant['phone'])) {
                        $phoneNumbers[] = $participant['phone'];
                    }
                }

                if ($emailCount > 0) {
                    Log::info("Sent $emailCount personalized {$this->type} emails for evaluation {$this->evaluationDeGouvernance->id}.");
                }

                // --- Send SMS (Bulk) ---
                $phoneNumbers = array_unique($phoneNumbers);
                
                if (!empty($phoneNumbers)) {
                    try {
                        $genericLink = $url . "/tools-perception/{$token}";
                        $message = "Bonjour,\n" .
                                    "Vous etes invite(e) a participer a l'enquete d'auto-evaluation de gouvernance de {$evaluationOrganisation->user->nom} dans le cadre du programme {$this->evaluationDeGouvernance->programme->nom} ({$this->evaluationDeGouvernance->annee_exercice}).\n" .
                                    "Participez des maintenant : " .
                                    "{$genericLink}\n" .
                                    "Merci !";
                        
                        // Adjust SMS message for rappel
                        if ($this->type == "rappel-soumission") {
                            $message = "Bonjour,\n\n" .
                                        "🔔 Rappel : Vous n’avez pas encore complete l’enquete d’auto-évaluation de gouvernance de {$evaluationOrganisation->user->nom} ({$this->evaluationDeGouvernance->programme->nom}, {$this->evaluationDeGouvernance->annee_exercice}).\n\n" .
                                        "Repondez des maintenant :\n" .
                                        "{$genericLink}\n\n" .
                                        "Merci pour votre participation !";
                        }

                        $this->sendSms($message, $phoneNumbers);
                        $smsCount = count($phoneNumbers);
                        Log::info("Sent bulk {$this->type} SMS to $smsCount numbers for evaluation {$this->evaluationDeGouvernance->id}.");
                    } catch (\Throwable $smsTh) {
                        Log::error('Error sending SMS invitations: ' . $smsTh->getMessage());
                    }
                }

            } else {
                Log::error("SendInvitationJob: Type '{$this->type}' not handled.");
            }
        } catch (\Throwable $th) {
            Log::error("Fatal Error in SendInvitationJob: " . $th->getMessage() . "\n" . $th->getTraceAsString());
            throw $th;
        }
    }}
