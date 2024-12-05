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
        Commands\Suivi::class,
    ];
    /**
     * Define the application's command schedule.
     *
     * @param  \Illuminate\Console\Scheduling\Schedule  $schedule
     * @return void
     */
    protected function schedule(Schedule $schedule)
    {
        // $schedule->command('inspire')->hourly
        $schedule->command('send-password-validity-expiration-soon-mail')->everyMinute();

        $schedule->command('command:change-statut')->everyMinute();
        $schedule->command('change-statut:evaluations')->everyMinute();
        $schedule->command('generate:report-soumissions-resultats')->everyMinute();

        $schedule->command('command:demarrage')->everyMinute();

        $schedule->command('command:rapport')->everyMinute();

        $schedule->command('command:rappel')->everyMinute();

        //$schedule->command('command:suivi')->everyMinute();

        $schedule->command('rappel-emission:mesure-a-prendre')->daily();

        $schedule->command('change-statut:action-a-mener')->dailyAt('00:00');

        $backupConfig = AlerteConfig::where('module', 'backup')->first();

        $backupFrequence = $backupConfig->frequenceBackup;

        $schedule->command('backup:run')->$backupFrequence();

        $schedule->command('gauge:prune')->daily();

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
