<?php

namespace App\Console\Commands;

use App\Events\NewNotification;
use App\Jobs\DemarrageJob;
use App\Models\Activite;
use App\Models\AlerteConfig;
use App\Models\Tache;
use App\Models\User;
use App\Notifications\DemarrageNotification;
use App\Traits\Helpers\HelperTrait;
use Illuminate\Console\Command;
use Illuminate\Support\Carbon;

class Demarrage extends Command
{
    use HelperTrait;
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'command:demarrage';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Envoie des alertes quelques jours avant le demarrage des activités et taches';

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

        $taches = Tache::all();

        $tacheConfig = AlerteConfig::where('module', 'tache')->first();

        foreach($taches as $tache)
        {
            $statut = $tache->statut;

            if($statut == -1)
            {
                // Skip if tache has no duree
                if (!$tache->duree) {
                    continue;
                }

                $debut = $tache->duree->debut;
                $days = "-".$tacheConfig->nombreDeJourAvant." days";

                if(date('Y-m-d', strtotime($debut.$days)) == date('Y-m-d'))
                {
                    $allUsers = User::where('programmeId', $tache->activite->composante->projet->programmeId)->get();
                    foreach($allUsers as $user)
                    {
                        if($user->hasPermissionTo('alerte-tache'))
                        {

                            for($days = "0 days", $frequence = 0; $frequence <= $tacheConfig->frequence; $frequence ++, $days = $frequence." days")
                            {
                                $carbonDate = strtotime(date('Y-m-d', strtotime($days)));

                                if($carbonDate - time() < 0)
                                {
                                    $carbonDate = time() + 60;
                                }

                                $data['texte'] = "La tache: ".$tache->nom." a demarrée";
                                $data['id'] = $tache->id;
                                $data['auteurId'] = 0;
                                $notification = new DemarrageNotification($data);

                                $user->notify($notification);

                                $notification = $user->notifications->last();

                                event(new NewNotification($this->formatageNotification($notification, $user)));

                                DemarrageJob::dispatch($user, null, $tache, 'tache', $debut)->delay($carbonDate - time());
                            }

                        }
                    }
                }
            }
        }

        $activites = Activite::all();

        $activiteConfig = AlerteConfig::where('module', 'activite')->first();

        foreach($activites as $activite)
        {

            $statut = $activite->statut;

            if($statut == -1)
            {
                // Skip if activite has no duree
                if (!$activite->duree) {
                    continue;
                }

                $debut = $activite->duree->debut;
                $days = " -".$activiteConfig->nombreDeJourAvant." days";

                if(date('Y-m-d', strtotime($debut.$days)) == date('Y-m-d'))
                {

                    $allUsers = User::where('programmeId', $activite->composante->projet->programmeId)->get();
                    foreach($allUsers as $user)
                    {
                        if($user->hasPermissionTo('alerte-activite'))
                        {

                            for($days = "0 days", $frequence = 0; $frequence <= $activiteConfig->frequence; $frequence ++, $days = $frequence." days")
                            {
                                $carbonDate = strtotime(date('Y-m-d', strtotime($days)));

                                if($carbonDate - time() < 0)
                                {
                                    $carbonDate = time() + 60;
                                }

                                $data['texte'] = "L'activite: ".$activite->nom." a demarrée";
                                $data['id'] = $activite->id;
                                $data['auteurId'] = 0;
                                $notification = new DemarrageNotification($data);

                                $user->notify($notification);

                                $notification = $user->notifications->last();

                                event(new NewNotification($this->formatageNotification($notification, $user)));

                                DemarrageJob::dispatch($user, $activite, null, 'activite', $debut)->delay($carbonDate - time());
                            }

                        }
                    }
                }
            }
        }

    }

}
