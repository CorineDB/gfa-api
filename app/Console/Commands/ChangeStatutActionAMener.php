<?php

namespace App\Console\Commands;

use App\Jobs\AppJob;
use App\Models\ActionAMener;
use App\Models\Programme;
use App\Models\User;
use App\Notifications\ActionAMenerNotification;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Notification;

class ChangeStatutActionAMener extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'change-statut:action-a-mener';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

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

        $unitee_de_gestion = User::where('type', 'unitee-de-gestion')->first();

        // Fetch up to $maxUpdates evaluations that should start today and update their status to '0' (demarrage)
        $startingActions = ActionAMener:: //DB::table('evaluations_de_gouvernance')->
            where('start_at', '<=', $today)
            ->where('statut', '<', 0)
            ->get();
        
        $this->info("Generated result for soumission ID {$startingActions}:");
        
        DB::table('actions_a_mener')
            ->whereIn('id', $startingActions->pluck('id'))
            ->update(['statut' => 0]);

        /* try {
            AppJob::dispatch(
                // Your action code here, for example, sending an email, processing data, etc.
                $this->starting_actions_notification($unitee_de_gestion, $startingActions, $url)
            ); // Optionally add additional delay at dispatch time

            // Log success or perform other actions as needed
        } catch (\Exception $e) {
            // Handle the exception, log an error, or notify of failure
            Log::error('Failed to send notifications: ' . $e->getMessage());
        } */

        $endedActions = ActionAMener::where('end_at', '<=', $today)
            ->where('statut', '==', 0)
            ->get();

        // Change the status based on the date        
        DB::table('actions_a_mener')
            ->whereIn('id', $endedActions->pluck('id'))
            ->update(['statut' => 1]); // Assuming '1' indicates a finished evaluation


        $endedActions = ActionAMener::where('end_at', '<=', $today)
            ->where('statut', '==', 0)
            ->get();

        // Change the status based on the date        
        DB::table('actions_a_mener')
            ->whereIn('id', $endedActions->pluck('id'))
            ->update(['statut' => 1]); // Assuming '1' indicates a finished evaluation

        /* try {
            AppJob::dispatch(
                // Your action code here, for example, sending an email, processing data, etc.
                $this->starting_actions_notification($unitee_de_gestion, $endedActions, $url)
            ); // Optionally add additional delay at dispatch time

            // Log success or perform other actions as needed
        } catch (\Exception $e) {
            // Handle the exception, log an error, or notify of failure
            Log::error('Failed to send notifications: ' . $e->getMessage());
        } */

        return 0;
    }

    protected function starting_actions_notification($unitee_de_gestion, $startingActions, string $url)
    {
        foreach ($startingActions as $key => $starting_action) {

            $data['module'] = "demarrage evaluation";
            $data['texte'] = "Demarrage de l'evaluation d'auto-gouvernance {$starting_action->action}";
            $data['id'] = $starting_action->id;
            $data['auteurId'] = 0;
            $data['details'] = [
                'view' => "emails.auto-evaluation.evaluation",
                'subject' => "L'ENQUETE D'AUTO-EVALUATION DE GOUVERNANCE POUR L'ANNEE D'EXERCICE {$starting_action->annee_exercice} A DEMARRER",
                'content' => [
                    "lien" => $url . "/dashboard/tools-factuel/{$starting_action->organisation->pivot->token}",
                    "link_text" => "Cliquez ici pour participer à l'enquête",
                ]
            ];

            if ((!empty($starting_action->organisation->user->email)) && (filter_var($starting_action->organisation->user->email, FILTER_VALIDATE_EMAIL))) {

                /* $data['details'] = [
                    'content' => [
                        "greeting" => "Salut, Monsieur/Madame! {$starting_action->organisation->nom_point_focal} {$starting_action->organisation->prenom_point_focal}",
                        "introduction" => "Nous vous informons du démarrage de l'action {$starting_action->action} de votre plan d'action emise afin d'ameliorer votre profile de gouvernance de l'année d'exercice {$starting_action->annee_exercice}.",

                    ]
                ]; */

                $data['details']['content']['greeting'] = "Salut, Monsieur/Madame! {$starting_action->organisation->nom_point_focal} {$starting_action->organisation->prenom_point_focal}";
                $data['details']['content']['introduction'] = "Nous vous informons du démarrage de l'action {$starting_action->action} de votre plan d'action emise afin d'ameliorer votre profile de gouvernance de l'année d'exercice {$starting_action->annee_exercice}.";

                // Create the notification instance with the required data
                $notification = new ActionAMenerNotification($data, ['mail', 'database', 'broadcast']);

                try {
                    $starting_action->organisation->user->notify($notification);
                    // Log success or perform other actions as needed
                } catch (\Exception $e) {
                    // Handle the exception, log an error, or notify of failure
                    Log::error('Failed to send notifications: ' . $e->getMessage());
                }
            } else {
                Log::error("Invalid or missing email address for user ID: {$starting_action->organisation->user->nom}");
            }

            if ((!empty($unitee_de_gestion->email)) && (filter_var($unitee_de_gestion->email, FILTER_VALIDATE_EMAIL))) {

                /* 
                    $data['details'] = [
                    'subject' => "L'ENQUETE D'AUTO-EVALUATION DE GOUVERNANCE POUR L'ANNEE D'EXERCICE {$starting_action->annee_exercice} A DEMARRER",
                        'content' => [
                            "greeting" => "Salut, Monsieur/Madame! {$unitee_de_gestion->nom} {$unitee_de_gestion->prenom}",
                            "introduction" => "Nous vous informons du démarrage de l'action {$starting_action->action} du plan d'action {$starting_action->organisation->user->nom} afin d'ameliorer son profile de gouvernance de l'année d'exercice {$starting_action->annee_exercice}.",
                        ]
                    ]; 
                */

                $data['details']['content']['greeting'] = "Salut, Monsieur/Madame! {$unitee_de_gestion->nom} {$unitee_de_gestion->prenom}";
                $data['details']['content']['introduction'] = "Nous vous informons du démarrage de l'action {$starting_action->action} du plan d'action {$starting_action->organisation->user->nom} afin d'ameliorer son profile de gouvernance de l'année d'exercice {$starting_action->annee_exercice}.";

                // Create the notification instance with the required data
                $notification = new ActionAMenerNotification($data, ['mail', 'database', 'broadcast']);

                try {
                    $unitee_de_gestion->notify($notification);
                    // Log success or perform other actions as needed
                } catch (\Exception $e) {
                    // Handle the exception, log an error, or notify of failure
                    Log::error('Failed to send notifications: ' . $e->getMessage());
                }
            } else {
                Log::error("Invalid or missing email address for user ID: {$unitee_de_gestion->nom}");
            }
        }
    }

    protected function ended_actions_notification($unitee_de_gestion, $endedActions, string $url)
    {
        foreach ($endedActions as $key => $ended_action) {

            $data['module'] = "cloture evaluation";
            $data['texte'] = "Clôture de l'enquete d'auto-évaluation de Gouvernance - Année {$ended_action->annee_exercice}";
            $data['id'] = $ended_action->id;
            $data['auteurId'] = 0;

            $data['details'] = [
                'view' => "emails.auto-evaluation.evaluation",
                'subject' => "Clôture de l'action a mener d'auto-évaluation de Gouvernance - Année {$ended_action->annee_exercice}",
                'content' => [
                    "lien" => $url . "/dashboard/synthese/{$ended_action->secure_id}",
                    "link_text" => "Consulter le rapport final",
                ]
            ];

            if ((!empty($ended_action->organisation->user->email)) && (filter_var($ended_action->organisation->user->email, FILTER_VALIDATE_EMAIL))) {

                $data['module'] = "cloture evaluation";
                $data['texte'] = "Clôture de l'enquete d'auto-évaluation de Gouvernance - Année {$ended_action->annee_exercice}";
                $data['id'] = $ended_action->id;
                $data['auteurId'] = 0;

                /* 
                    $data['details'] = [
                        'content' => [
                            "greeting" => "Salut, Monsieur/Madame! {$ended_action->organisation->nom_point_focal} {$ended_action->organisation->prenom_point_focal}",
                            "introduction" => "Nous vous informons de la clôture de l'enquête d'auto-évaluation de gouvernance du programme {$ended_action->programme->nom} - Année {$ended_action->annee_exercice}. \n Trouver dans le lien ci-dessous le resultat de l'enquete auto-evaluation.",
                        ]
                    ];
                */
                $data['details']['content']['greeting'] = "Salut, Monsieur/Madame! {$ended_action->organisation->nom_point_focal} {$ended_action->organisation->prenom_point_focal}";
                $data['details']['content']['introduction'] = "Nous vous informons de la clôture de l'enquête d'auto-évaluation de gouvernance du programme {$ended_action->programme->nom} - Année {$ended_action->annee_exercice}. \n Trouver dans le lien ci-dessous le resultat de l'enquete auto-evaluation.";


                // Create the notification instance with the required data
                $notification = new ActionAMenerNotification($data, ['mail', 'database', 'broadcast']);

                try {
                    $ended_action->organisation->user->notify($notification);
                    // Log success or perform other actions as needed
                } catch (\Exception $e) {
                    // Handle the exception, log an error, or notify of failure
                    Log::error('Failed to send notifications: ' . $e->getMessage());
                }
            } else {
                Log::error("Invalid or missing email address for user ID: {$ended_action->organisation->user->nom}");
            }

            if ((!empty($unitee_de_gestion->email)) && (filter_var($unitee_de_gestion->email, FILTER_VALIDATE_EMAIL))) {

                /* $data['details'] = [
                    'content' => [
                        "greeting" => "Salut, Monsieur/Madame! {$unitee_de_gestion->nom} {$unitee_de_gestion->prenom}",
                        "introduction" => "Nous vous informons du démarrage de l'action {$ended_action->action} du plan d'action {$ended_action->organisation->user->nom} afin d'ameliorer son profile de gouvernance de l'année d'exercice {$ended_action->annee_exercice}.",
                    ]
                ]; */

                $data['details']['content']['greeting'] = "Salut, Monsieur/Madame! {$unitee_de_gestion->nom} {$unitee_de_gestion->prenom}";
                $data['details']['content']['introduction'] = "Nous vous informons du démarrage de l'action {$ended_action->action} du plan d'action {$ended_action->organisation->user->nom} afin d'ameliorer son profile de gouvernance de l'année d'exercice {$ended_action->annee_exercice}.";

                // Create the notification instance with the required data
                $notification = new ActionAMenerNotification($data, ['mail', 'database', 'broadcast']);

                try {
                    $unitee_de_gestion->notify($notification);
                    // Log success or perform other actions as needed
                } catch (\Exception $e) {
                    // Handle the exception, log an error, or notify of failure
                    Log::error('Failed to send notifications: ' . $e->getMessage());
                }
            } else {
                Log::error("Invalid or missing email address for user ID: {$unitee_de_gestion->nom}");
            }
        }
    }
}
