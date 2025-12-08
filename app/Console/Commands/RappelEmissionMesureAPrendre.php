<?php

namespace App\Console\Commands;

use App\Models\EvaluationDeGouvernance;
use App\Notifications\RappelEmissionPlanActionNotification;
use Illuminate\Console\Command;

class RappelEmissionMesureAPrendre extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'rappel-emission:mesure-a-prendre';

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
        if($evaluation_de_gouvernance = EvaluationDeGouvernance::/* where("annee_exercice", now()->year)->where("statut", 1)-> */where('id', 1)->first()){
            $profiles_de_gouvernance = $evaluation_de_gouvernance->failedProfilesDeGouvernance;

            foreach ($profiles_de_gouvernance as $key => $profile_de_gouvernance) {
                $profile_de_gouvernance->organisation->user->notify(new RappelEmissionPlanActionNotification([]));
            }
        }

        $this->info("Rappel emission de mesure a prendre");
        return 0;
    }
}
