<?php

namespace App\Console\Commands;

use App\Events\NewNotification;
use App\Jobs\ChangementStatutJob;
use App\Models\Activite;
use App\Models\Projet;
use App\Models\Tache;
use App\Models\User;
use App\Notifications\ChangementStatutNotification;
use App\Traits\Helpers\HelperTrait;
use Illuminate\Console\Command;

class ChangeStatut extends Command
{
    use HelperTrait;
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'command:change-statut';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Change le statut des taches activites en en retard';

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

        foreach($taches as $tache)
        {
            // Skip if tache has no duree
            if (!$tache->duree) {
                continue;
            }

            $fin = $tache->duree->fin;
            $debut = $tache->duree->debut;
            $statut = $tache->statut;

            if($statut == -1 && $debut <= date('Y-m-d'))
            {
                /*$etat = ['etat' => 0];
                $statut = $tache->statuts()->create($etat);*/
                $tache->statut = 0;
                $tache->save();
                $tache->suivis()->create(["poidsActuel" => 50, "programmeId" => $tache->programmeId, "commentaire" => "Etat actuel"]);
                $tache->refresh();

                $allUsers = User::where('programmeId', $tache->activite->composante->projet->programmeId);
                foreach($allUsers as $user)
                {
                    if($user->hasPermissionTo('alerte-tache'))
                    {
                        $data['texte'] = "Le statut de la tache: ".$tache->nom." a changé";
                        $data['id'] = $tache->id;
                        $data['auteurId'] = 0;
                        $notification = new ChangementStatutNotification($data);

                        $user->notify($notification);

                        $notification = $user->notifications->last();

                        event(new NewNotification($this->formatageNotification($notification, $user)));

             //           ChangementStatutJob::dispatch($user,null, $tache, 'tache', 'en cours')->delay(10);
                    }
                }
            }

            else if($statut < 1 && $fin < date('Y-m-d'))
            {
                /*$etat = ['etat' => 1];
                $statut = $tache->statuts()->create($etat);*/
                $tache->statut = 1;
                $tache->save();

                $allUsers = User::where('programmeId', $tache->activite->composante->projet->programmeId);
                foreach($allUsers as $user)
                {
                    if($user->hasPermissionTo('alerte-tache'))
                    {
                        $data['texte'] = "Le statut de la tache: ".$tache->nom." a changé";
                        $data['id'] = $tache->id;
                        $data['auteurId'] = 0;
                        $notification = new ChangementStatutNotification($data);

                        $user->notify($notification);

                        $notification = $user->notifications->last();

                        event(new NewNotification($this->formatageNotification($notification, $user)));

                        ChangementStatutJob::dispatch($user,null, $tache, 'tache', 'en retard')->delay(10);

                    }
                }

            }

            else if($statut == 1 && $fin >= date('Y-m-d'))
            {
                /*$etat = ['etat' => 0];
                $statut = $this->statuts()->create($etat);*/
                $tache->statut = 0;
                $tache->save();

                $allUsers = User::where('programmeId', $tache->activite->composante->projet->programmeId);
                foreach($allUsers as $user)
                {
                    if($user->hasPermissionTo('alerte-tache'))
                    {
                        $data['texte'] = "Le statut de l'tache: ".$tache->nom." a changé";
                        $data['id'] = $tache->id;
                        $data['auteurId'] = 0;
                        $notification = new ChangementStatutNotification($data);

                        $user->notify($notification);

                        $notification = $user->notifications->last();

                        event(new NewNotification($this->formatageNotification($notification, $user)));

                        ChangementStatutJob::dispatch($user,$tache, null, 'tache', 'en cours')->delay(10);
                    }
                }
            }

        }

        $activites = Activite::all();

