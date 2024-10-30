<?php

namespace App\Console\Commands;

use Carbon\Carbon;
use Illuminate\Console\Command;
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
            
        return 0;
    }
}
