<?php

namespace App\Console;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;
use App\Models\AlerteConfig;

class Kernel extends ConsoleKernel
{

    protected $commands = [
        Commands\SendPasswordValidityExpirationSoonMail::class,
        Commands\ChangeStatut::class,
        Commands\ChangeStatutEvaluations::class,
        Commands\GenerateEvaluationResultats::class,
        Commands\GenerateResultatsForValidatedSoumission::class,
        Commands\RappelEmissionMesureAPrendre::class,
        Commands\ChangeStatutActionAMener::class,
        Commands\Demarrage::class,
        Commands\RappelCron::class,
        Commands\Rapport::class,
        Commands\enquetes_de_gouvernance\GenerateResultatsForValidatedSoumission::class,
        Commands\enquetes_de_gouvernance\UpdateEvaluationStatuses::class,
    ];
    /**
     * Define the application's command schedule.
     *
     * @param  \Illuminate\Console\Scheduling\Schedule  $schedule
     * @return void
     */
    protected function schedule(Schedule $schedule)
    {

        $schedule->command('gouvernance:generate-results')->daily();
        $schedule->command('update-evaluation-statuses')->everyFifteenMinutes();

        // $schedule->command('inspire')->hourly
       $schedule->command('send-password-validity-expiration-soon-mail')->everyFifteenMinutes();

        $schedule->command('command:change-statut')->everyFifteenMinutes();
        //$schedule->command('change-statut:evaluations')->everyFifteenMinutes();//->dailyAt('00:00');
        // $schedule->command('generate:report-for-validated-soumissions')->daily();

       $schedule->command('command:demarrage')->everyFifteenMinutes();

       $schedule->command('command:rapport')->everyFifteenMinutes();
       $schedule->command('command:suivi')->everyMinute();//->dailyAt('00:00');//->everyFifteenMinutes();

        //$schedule->command('command:rappel')->everyMinute();

        $schedule->command('rappel-emission:mesure-a-prendre')->daily();

        $schedule->command('change-statut:action-a-mener')->dailyAt('00:00');

       /*$backupConfig = AlerteConfig::where('module', 'backup')->first();

       if($backupConfig){

            $backupFrequence = $backupConfig->frequenceBackup;

            $schedule->command('backup:run')->$backupFrequence();
        }
        $schedule->command('gauge:prune')->daily();
      */

    }

    /**
     * Register the commands for the application.
     *
     * @return void
     */
    protected function commands()
    {
        $this->load(__DIR__.'/Commands');

        require base_path('routes/console.php');
    }
}