        foreach($activites as $activite)
        {
            // Skip if activite has no duree
            if (!$activite->duree) {
                continue;
            }

            $fin = $activite->duree->fin;
            $debut = $activite->duree->debut;
            $statut = $activite->statut;

            if($statut == -1 && $debut <= date('Y-m-d'))
            {
                /*$etat = ['etat' => 0];
                $statut = $activite->statuts()->create($etat);*/
                $activite->statut = 0;
                $activite->save();

                $allUsers = User::where('programmeId', $activite->composante->projet->programmeId)->get();
                foreach($allUsers as $user)
                {
                    if($user->hasPermissionTo('alerte-activite'))
                    {
                        $data['texte'] = "Le statut de l'activite: ".$activite->nom." a changé";
                        $data['id'] = $activite->id;
                        $data['auteurId'] = 0;
                        $notification = new ChangementStatutNotification($data);

                        $user->notify($notification);

                        $notification = $user->notifications->last();

                   //     event(new NewNotification($this->formatageNotification($notification, $user)));

                        ChangementStatutJob::dispatch($user,$activite, null, 'activite', 'en cours')->delay(10);
                    }
                }
            }

            else if($statut < 1 && $fin < date('Y-m-d'))
            {
                /*$etat = ['etat' => 1];
                $statut = $activite->statuts()->create($etat);*/
                $tache->statut = 1;
                $tache->save();

                $allUsers = User::where('programmeId', $activite->composante->projet->programmeId);
                foreach($allUsers as $user)
                {
                    if($user->hasPermissionTo('alerte-activite'))
                    {
                        $data['texte'] = "Le statut de l'activite: ".$activite->nom." a changé";
                        $data['id'] = $activite->id;
                        $data['auteurId'] = 0;
                        $notification = new ChangementStatutNotification($data);

                        $user->notify($notification);

                        $notification = $user->notifications->last();

                    //    event(new NewNotification($this->formatageNotification($notification, $user)));

                    //wens    ChangementStatutJob::dispatch($user,$activite, null, 'activite', 'en retard')->delay(10);
                    }
                }
            }

            else if($statut == 1 && $fin > date('Y-m-d'))
            {
                /*$etat = ['etat' => 0];
                $statut = $this->statuts()->create($etat);*/

                $activite->statut = 0;
                $activite->save();

                $allUsers = User::where('programmeId', $activite->composante->projet->programmeId);
                foreach($allUsers as $user)
                {
                    if($user->hasPermissionTo('alerte-activite'))
                    {
                        $data['texte'] = "Le statut de l'activite: ".$activite->nom." a changé";
                        $data['id'] = $activite->id;
                        $data['auteurId'] = 0;
                        $notification = new ChangementStatutNotification($data);

                        $user->notify($notification);

                        $notification = $user->notifications->last();

                    //    event(new NewNotification($this->formatageNotification($notification, $user)));

                       // ChangementStatutJob::dispatch($user,$activite, null, 'activite', 'en cours')->delay(10);
                    }
                }
            }

        }

        $projets = Projet::all();

        foreach($projets as $projet)
        {

            $fin = $projet->fin;
            $debut = $projet->debut;
            $statut = $projet->statut;

            if($statut == -1 && $debut <= date('Y-m-d'))
            {
                /*$etat = ['etat' => 0];
                $statut = $projet->statuts()->create($etat);*/

                $projet->statut = 0;
                $projet->save();

                /*$allUsers = User::where('programmeId', $projet->composante->projet->programmeId)->get();
                foreach($allUsers as $user)
                {
                    if($user->hasPermissionTo('alerte-projet'))
                    {
                        $data['texte'] = "Le statut de l'projet: ".$projet->nom." a changé";
                        $data['id'] = $projet->id;
                        $data['auteurId'] = 0;
                        $notification = new ChangementStatutNotification($data);

                        $user->notify($notification);

                        $notification = $user->notifications->last();

                        event(new NewNotification($this->formatageNotification($notification, $user)));

                        ChangementStatutJob::dispatch($user,$projet, null, 'projet', 'en cours')->delay(10);
                    }
                }*/
            }

            else if($statut < 1 && $fin < date('Y-m-d'))
            {
                /*$etat = ['etat' => 1];
                $statut = $projet->statuts()->create($etat);*/

                $projet->statut = 1;
                $projet->save();

                /*$allUsers = User::where('programmeId', $tache->projet->composante->projet->programmeId);
                foreach($allUsers as $user)
                {
                    if($user->hasPermissionTo('alerte-projet'))
                    {
                        $data['texte'] = "Le statut de l'projet: ".$projet->nom." a changé";
                        $data['id'] = $projet->id;
                        $data['auteurId'] = 0;
                        $notification = new ChangementStatutNotification($data);

                        $user->notify($notification);

                        $notification = $user->notifications->last();

                        event(new NewNotification($this->formatageNotification($notification, $user)));

                        ChangementStatutJob::dispatch($user,$activite, null, 'activite', 'en retard')->delay(10);
                    }
                }*/
            }

        }

    }

}
