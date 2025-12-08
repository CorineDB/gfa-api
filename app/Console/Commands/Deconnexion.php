<?php

namespace App\Console\Commands;

use App\Jobs\GenererPta;
use App\Models\Programme;
use App\Models\User;
use Illuminate\Console\Command;
use App\Events\PtaNotification;
use App\Traits\Helpers\Pta;
use App\Models\Projet;
use App\Traits\Helpers\HelperTrait;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;

class Deconnexion extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'command:pta {annee} ';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Deconnecte tout les utilisateur';

    use Pta;
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
        /*$users = User::all();

        foreach($users as $user)
        {

            $user->token = null;

            // Sauvegarder les informations
            $user->save();

            $user->tokens()->delete();
        }*/

        /*$user = User::where('email', $this->argument('user'))->first();

        if($user == null)
        {
            $message = "Ce utilisateur n'existe pas";

            return dump($message);
        }

        $user->token = null;

        // Sauvegarder les informations
        $user->save();

        $user->tokens()->delete();

        $message = "Deconnexion de ".$user->nom." effectuée";
        dump($message);*/

        /*$programme = Programme::find(1);
        dispatch(new GenererPta($programme))->delay(now());

        $pta = $this->filtreByAnnee(['annee' => $this->argument('annee'), 'programmeId' => 1]);

        dump($pta);*/

        $programme = Programme::find(1);
        if (!file_exists(storage_path('app')."/pta"))
            {
                //mkdir (".".Storage::url('app')."/pta", 0777);
                File::makeDirectory(storage_path('app').'/pta',0777,true);

            }

            $file = "";
            $filename = "/pta/pta.json";
            $path = storage_path('app').$filename;
            $bytes = file_put_contents($path, $file);

            //$this->ptaFile('false');

            $projets = Projet::where('programmeId', $programme->id)
                                ->get();

            $pta = [];

            if(count($projets))
            {
                foreach($projets as $projet)
                {
                    if($projet->statut < -1) continue;

                    $debutTab = explode('-', $projet->debut);
                    $finTab = explode('-', $projet->fin);

                    if($debutTab[0] > date('Y') || $finTab[0] < date('Y'))
                    {
                        continue;
                    }

                    $composantes = $this->triPta($projet->composantes);

                    $composantestab = [];

                    foreach($composantes as $composante)
                    {

                        if($composante->statut < -1) continue;

                        $sousComposantes = $this->triPta($composante->sousComposantes);

                        if(count($sousComposantes))
                        {
                            $sctab = [];

                            foreach($sousComposantes as $key => $sousComposante)
                            {

                                if($sousComposante->statut < -1) continue;

                                $activites = $this->triPta($sousComposante->activites);
                                $activitestab = [];
                                foreach($activites as $activite)
                                {
                                    if($activite->statut < -1) continue;

                                    $controle = 1;

                                    $durees = $activite->durees;
                                    foreach($durees as $duree)
                                    {
                                        $debutTab = explode('-', $duree->debut);
                                        $finTab = explode('-', $duree->fin);

                                        if($debutTab[0] <= date('Y') && $finTab[0] >= date('Y'))
                                        {
                                            $controle = 0;
                                            break;
                                        }
                                    }

                                    if($controle)
                                    {
                                        continue;
                                    }

                                    $taches = $this->triPta($activite->taches);
                                    $tachestab = [];
                                    foreach($taches as $tache)
                                    {
                                        if($tache->statut < -1) continue;

                                        $controle = 1;

                                        $durees = $tache->durees;
                                        foreach($durees as $duree)
                                        {
                                            $debutTab = explode('-', $duree->debut);
                                            $finTab = explode('-', $duree->fin);

                                            if($debutTab[0] <= date('Y') && $finTab[0] >= date('Y'))
                                            {
                                                $controle = 0;
                                                break;
                                            }
                                        }

                                        if($controle)
                                        {
                                            continue;
                                        }

                                        array_push($tachestab, [
                                            "id" => $tache->secure_id,
                                            "nom" => $tache->nom,
                                            "code" => $tache->codePta,
                                            "poids" => $tache->poids,
                                            "poidsActuel" => optional($tache->suivis->last())->poidsActuel ?? 0,
                                            "durees" => $this->dureePta($tache->durees->where('debut', '>=', date('Y').'-01-01')->where('fin', '<=', date('Y').'-12-31')->toArray())
                                        ]);
                                    }

                                    array_push($activitestab, ["id" => $activite->secure_id,
                                                      "nom" => $activite->nom,
                                                      "code" => $activite->codePta,
                                                      "budgetNational" => $activite->budgetNational,
                                                      "pret" => $activite->pret,
                                                      "trimestre1" => $activite->planDeDecaissement(1, date('Y')),
                                                      "trimestre2" => $activite->planDeDecaissement(2, date('Y')),
                                                      "trimestre3" => $activite->planDeDecaissement(3, date('Y')),
                                                      "trimestre4" => $activite->planDeDecaissement(4, date('Y')),
                                                      "budgetise" => $activite->planDeDecaissementParAnnee(date('Y')),
                                                      "poids" => $activite->poids,
                                                      "poidsActuel" => optional($activite->suivis->last())->poidsActuel ?? 0,
                                                      "durees" => $this->dureePta($activite->durees->where('debut', '>=', date('Y').'-01-01')->where('fin', '<=', date('Y').'-12-31')->toArray()),
                                                      "structureResponsable" => $activite->structureResponsable()->nom,
                                                      "structureAssocie" => $activite->structureAssociee()->nom,
                                                      "taches" => $tachestab]);
                                }

                                array_push($sctab, ["id" => $sousComposante->secure_id,
                                                    "nom" => $sousComposante->nom,
                                                    "budgetNational" => $sousComposante->budgetNational,
                                                    "pret" => $sousComposante->pret,
                                                      "trimestre1" => $sousComposante->planDeDecaissement(1, date('Y')),
                                                      "trimestre2" => $sousComposante->planDeDecaissement(2, date('Y')),
                                                      "trimestre3" => $sousComposante->planDeDecaissement(3, date('Y')),
                                                      "trimestre4" => $sousComposante->planDeDecaissement(4, date('Y')),
                                                      "budgetise" => $sousComposante->planDeDecaissementParAnnee(date('Y')),
                                                      "poids" => $sousComposante->poids,
                                                      "poidsActuel" => optional($sousComposante->suivis->last())->poidsActuel ?? 0,
                                                  "code" => $sousComposante->codePta,
                                                "activites" => $activitestab]);
                            }

                        }

                        else
                        {
                            $activites = $this->triPta($composante->activites);
                            $sctab = [];
                            $act = [];

                            foreach($activites as $activite)
                            {
                                if($activite->statut < -1) continue;
                                $controle = 1;

                                    $durees = $activite->durees;
                                    foreach($durees as $duree)
                                    {
                                        $debutTab = explode('-', $duree->debut);
                                        $finTab = explode('-', $duree->fin);

                                        if($debutTab[0] <= date('Y') && $finTab[0] >= date('Y'))
                                        {
                                            $controle = 0;
                                            break;
                                        }
                                    }

                                    if($controle)
                                    {
                                        continue;
                                    }

                                    $taches = $this->triPta($activite->taches);
                                    $tachestab = [];
                                    foreach($taches as $tache)
                                    {
                                        if($tache->statut < -1) continue;

                                        $controle = 1;

                                        $durees = $tache->durees;
                                        foreach($durees as $duree)
                                        {
                                            $debutTab = explode('-', $duree->debut);
                                            $finTab = explode('-', $duree->fin);

                                            if($debutTab[0] <= date('Y') && $finTab[0] >= date('Y'))
                                            {
                                                $controle = 0;
                                                break;
                                            }
                                        }

                                        if($controle)
                                        {
                                            continue;
                                        }

                                        array_push($tachestab, [
                                            "id" => $tache->secure_id,
                                            "nom" => $tache->nom,
                                            "code" => $tache->codePta,
                                            "poids" => $tache->poids,
                                            "poidsActuel" => optional($tache->suivis->last())->poidsActuel ?? 0,
                                            "durees" => $this->dureePta($tache->durees->where('debut', '>=', date('Y').'-01-01')->where('fin', '<=', date('Y').'-12-31')->toArray())
                                        ]);
                                    }

                                    array_push($act, ["id" => $activite->id,
                                                  "nom" => $activite->nom,
                                                  "code" => $activite->codePta,
                                                  "budgetNational" => $activite->budgetNational,
                                                  "pret" => $activite->pret,
                                                  "trimestre1" => $activite->planDeDecaissement(1, date('Y')),
                                                  "trimestre2" => $activite->planDeDecaissement(2, date('Y')),
                                                  "trimestre3" => $activite->planDeDecaissement(3, date('Y')),
                                                      "trimestre4" => $activite->planDeDecaissement(4, date('Y')),
                                                      "budgetise" => $activite->planDeDecaissementParAnnee(date('Y')),
                                                      "poids" => $activite->poids,
                                                      "poidsActuel" => optional($activite->suivis->last())->poidsActuel ?? 0,
                                                      "structureResponsable" => $activite->structureResponsable()->nom,
                                                      "structureAssocie" => $activite->structureAssociee()->nom,
                                                      "durees" => $this->dureePta($activite->durees->where('debut', '>=', date('Y').'-01-01')->where('fin', '<=', date('Y').'-12-31')->toArray()),
                                                  "taches" => $tachestab]);
                            }

                            array_push($sctab, ["id" => 0,
                                            "nom" => 0,
                                            "code" => 0,
                                            "budgetNational" => 0,
                                            "pret" => 0,
                                            "trimestre1" => 0,
                                            "trimestre2" => 0,
                                            "trimestre3" => 0,
                                            "trimestre4" => 0,
                                            "budgetise" => 0,
                                            "poids" => 0,
                                            "poidsActuel" => 0,
                                            "activites" => $act]);
                        }

                        $activites = $composante->activites;

                        if(count($activites))
                        {
                            $activites = $this->triPta($activites);
                            $sctab = [];
                            $act = [];

                            foreach($activites as $activite)
                            {
                                if($activite->statut < -1) continue;
                                $controle = 1;

                                    $durees = $activite->durees;
                                    foreach($durees as $duree)
                                    {
                                        $debutTab = explode('-', $duree->debut);
                                        $finTab = explode('-', $duree->fin);

                                        if($debutTab[0] <= date('Y') && $finTab[0] >= date('Y'))
                                        {
                                            $controle = 0;
                                            break;
                                        }
                                    }

                                    if($controle)
                                    {
                                        continue;
                                    }

                                    $taches = $this->triPta($activite->taches);
                                    $tachestab = [];
                                    foreach($taches as $tache)
                                    {
                                        if($tache->statut < -1) continue;

                                        $controle = 1;

                                        $durees = $tache->durees;
                                        foreach($durees as $duree)
                                        {
                                            $debutTab = explode('-', $duree->debut);
                                            $finTab = explode('-', $duree->fin);

                                            if($debutTab[0] <= date('Y') && $finTab[0] >= date('Y'))
                                            {
                                                $controle = 0;
                                                break;
                                            }
                                        }

                                        if($controle)
                                        {
                                            continue;
                                        }

                                        array_push($tachestab, [
                                            "id" => $tache->secure_id,
                                            "nom" => $tache->nom,
                                            "code" => $tache->codePta,
                                            "poids" => $tache->poids,
                                            "poidsActuel" => optional($tache->suivis->last())->poidsActuel ?? 0,
                                            "durees" => $this->dureePta($tache->durees->where('debut', '>=', date('Y').'-01-01')->where('fin', '<=', date('Y').'-12-31')->toArray())
                                        ]);
                                    }

                                    array_push($act, ["id" => $activite->id,
                                                  "nom" => $activite->nom,
                                                  "code" => $activite->codePta,
                                                  "budgetNational" => $activite->budgetNational,
                                                  "pret" => $activite->pret,
                                                  "trimestre1" => $activite->planDeDecaissement(1, date('Y')),
                                                  "trimestre2" => $activite->planDeDecaissement(2, date('Y')),
                                                  "trimestre3" => $activite->planDeDecaissement(3, date('Y')),
                                                      "trimestre4" => $activite->planDeDecaissement(4, date('Y')),
                                                      "budgetise" => $activite->planDeDecaissementParAnnee(date('Y')),
                                                      "poids" => $activite->poids,
                                                      "poidsActuel" => optional($activite->suivis->last())->poidsActuel ?? 0,
                                                      "structureResponsable" => $activite->structureResponsable()->nom,
                                                      "structureAssocie" => $activite->structureAssociee()->nom,
                                                      "durees" => $this->dureePta($activite->durees->where('debut', '>=', date('Y').'-01-01')->where('fin', '<=', date('Y').'-12-31')->toArray()),
                                                  "taches" => $tachestab]);
                            }

                            array_push($sctab, ["id" => 0,
                                            "nom" => 0,
                                            "code" => 0,
                                            "budgetNational" => 0,
                                            "pret" => 0,
                                            "trimestre1" => 0,
                                            "trimestre2" => 0,
                                            "trimestre3" => 0,
                                            "trimestre4" => 0,
                                            "budgetise" => 0,
                                            "poids" => 0,
                                            "poidsActuel" => 0,
                                            "activites" => $act]);
                        }

                        array_push($composantestab, ["id" => $composante->secure_id,
                                                      "nom" => $composante->nom,
                                                      "code" => $composante->codePta,
                                                      "budgetNational" => $composante->budgetNational,
                                                      "pret" => $composante->pret,
                                                      "trimestre1" => $composante->planDeDecaissement(1, date('Y')),
                                                      "trimestre2" => $composante->planDeDecaissement(2, date('Y')),
                                                      "trimestre3" => $composante->planDeDecaissement(3, date('Y')),
                                                      "trimestre4" => $composante->planDeDecaissement(4, date('Y')),
                                                      "budgetise" => $composante->planDeDecaissementParAnnee(date('Y')),
                                                      "poids" => $composante->poids,
                                                      "poidsActuel" => optional($composante->suivis->last())->poidsActuel ?? 0,
                                                      "sousComposantes" => $sctab]);
                    }

                    array_push($pta, ["bailleur" => $projet->bailleur->sigle,
                    "projetId" => $projet->secure_id,
                    "nom" => $projet->nom,
                    "code" => $projet->codePta,
                    "budgetNational" => $projet->budgetNational,
                    "pret" => $projet->pret,
                    "composantes" => $composantestab]);
                }
            }

            $file = json_encode($pta);
            $filename = "/pta/pta.json";
            $path = storage_path('app').$filename;
            $bytes = file_put_contents($path, $file);


            //event(new PtaNotification("Pta mis à jour"));

            //$this->ptaFile('true');

    }

}
