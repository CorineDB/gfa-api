<?php

namespace App\Console\Commands;
use App\Events\NewNotification;
use App\Jobs\RapportJob;
use App\Models\AlerteConfig;
use App\Models\User;
use App\Notifications\RapportNotification;
use App\Traits\Helpers\HelperTrait;
use Illuminate\Console\Command;

class Rapport extends Command
{
    use HelperTrait;
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'command:rapport';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Envoie des alertes quelques jours avant le jour de presentation des rapports';

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
        $entrepriseConfig = AlerteConfig::where('module', 'rapport-entreprise')->first();
        $missionDeControleConfig = AlerteConfig::where('module', 'rapport-mission-de-controle')->first();
        $chefEnvironnementConfig = AlerteConfig::where('module', 'rapport-chef-environnemental')->first();

        // Create default config if it doesn't exist
        if (!$entrepriseConfig) {
            $entrepriseConfig = AlerteConfig::create([
                'module' => 'rapport-entreprise',
                'nombreDeJourAvant' => 5,
                'frequenceRapport' => 30
            ]);
        }

        if (!$missionDeControleConfig) {
            $missionDeControleConfig = AlerteConfig::create([
                'module' => 'rapport-mission-de-controle',
                'nombreDeJourAvant' => 5,
                'frequenceRapport' => 30
            ]);
        }

        if (!$chefEnvironnementConfig) {
            $chefEnvironnementConfig = AlerteConfig::create([
                'module' => 'rapport-chef-environnemental',
                'nombreDeJourAvant' => 5,
                'frequenceRapport' => 30
            ]);
        }

        if(date('d') == ($entrepriseConfig->frequenceRapport - $entrepriseConfig->nombreDeJourAvant))
        {
            $allUsers = User::all();
                foreach($allUsers as $user)
                {
                    if($user->hasPermissionTo('alerte-creer-rapport-entreprise'))
                    {
                        $data['texte'] = "Il est temps de faire un rapport";
                        $data['id'] = null;
                        $data['auteurId'] = 0;
                        $notification = new RapportNotification($data);

                        $user->notify($notification);

                        $notification = $user->notifications->last();

                        event(new NewNotification($this->formatageNotification($notification, $user)));

                        RapportJob::dispatch($user)->delay(10);
                    }
                }
        }

        if(date('d') == ($missionDeControleConfig->frequenceRapport - $missionDeControleConfig->nombreDeJourAvant))
        {
            $allUsers = User::all();
                foreach($allUsers as $user)
                {
                    if($user->hasPermissionTo('alerte-creer-rapport-missionDeControle'))
                    {
                        $data['texte'] = "Il est temps de faire un rapport";
                        $data['id'] = null;
                        $data['auteurId'] = 0;
                        $notification = new RapportNotification($data);

                        $user->notify($notification);

                        $notification = $user->notifications->last();

                        event(new NewNotification($this->formatageNotification($notification, $user)));

                        RapportJob::dispatch($user)->delay(10);
                    }
                }
        }

        if(date('d') == ($chefEnvironnementConfig->frequenceRapport - $chefEnvironnementConfig->nombreDeJourAvant))
        {
            $allUsers = User::all();
                foreach($allUsers as $user)
                {
                    if($user->hasPermissionTo('alerte-creer-rapport-chefEnvironnement'))
                    {
                        $data['texte'] = "Il est temps de faire un rapport";
                        $data['id'] = null;
                        $data['auteurId'] = 0;
                        $notification = new RapportNotification($data);

                        $user->notify($notification);

                        $notification = $user->notifications->last();

                        event(new NewNotification($this->formatageNotification($notification, $user)));

                        RapportJob::dispatch($user)->delay(10);
                    }
                }
        }
    }
}
