<?php

namespace App\Console\Commands;

use App\Jobs\SuiviJob;
use App\Events\NewNotification;
use App\Models\AlerteConfig;
use App\Models\User;
use App\Notifications\SuiviNotification;
use App\Traits\Helpers\HelperTrait;
use Carbon\Carbon;
use Illuminate\Console\Command;

class Suivi extends Command
{
    use HelperTrait;
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'command:suivi';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Envoie des alertes pour les suivis';

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
        $financierConfig = AlerteConfig::where('module', 'suivi-financier')->first();
        $indicateurConfig = AlerteConfig::where('module', 'suivi-indicateur')->first();

        // Create default config if it doesn't exist
        if (!$financierConfig) {
            $financierConfig = AlerteConfig::create([
                'module' => 'suivi-financier',
                'nombreDeJourAvant' => 0,
                'frequence' => 30,
                'debutSuivi' => date('Y-m-d')
            ]);
        }

        if($financierConfig && date('Y-m-d') >= $financierConfig->debutSuivi)
        {

            $debutSuivi = Carbon::parse($financierConfig->debutSuivi); // Convert start date to Carbon instance
            $today = Carbon::today(); // Get today's date

            $daysDifference = $debutSuivi->diffInDays($today); // Calculate the difference in days

            if ($daysDifference % $financierConfig->frequence == 0)

            //if(($financierConfig->debutSuivi - date('Y-m-d'))%$financierConfig->frequence == 0)
            {
                $allUsers = User::all();
                foreach($allUsers as $user)
                {
                    if($user->hasPermissionTo('alerte-suivi-financier'))
                    {
                        $data['texte'] = "ALERTE RAPPEL SUIVI INDICATEUR";
                        $data['id'] = null;
                        $data['auteurId'] = 0;
                        $notification = new SuiviNotification($data);

                        $user->notify($notification);

                        $notification = $user->notifications->last();

                        event(new NewNotification($this->formatageNotification($notification, $user)));

                        SuiviJob::dispatch($user, 'financier')->delay(10);
                    }
                }
            }
        }

        // Create default config if it doesn't exist
        if (!$indicateurConfig) {
            $indicateurConfig = AlerteConfig::create([
                'module' => 'suivi-indicateur',
                'nombreDeJourAvant' => 0,
                'frequence' => 30,
                'debutSuivi' => date('Y-m-d')
            ]);
        }

        if($indicateurConfig && date('Y-m-d') >= $indicateurConfig->debutSuivi)
        {
            $debutSuivi = Carbon::parse($indicateurConfig->debutSuivi); // Convert start date to Carbon instance
            $today = Carbon::today(); // Get today's date

            $daysDifference = $debutSuivi->diffInDays($today); // Calculate the difference in days

            if ($daysDifference % $indicateurConfig->frequence == 0)

            //if(($indicateurConfig->debutSuivi - date('Y-m-d')) % $indicateurConfig->frequence == 0)
            {
                $allUsers = User::all();
                foreach($allUsers as $user)
                {
                    if($user->hasPermissionTo('alerte-suivi-indicateur'))
                    {
                        $data['texte'] = "ALERTE RAPPEL SUIVI FINANCIER";
                        $data['id'] = null;
                        $data['auteurId'] = 0;
                        $notification = new SuiviNotification($data);

                        $user->notify($notification);

                        $notification = $user->notifications->last();

                        event(new NewNotification($this->formatageNotification($notification, $user)));

                        SuiviJob::dispatch($user, 'indicateur')->delay(10);
                    }
                }
            }
        }
    }
}
