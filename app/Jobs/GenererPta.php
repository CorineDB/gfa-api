<?php

namespace App\Jobs;

use App\Events\PtaNotification;
use App\Models\Programme;
use App\Traits\Helpers\Pta;
use App\Models\Projet;
use App\Services\PtaService;
use App\Traits\Helpers\HelperTrait;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;

class GenererPta implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels, Pta, HelperTrait;

    private $programme;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct(Programme $programme = null)
    {
        $this->programme = $programme;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {

        $pta_path="pta/pta{$this->programme->secure_id}".date('Y').".json";

        if (!file_exists(storage_path('app') . "/pta")) {
            //mkdir (".".Storage::url('app')."/pta", 0777);
            File::makeDirectory(storage_path('app') . '/pta', 0777, true);
        }

        $file = "";
        $filename = "/".$pta_path;
        $path = storage_path('app') . $filename;
        $bytes = file_put_contents($path, $file);

        $projets = Projet::where('programmeId', $this->programme->id)
            ->get();

        $pta = [];

        if (count($projets)) {
            foreach ($projets as $projet) {
                if ($projet->statut < -1) continue;

                $debutTab = explode('-', $projet->debut);
                $finTab = explode('-', $projet->fin);

                if ($debutTab[0] > date('Y') || $finTab[0] < date('Y')) {
                    continue;
                }

                $composantes = $this->triPta($projet->composantes);

                $composantestab = [];

                foreach ($composantes as $composante) {

                    if ($composante->statut < -1) continue;

                    $sousComposantes = $this->triPta($composante->sousComposantes);

                    if (count($sousComposantes)) {
                        $sctab = [];

                        foreach ($sousComposantes as $key => $sousComposante) {

                            if ($sousComposante->statut < -1) continue;

                            $activites = $this->triPta($sousComposante->activites);
                            $activitestab = [];
                            foreach ($activites as $activite) {
                                if ($activite->statut < -1) continue;

                                $controle = 1;

                                $durees = $activite->durees;
                                foreach ($durees as $duree) {
                                    $debutTab = explode('-', $duree->debut);
                                    $finTab = explode('-', $duree->fin);

                                    if ($debutTab[0] <= date('Y') && $finTab[0] >= date('Y')) {
                                        $controle = 0;
                                        break;
                                    }
                                }

                                if ($controle) {
                                    continue;
                                }

                                $taches = $this->triPta($activite->taches);
                                $tachestab = [];
                                foreach ($taches as $tache) {
                                    if ($tache->statut < -1) continue;

                                    $controle = 1;

                                    $durees = $tache->durees;
                                    foreach ($durees as $duree) {
                                        $debutTab = explode('-', $duree->debut);
                                        $finTab = explode('-', $duree->fin);

                                        if ($debutTab[0] <= date('Y') && $finTab[0] >= date('Y')) {
                                            $controle = 0;
                                            break;
                                        }
                                    }

                                    if ($controle) {
                                        continue;
                                    }

                                    array_push($tachestab, [
                                        "id" => $tache->secure_id,
                                        "nom" => $tache->nom,
                                        "code" => $tache->codePta,
                                        "poids" => $tache->poids,
                                        "tep" => $tache->tep,
                                        "poidsActuel" => optional($tache->suivis->last())->poidsActuel ?? 0,
                                        "durees" => $this->dureePta($tache->durees->where('debut', '>=', date('Y') . '-01-01')->where('fin', '<=', date('Y') . '-12-31')->toArray()),
                                        "suivis" => $tache->suivis,
                                    ]);
                                }

                                array_push($activitestab, [
                                    "id" => $activite->secure_id,
                                    "nom" => $activite->nom,
                                    "code" => $activite->codePta,
                                    "budgetNational" => $activite->budgetNational,
                                    "pret" => $activite->pret,
                                    "depenses" => round($activite->consommer,2),
                                    "tep" => round($activite->tep,2),
                                    "tef" => round($activite->tef,2),
                                    "trimestre1" => $activite->planDeDecaissement(1, date('Y')),
                                    "trimestre2" => $activite->planDeDecaissement(2, date('Y')),
                                    "trimestre3" => $activite->planDeDecaissement(3, date('Y')),
                                    "trimestre4" => $activite->planDeDecaissement(4, date('Y')),
                                    "budgetise" => $activite->planDeDecaissementParAnnee(date('Y')),
                                    "poids" => $activite->poids,
                                    "poidsActuel" => optional($activite->suivis->last())->poidsActuel ?? 0,
                                    "durees" => $this->dureePta($activite->durees->where('debut', '>=', date('Y') . '-01-01')->where('fin', '<=', date('Y') . '-12-31')->toArray()),/*
                                    "structureResponsable" => $activite->structureResponsable()->nom,
                                    "structureAssocie" => $activite->structureAssociee()->nom,*/
                                    "taches" => $tachestab
                                ]);
                            }

                            array_push($sctab, [
                                "id" => $sousComposante->secure_id,
                                "nom" => $sousComposante->nom,
                                "budgetNational" => $sousComposante->budgetNational,
                                "pret" => $sousComposante->pret,
                                "depenses" => round($sousComposante->consommer,2),
                                "tep" => round($sousComposante->tep,2),
                                "tef" => round($sousComposante->tef,2),
                                "trimestre1" => $sousComposante->planDeDecaissement(1, date('Y')),
                                "trimestre2" => $sousComposante->planDeDecaissement(2, date('Y')),
                                "trimestre3" => $sousComposante->planDeDecaissement(3, date('Y')),
                                "trimestre4" => $sousComposante->planDeDecaissement(4, date('Y')),
                                "budgetise" => $sousComposante->planDeDecaissementParAnnee(date('Y')),
                                "poids" => $sousComposante->poids,
                                "poidsActuel" => optional($sousComposante->suivis->last())->poidsActuel ?? 0,
                                "code" => $sousComposante->codePta,
                                "activites" => $activitestab
                            ]);
                        }
                    } else {
                        $activites = $this->triPta($composante->activites);
                        $sctab = [];
                        $act = [];

                        foreach ($activites as $activite) {
                            if ($activite->statut < -1) continue;
                            $controle = 1;

                            $durees = $activite->durees;
                            foreach ($durees as $duree) {
                                $debutTab = explode('-', $duree->debut);
                                $finTab = explode('-', $duree->fin);

                                if ($debutTab[0] <= date('Y') && $finTab[0] >= date('Y')) {
                                    $controle = 0;
                                    break;
                                }
                            }

                            if ($controle) {
                                continue;
                            }

                            $taches = $this->triPta($activite->taches);
                            $tachestab = [];
                            foreach ($taches as $tache) {
                                if ($tache->statut < -1) continue;

                                $controle = 1;

                                $durees = $tache->durees;
                                foreach ($durees as $duree) {
                                    $debutTab = explode('-', $duree->debut);
                                    $finTab = explode('-', $duree->fin);

                                    if ($debutTab[0] <= date('Y') && $finTab[0] >= date('Y')) {
                                        $controle = 0;
                                        break;
                                    }
                                }

                                if ($controle) {
                                    continue;
                                }

                                array_push($tachestab, [
                                    "id" => $tache->secure_id,
                                    "nom" => $tache->nom,
                                    "code" => $tache->codePta,
                                    "poids" => $tache->poids,
                                    "poidsActuel" => optional($tache->suivis->last())->poidsActuel ?? 0,
                                    "tep" => $tache->tep,
                                    "durees" => $this->dureePta($tache->durees->where('debut', '>=', date('Y') . '-01-01')->where('fin', '<=', date('Y') . '-12-31')->toArray()),
                                    "suivis" => $tache->suivis,
                                ]);
                            }

                            array_push($act, [
                                "id" => $activite->id,
                                "nom" => $activite->nom,
                                "code" => $activite->codePta,
                                "budgetNational" => $activite->budgetNational,
                                "pret" => $activite->pret,
                                "depenses" => round($activite->consommer,2),
                                "tep" => round($activite->tep,2),
                                "tef" => round($activite->tef,2),
                                "trimestre1" => $activite->planDeDecaissement(1, date('Y')),
                                "trimestre2" => $activite->planDeDecaissement(2, date('Y')),
                                "trimestre3" => $activite->planDeDecaissement(3, date('Y')),
                                "trimestre4" => $activite->planDeDecaissement(4, date('Y')),
                                "budgetise" => $activite->planDeDecaissementParAnnee(date('Y')),
                                "poids" => $activite->poids,
                                "poidsActuel" => optional($activite->suivis->last())->poidsActuel ?? 0,/*
                                "structureResponsable" => $activite->structureResponsable()->nom,
                                "structureAssocie" => $activite->structureAssociee()->nom,*/
                                "durees" => $this->dureePta($activite->durees->where('debut', '>=', date('Y') . '-01-01')->where('fin', '<=', date('Y') . '-12-31')->toArray()),
                                "taches" => $tachestab
                            ]);
                        }

                        array_push($sctab, [
                            "id" => 0,
                            "nom" => 0,
                            "code" => 0,
                            "budgetNational" => 0,
                            "pret" => 0,
                            "depenses" => 0,
                            "tep" => 0,
                            "tef" => 0,
                            "trimestre1" => 0,
                            "trimestre2" => 0,
                            "trimestre3" => 0,
                            "trimestre4" => 0,
                            "budgetise" => 0,
                            "poids" => 0,
                            "poidsActuel" => 0,
                            "activites" => $act
                        ]);
                    }

                    array_push($composantestab, [
                        "id" => $composante->secure_id,
                        "nom" => $composante->nom,
                        "code" => $composante->codePta,
                        "budgetNational" => $composante->budgetNational,
                        "pret" => $composante->pret,
                        "depenses" => round($composante->consommer,2),
                        "tep" => round($composante->tep,2),
                        "tef" => round($composante->tef,2),
                        "trimestre1" => $composante->planDeDecaissement(1, date('Y')),
                        "trimestre2" => $composante->planDeDecaissement(2, date('Y')),
                        "trimestre3" => $composante->planDeDecaissement(3, date('Y')),
                        "trimestre4" => $composante->planDeDecaissement(4, date('Y')),
                        "budgetise" => $composante->planDeDecaissementParAnnee(date('Y')),
                        "poids" => $composante->poids,
                        "poidsActuel" => optional($composante->suivis->last())->poidsActuel ?? 0,
                        "sousComposantes" => $sctab
                    ]);
                }

                array_push($pta, [//"bailleur" => $projet->bailleur->sigle,
                    "owner_id" => $projet->projetable->secure_id,
                    "owner_nom" => $projet->projetable->user->nom,
                    "projetId" => $projet->secure_id,
                    "nom" => $projet->nom,
                    "code" => $projet->codePta,
                    "budgetNational" => $projet->budgetNational,
                    "pret" => $projet->pret,
                    "depenses" => round($projet->consommer,2),
                    "tep" => round($projet->tep, 2),
                    "tef" => round($projet->tef,2),
                    "composantes" => $composantestab]);
            }
        }

        $file = json_encode($pta);
        $filename = "/".$pta_path;
        //$filename = "/pta/pta.json";
        $path = storage_path('app') . $filename;
        $bytes = file_put_contents($path, $file);
    }
}
