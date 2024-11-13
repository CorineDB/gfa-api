<?php

namespace App\Console\Commands;

use App\Events\InAppNotification;
use App\Events\NewNotification;
use App\Jobs\RappelJob;
use App\Models\EvaluationDeGouvernance;
use App\Notifications\EvaluationNotification;
use App\Notifications\RappelNotification;
use App\Traits\Helpers\HelperTrait;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Notification;

class ChangeStatutEvaluations extends Command
{
    use HelperTrait;
    
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'command:change-statut-evaluations';

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

        // Fetch up to $maxUpdates evaluations that should start today and update their status to '0' (demarrage)
        $startingEvaluations = EvaluationDeGouvernance:: //DB::table('evaluations_de_gouvernance')->
            where('debut', '<=', $today)
            ->where('statut', '<', 0)
            ->get();

        /*
            DB::table('evaluations_de_gouvernance')
                ->where(function($query) use ($today) {
                    $query->where('debut', '<=', $today)
                        ->orWhere('fin', '<=', $today);
                })
                ->update([
                    'statut' => DB::raw("CASE 
                                            WHEN debut <= '$today' THEN 0 
                                            WHEN fin <= '$today' THEN 1 
                                        END")
                ]);

        */

        DB::table('evaluations_de_gouvernance')
            ->whereIn('id', $startingEvaluations->pluck('id'))
            ->update(['statut' => 0]);

        foreach ($startingEvaluations as $key => $starting_evaluation) {

            // Get all users associated with the organizations of the starting evaluation
            $users = $starting_evaluation->organisations_user();

            // Check if there are users to notify
            if ($users->isNotEmpty()) {
                $url = config("app.url");

                // If the URL is localhost, append the appropriate IP address and port
                if (strpos($url, 'localhost') !== false) {
                    $url = '192.168.1.16:3000';
                }

                $data['module'] = "Demarrage d'une evaluation";
                $data['texte'] = "Demarrage de l'evaluation d'auto-gouvernance {$starting_evaluation->nom}";
                $data['id'] = $starting_evaluation->id;
                $data['auteurId'] = 0;

                $data['details'] = [
                    'view' => "emails.auto-evaluation.evaluation",
                    'subject' => "L'ENQUETE D'AUTO-EVALUATION DE GOUVERNANCE POUR L'ANNEE D'EXERCICE {$starting_evaluation->annee_exercice} A DEMARRER",
                    'content' => [
                        "greeting" => "Salut, Monsieur/Madame!",
                        "introduction" => "Nous vous informons du démarrage de l'enquête de collecte d'auto-évaluation de gouvernance pour l'évaluation de l'auto-gouvernance de {$starting_evaluation->nom}, dans le cadre de l'année d'exercice {$starting_evaluation->annee_exercice}. Votre participation est essentielle pour cette activité de gouvernance. Nous vous invitons à prendre part à cette évaluation.",
                        "lien" => $url . "/tools-factuel/{$starting_evaluation->id}",
                        "link_text" => "Cliquez ici pour participer à l'enquête",
                    ]
                ];

                // Create the notification instance with the required data
                $notification = new EvaluationNotification($data, ['mail', 'database', 'broadcast']);

                // Send the notification to all users at once
                Notification::send($users, $notification);

            }

        }

        $endedEvaluations = EvaluationDeGouvernance::where('fin', '<=', $today)
            ->where('statut', '==', 0)
            ->get();

        // Change the status based on the date        
        DB::table('evaluations_de_gouvernance')
            ->whereIn('id', $endedEvaluations->pluck('id'))
            ->update(['statut' => 1]); // Assuming '1' indicates a finished evaluation

        // Get all evaluations that have now ended
        $endedEvaluations = DB::table('evaluations_de_gouvernance')
            ->where('fin', '<=', $today)
            ->where('statut', 1) // Get evaluations just updated to finished
            ->get();

        foreach ($endedEvaluations as $key => $ended_evaluation) {

            // Get all users associated with the organizations of the starting evaluation
            $users = $ended_evaluation->organisations_user();

            // Check if there are users to notify
            if ($users->isNotEmpty()) {

                $data['texte'] = "Fin de l'evaluation d'auto-gouvernance";
                $data['id'] = $ended_evaluation->id;
                $data['auteurId'] = 0;

                $data['details'] = [
                    'subject' => "ALERTE DEMARRAGE DE L'EVALUATION DE GOUVERNANCE",
                    'content' => [
                        "greeting" => "Salut, Monsieur/Madame!",
                        "introduction" => "Vous ALERTE DEMARRAGE ACTIVITE à participer à l'enquête de collecte auto-evaluation de gouvernance de {$ended_evaluation->nom} de l'annee d'exercice {$ended_evaluation->annee_exercice}.",
                        "lien" => "",
                    ]
                ];

                // Create the notification instance with the required data
                $notification = new EvaluationNotification($data, ['database', 'broadcast', 'mail']);

                // Send the notification to all users at once
                Notification::send($users, $notification);
            }

            foreach ($ended_evaluation->organisations as $key => $organisation) {

                $organisation->user->notify($notification);

                $notification = $organisation->user->notifications->last();

                event(new NewNotification($this->formatageNotification($notification, $organisation->user)));

                RappelJob::dispatch($organisation->user, $organisation->description);
            }

            // Call the GenerateEvaluationResultats command with the evaluation ID
            Artisan::call('command:generate-evaluation-resultats', [
                'evaluationId' => $ended_evaluation->id
            ]);
        }

        return 0;
    }
}
