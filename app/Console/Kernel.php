<?php

namespace App\Console;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

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
        // Génération de résultats - 1x/jour
        $schedule->command('gouvernance:generate-results')->daily();

        // Statuts évaluations - 1x/heure (optimisé depuis everyFifteenMinutes)
        $schedule->command('update-evaluation-statuses')->hourly();

        // Emails expiration mot de passe - 1x/jour à 09:00 (optimisé depuis everyFifteenMinutes)
        $schedule->command('send-password-validity-expiration-soon-mail')->dailyAt('09:00');

        // Changement statut tâches/activités - 1x/heure (optimisé depuis everyFifteenMinutes)
        $schedule->command('command:change-statut')->hourly();

        // Alertes démarrage - 1x/jour à 07:00 avant la journée (optimisé depuis everyFifteenMinutes)
        $schedule->command('command:demarrage')->dailyAt('07:00');

        // Génération rapports quotidiens - 1x/jour à 16:00 (optimisé depuis everyFifteenMinutes)
        $schedule->command('command:rapport')->dailyAt('16:00');

        // Suivi financier/indicateurs - 1x/jour à 08:00 (optimisé depuis everyMinute)
        $schedule->command('command:suivi')->dailyAt('08:00');

        // Rappel émission mesures à prendre - 1x/jour
        $schedule->command('rappel-emission:mesure-a-prendre')->daily();

        // Changement statut actions à mener - 1x/jour à minuit
        $schedule->command('change-statut:action-a-mener')->dailyAt('00:00');

        // Commandes désactivées
        // $schedule->command('command:rappel')->everyMinute();
        // $schedule->command('change-statut:evaluations')->everyFifteenMinutes();
        // $schedule->command('generate:report-for-validated-soumissions')->daily();

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
