<?php

namespace App\Console\Commands;

use App\Models\EvaluationDeGouvernance;
use App\Notifications\AlertUnderPerformance;
use Carbon\Carbon;
use Illuminate\Console\Command;

class CheckUnderPerformPartner extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'gouvernance:check-under-performance';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Check underperforming partners and send alerts with recommendations and action plan';

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
        // Set the threshold score
        $seuil = 0.5;

        // Get the evaluation for the current year
        $evaluation_de_gouvernance = EvaluationDeGouvernance::where('annee_exercice', Carbon::now()->year)
                                                            ->where('statut', 1)
                                                            ->first();

        if ($evaluation_de_gouvernance) {
            // Get the partners (organisations) who are underperforming based on the seuil
            $underperforming_partners = $evaluation_de_gouvernance->failedProfilesDeGouvernance($seuil);

            foreach ($underperforming_partners as $partner) {
                // Send the alert with recommendations and action plan to the partner
                $partner->organisation->notify(new AlertUnderPerformance($seuil));

                $this->info("Alert sent to underperforming partner: {$partner->organisation->name}");
            }

            $this->info("Alerts sent to all underperforming partners.");
        } else {
            $this->info("No governance evaluation found for the current year.");
        }
    }
}
