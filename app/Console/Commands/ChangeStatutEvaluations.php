<?php

namespace App\Console\Commands;

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

        
        // Change the status based on the date
        DB::table('evaluations_de_gouvernance')
            ->where('fin', '<=', $today)
            ->update(['statut' => 1]); // Assuming '1' indicates a finished evaluation

        // Get all evaluations that have now ended
        $endedEvaluations = DB::table('evaluations_de_gouvernance')
            ->where('fin', '<=', $today)
            ->where('statut', 1) // Get evaluations just updated to finished
            ->get();

        foreach ($endedEvaluations as $evaluation) {
            // Dispatch the GenerateEvaluationResultats command for each evaluation
            Artisan::call('command:generate-evaluation-resultats', [
                '--evaluation' => $evaluation->id
            ]);
        }
            
        return 0;
    }
}
