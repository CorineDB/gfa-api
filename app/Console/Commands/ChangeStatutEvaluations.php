<?php

namespace App\Console\Commands;

use App\Events\InAppNotification;
use App\Events\NewNotification;
use App\Jobs\AppJob;
use App\Jobs\RappelJob;
use App\Models\EvaluationDeGouvernance;
use App\Notifications\EvaluationNotification;
use App\Notifications\RappelNotification;
use App\Traits\Helpers\HelperTrait;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Notification;

class ChangeStatutEvaluations extends Command
{
    use HelperTrait;

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'change-statut:evaluations';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Update the status of evaluations based on their start and end dates';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $today = Carbon::today()->toDateString();

        $url = config("app.url");

        // If the URL is localhost, append the appropriate IP address and port
        if (strpos($url, 'localhost') == false) {
            $url = config("app.organisation_url");
        }

        // Fetch up to $maxUpdates evaluations that should start today and update their status to '0' (demarrage)
        $startingEvaluations = EvaluationDeGouvernance:: //DB::table('evaluations_de_gouvernance')->
            where('debut', '<=', $today)
            ->where('statut', '<', 0)
            ->get();

        DB::table('evaluations_de_gouvernance')
            ->whereIn('id', $startingEvaluations->pluck('id'))
            ->update(['statut' => 0]);

        try {
            AppJob::dispatch(
                // Your action code here, for example, sending an email, processing data, etc.
                $this->starting_evaluations_notification($startingEvaluations, $url)
            ); // Optionally add additional delay at dispatch time

            // Log success or perform other actions as needed
        } catch (\Exception $e) {
            // Handle the exception, log an error, or notify of failure
            Log::error('Failed to send notifications: ' . $e->getMessage());
        }

        $endedEvaluations = EvaluationDeGouvernance::where('fin', '<=', $today)
            ->where('statut', '==', 0)
            ->get();

        // Change the status based on the date
        DB::table('evaluations_de_gouvernance')
            ->whereIn('id', $endedEvaluations->pluck('id'))
            ->update(['statut' => 1]); // Assuming '1' indicates a finished evaluation

        try {
            AppJob::dispatch(
                // Your action code here, for example, sending an email, processing data, etc.
                $this->ended_evaluations_notification($endedEvaluations, $url)
            ); // Optionally add additional delay at dispatch time

            // Log success or perform other actions as needed
        } catch (\Exception $e) {
            // Handle the exception, log an error, or notify of failure
            Log::error('Failed to send notifications: ' . $e->getMessage());
        }

        /*foreach ($endedEvaluations as $key => $ended_evaluation) {

            foreach ($ended_evaluation->organisations as $key => $organisation) {

                if ((!empty($organisation->user->email)) && (filter_var($organisation->user->email, FILTER_VALIDATE_EMAIL))) {

                    $data['module'] = "cloture evaluation";
                    $data['texte'] = "Clôture de l'enquete d'auto-évaluation de Gouvernance - Année {$ended_evaluation->annee_exercice}";
                    $data['id'] = $ended_evaluation->id;
                    $data['auteurId'] = 0;

                    $data['details'] = [
                        'view' => "emails.auto-evaluation.evaluation",
                        'subject' => "Clôture de l'enquete d'auto-évaluation de Gouvernance - Année {$ended_evaluation->annee_exercice}",
                        'content' => [
                            "greeting" => "Salut, Monsieur/Madame! {$organisation->nom_point_focal} {$organisation->prenom_point_focal}",
                            "introduction" => "Nous vous informons de la clôture de l'enquête d'auto-évaluation de gouvernance du programme {$ended_evaluation->programme->nom} - Année {$ended_evaluation->annee_exercice}. \n Trouver dans le lien ci-dessous le resultat de l'enquete auto-evaluation.",
                            "lien" => $url . "/dashboard/synthese/{$ended_evaluation->secure_id}",
                            "link_text" => "Consulter le rapport final",
                        ]
                    ];

                    // Create the notification instance with the required data
                    $notification = new EvaluationNotification($data, ['mail', 'database', 'broadcast']);

                    try {
                        $organisation->user->notify($notification);
                        // Log success or perform other actions as needed
                    } catch (\Exception $e) {
                        // Handle the exception, log an error, or notify of failure
                        Log::error('Failed to send notifications: ' . $e->getMessage());
                    }

                } else {
                    Log::error("Invalid or missing email address for user ID: {$organisation->user->nom}");
                }

                AppJob::dispatch(
                    // Call the GenerateEvaluationResultats command with the evaluation ID
                    Artisan::call('generate:report-evaluation-resultats', [
                        'evaluationId' => $ended_evaluation->id
                    ])
                )->delay(now()); // Optionally add additional delay at dispatch time->addMinutes(10)
            }
        }*/

