<?php

namespace App\Console\Commands;

use App\Events\NewNotification;
use App\Jobs\RappelJob;
use App\Models\EvaluationDeGouvernance;
use App\Notifications\RappelNotification;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;

class ChangeStatutEvaluations extends Command
{
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
        $startingEvaluations = EvaluationDeGouvernance:://DB::table('evaluations_de_gouvernance')->
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
            
            $data['texte'] = $starting_evaluation->description;
            $data['id'] = $starting_evaluation->id;
            $data['auteurId'] = 0;
            $notification = new RappelNotification($data);

            foreach ($starting_evaluation->organisations as $key => $organisation) {

                $organisation->user->notify($notification);
    
                $notification = $organisation->user->notifications->last();
    
                event(new NewNotification($this->formatageNotification($notification, $organisation->user)));
    
                RappelJob::dispatch($organisation->user, $organisation->description);
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
            
            $data['texte'] = $ended_evaluation->description;
            $data['id'] = $ended_evaluation->id;
            $data['auteurId'] = 0;
            $notification = new RappelNotification($data);

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