        $startingEvaluations = EvaluationDeGouvernance::
            where('debut', '>', $today)
            ->where('statut', '>=', 0)
            ->get();

        // Update their statut to -1 (not started)
        DB::table('evaluations_de_gouvernance')
            ->whereIn('id', $startingEvaluations->pluck('id'))
            ->update(['statut' => -1]);

        return 0;
    }

    protected function starting_evaluations_notification($startingEvaluations, string $url){
        foreach ($startingEvaluations as $key => $starting_evaluation) {

            foreach ($starting_evaluation->organisations as $key => $organisation) {

                if ((!empty($organisation->user->email)) && (filter_var($organisation->user->email, FILTER_VALIDATE_EMAIL))) {

                    $data['module'] = "demarrage evaluation";
                    $data['texte'] = "Demarrage de l'evaluation d'auto-gouvernance {$starting_evaluation->nom}";
                    $data['id'] = $starting_evaluation->id;
                    $data['auteurId'] = 0;

                    $data['details'] = [
                        'view' => "emails.auto-evaluation.evaluation",
                        'subject' => "L'ENQUETE D'AUTO-EVALUATION DE GOUVERNANCE POUR L'ANNEE D'EXERCICE {$starting_evaluation->annee_exercice} A DEMARRER",
                        'content' => [
                            "greeting" => "Salut, Monsieur/Madame! {$organisation->nom_point_focal} {$organisation->prenom_point_focal}",
                            "introduction" => "Nous vous informons du démarrage de l'enquête de collecte d'auto-évaluation de gouvernance pour l'évaluation de l'auto-gouvernance de {$starting_evaluation->nom}, dans le cadre de l'année d'exercice {$starting_evaluation->annee_exercice}.",
                            "lien" => $url . "/dashboard/tools-factuel/{$organisation->pivot->token}",
                            "link_text" => "Cliquez ici pour participer à l'enquête",
                        ]
                    ];

                    // Create the notification instance with the required data
                    $notification = new EvaluationNotification($data, ['mail', 'database', 'broadcast']);

                    try {
                        $organisation->user->notify($notification);
                        // Log success or perform other actions as needed
                    } catch (\Exception $e) {
                        // Handle the exception, log an error, or notify of failure
                        Log::error('Failed to send notifications: ' . $e->getMessage());
                    }

                } else {
                    Log::error("Invalid or missing email address for user ID: {$organisation->user->nom}");
                }
            }
        }
    }

    protected function ended_evaluations_notification($endedEvaluations, string $url){

        foreach ($endedEvaluations as $key => $ended_evaluation) {

            foreach ($ended_evaluation->organisations as $key => $organisation) {

                if ((!empty($organisation->user->email)) && (filter_var($organisation->user->email, FILTER_VALIDATE_EMAIL))) {

                    $data['module'] = "cloture evaluation";
                    $data['texte'] = "Clôture de l'enquete d'auto-évaluation de Gouvernance - Année {$ended_evaluation->annee_exercice}";
                    $data['id'] = $ended_evaluation->id;
                    $data['auteurId'] = 0;

                    $data['details'] = [
                        'view' => "emails.auto-evaluation.evaluation",
                        'subject' => "Clôture de l'enquete d'auto-évaluation de Gouvernance - Année {$ended_evaluation->annee_exercice}",
                        'content' => [
                            "greeting" => "Salut, Monsieur/Madame! {$organisation->nom_point_focal} {$organisation->prenom_point_focal}",
                            "introduction" => "Nous vous informons de la clôture de l'enquête d'auto-évaluation de gouvernance du programme {$ended_evaluation->programme->nom} - Année {$ended_evaluation->annee_exercice}. \n Trouver dans le lien ci-dessous le resultat de l'enquete auto-evaluation.",
                            "lien" => $url . "/dashboard/synthese/{$ended_evaluation->secure_id}",
                            "link_text" => "Consulter le rapport final",
                        ]
                    ];

                    // Create the notification instance with the required data
                    $notification = new EvaluationNotification($data, ['mail', 'database', 'broadcast']);

                    try {
                        $organisation->user->notify($notification);
                        // Log success or perform other actions as needed
                    } catch (\Exception $e) {
                        // Handle the exception, log an error, or notify of failure
                        Log::error('Failed to send notifications: ' . $e->getMessage());
                    }

                } else {
                    Log::error("Invalid or missing email address for user ID: {$organisation->user->nom}");
                }

                AppJob::dispatch(
                    // Call the GenerateEvaluationResultats command with the evaluation ID
                    Artisan::call('generate:report-evaluation-resultats', [
                        'evaluationId' => $ended_evaluation->id
                    ])
                )->delay(now()); // Optionally add additional delay at dispatch time->addMinutes(10)
            }
        }
    }
}
