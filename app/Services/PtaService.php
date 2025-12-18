<?php

namespace App\Services;

use App\Jobs\GenererPta;
use App\Models\Organisation;
use App\Repositories\PtaRepository;
use App\Repositories\ProgrammeRepository;
use App\Models\Projet;
use Core\Services\Contracts\BaseService;
use Core\Services\Interfaces\PtaServiceInterface;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;
use App\Traits\Helpers\Pta;
use Exception;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;

/**
* Interface PtaServiceInterface
* @package Core\Services\Interfaces
*/
class PtaService extends BaseService implements PtaServiceInterface
{

    use Pta;

    /**
     * @var service
     */
    protected $repository, $programmeRepository;

    /**
     * PtaService constructor.
     *
     * @param PtaRepository $ptaRepository
     */
    public function __construct(ProgrammeRepository $programmeRepository)
    {
        parent::__construct($programmeRepository);
        $this->programmeRepository = $programmeRepository;
    }

    public function generer(array $attributs) : JsonResponse
    {
        try
        {

            if(isset($attributs['programmeId'])){
                if(!($programme = $this->programmeRepository->findById($attributs['programmeId']))) throw new Exception( "Ce programme n'existe pas", 500);
            }
            else{
                $programme = Auth::user()->programme;
            }

            /* if (file_exists(storage_path('app')."/pta/pta.json"))
            {
                $file = Storage::disk('local')->get('pta/pta.json');

                if(strlen($file))
                {

                    $pta = json_decode($file);

                    return response()->json(['statut' => 'success', 'message' => null, 'data' => $pta, 'statutCode' => Response::HTTP_OK], Response::HTTP_OK);
                }
            } */


            $pta_path="pta/pta{$programme->secure_id}".date('Y').".json";

            if (file_exists(storage_path('app')."/".$pta_path))
            {
                $file = Storage::disk('local')->get($pta_path);

                if(strlen($file))
                {

                    $pta = json_decode($file);

                    return response()->json(['statut' => 'success', 'message' => null, 'data' => $pta, 'statutCode' => Response::HTTP_OK], Response::HTTP_OK);
                }
            }

            if(Auth::user()->hasRole('organisation') || ( get_class(auth()->user()->profilable) == Organisation::class))
            {
                $projets = Projet::where('programmeId', $programme->id)
                                 ->where('projetable_id', Auth::user()->profilable->id)
                                 ->get();
            }
            else
            {
                $projets = Projet::where('programmeId', $programme->id)->where('statut', '>=' , -1)
                                 ->get();
            }

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
                                            "durees" => $this->dureePta($tache->durees->where('debut', '>=', date('Y').'-01-01')->where('fin', '<=', date('Y').'-12-31')->toArray()),
                                            "tep" => $tache->tep,
                                            "suivis" => $tache->suivis,
                                        ]);
                                    }

                                    array_push($activitestab, ["id" => $activite->secure_id,
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
                                                      "durees" => $this->dureePta($activite->durees->where('debut', '>=', date('Y').'-01-01')->where('fin', '<=', date('Y').'-12-31')->toArray()),
                                                      /*"structureResponsable" => $activite->structureResponsable()->nom,
                                                      "structureAssocie" => $activite->structureAssociee()->nom,*/
                                                      "taches" => $tachestab]);
                                }

                                array_push($sctab, ["id" => $sousComposante->secure_id,
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
                                            "durees" => $this->dureePta($tache->durees->where('debut', '>=', date('Y').'-01-01')->where('fin', '<=', date('Y').'-12-31')->toArray()),
                                            "tep" => $tache->tep,
                                            "suivis" => $tache->suivis,
                                        ]);
                                    }

                                    array_push($act, ["id" => $activite->id,
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
                                                      "durees" => $this->dureePta($activite->durees->where('debut', '>=', date('Y').'-01-01')->where('fin', '<=', date('Y').'-12-31')->toArray()),
                                                  "taches" => $tachestab]);
                            }

                            array_push($sctab, ["id" => 0,
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
                                            "activites" => $act]);
                        }

                        array_push($composantestab, ["id" => $composante->secure_id,
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
                                                      "sousComposantes" => $sctab]);
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
                    "tep" => round($projet->tep,2),
                    "tef" => round($projet->tef,2),
                    "composantes" => $composantestab]);
                }
            }

            if (!file_exists(storage_path('app')."/pta"))
            {
                //mkdir (".".Storage::url('app')."/pta", 0777);
                File::makeDirectory(storage_path('app').'/pta',0777,true);
            }

            $file = json_encode($pta);
            $filename = "/".$pta_path;
            $path = storage_path('app').$filename;
            $bytes = file_put_contents($path, $file); 

            /* if (!file_exists(storage_path('app')."/pta"))
            {
                //mkdir (".".Storage::url('app')."/pta", 0777);
                File::makeDirectory(storage_path('app').'/pta',0777,true);
            }

            $file = json_encode($pta);
            $filename = "/pta/pta.json";
            $path = storage_path('app').$filename;
            $bytes = file_put_contents($path, $file); */

            return response()->json(['statut' => 'success', 'message' => null, 'data' => $pta, 'statutCode' => Response::HTTP_OK], Response::HTTP_OK);
        }
        catch (\Throwable $th)
        {
            DB::rollback();
            return response()->json(['statut' => 'error', 'message' => $th->getMessage(), 'errors' => []], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    public function oldGenerer(array $attributs) : JsonResponse
    {
        try
        {
            if (file_exists(storage_path('app')."/pta/pta.json"))
            {
                $file = Storage::disk('local')->get('pta/pta.json');

                if(strlen($file))
                {

                    $pta = json_decode($file);

                    return response()->json(['statut' => 'success', 'message' => null, 'data' => $pta, 'statutCode' => Response::HTTP_OK], Response::HTTP_OK);
                }
            }

            if(!($programme = $this->programmeRepository->findById($attributs['programmeId']))) throw new Exception( "Ce programme n'existe pas", 500);

            if(Auth::user()->hasRole('bailleur'))
            {
                $projets = Projet::where('programmeId', $programme->id)
                                 ->where('bailleurId', Auth::user()->profilable->id)
                                 ->get();
            }

            else
            {
                $projets = Projet::where('programmeId', $programme->id)
                                 ->get();
            }

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

            if (!file_exists(storage_path('app')."/pta"))
            {
                //mkdir (".".Storage::url('app')."/pta", 0777);
                File::makeDirectory(storage_path('app').'/pta',0777,true);

            }

            $file = json_encode($pta);
            $filename = "/pta/pta.json";
            $path = storage_path('app').$filename;
            $bytes = file_put_contents($path, $file);

            return response()->json(['statut' => 'success', 'message' => null, 'data' => $pta, 'statutCode' => Response::HTTP_OK], Response::HTTP_OK);
        }
        catch (\Throwable $th)
        {
            DB::rollback();
            return response()->json(['statut' => 'error', 'message' => $th->getMessage(), 'errors' => []], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    public function filtre(array $attributs) : JsonResponse
    {
        try
        {
            if( !(auth()->user()->hasRole("organisation", "unitee-de-gestion")) ){
                throw new Exception("Vous n'avez pas les permissions pour effectuer cette action", 1);
            }

            $programme = Auth::user()->programme;
            $attributs = array_merge($attributs, ["programmeId" => $programme->id]);
            if(!($programme = $this->programmeRepository->findById($attributs['programmeId']))) throw new Exception( "Ce programme n'existe pas", 500);

            $pta = [];

            if(array_key_exists('ppm', $attributs))
            {
                if(!(array_key_exists('annee', $attributs))) throw new Exception( "L'année est obligatoire", 500);

                $pta = $this->filtreByPpm($attributs);
            }

            else if(array_key_exists('annee', $attributs) && !(array_key_exists('mois', $attributs)))
            {
               $pta = $this->filtreByAnnee($attributs);
            }

            else
            {
               $pta = $this->filtreByAnnee(array_merge($attributs, ["annee" => date('Y')]));
            }

            /*else if(array_key_exists('mois', $attributs))
            {
               if(!(array_key_exists('annee', $attributs))) throw new Exception( "L'année est obligatoire", 500);

               $pta = $this->filtreByMois($attributs);
            }

            else if(array_key_exists('debut', $attributs) && array_key_exists('fin', $attributs))
            {
               $pta = $this->filtreByDate($attributs);
            }

            else
            {
                $pta = $this->filtreAll($attributs);
            }*/

            return response()->json(['statut' => 'success', 'message' => null, 'data' => $pta, 'statutCode' => Response::HTTP_OK], Response::HTTP_OK);
        }

        catch (\Throwable $th)
        {
            DB::rollback();
            return response()->json(['statut' => 'error', 'message' => $th->getMessage(), 'errors' => []], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    public function oldFiltre(array $attributs) : JsonResponse
    {
        try
        {
            if(!($programme = $this->programmeRepository->findById($attributs['programmeId']))) throw new Exception( "Ce programme n'existe pas", 500);
            $attributs = array_merge($attributs, ["programmeId" => $programme->id]);

            $pta = [];

            if(array_key_exists('ppm', $attributs))
            {
                if(!(array_key_exists('annee', $attributs))) throw new Exception( "L'année est obligatoire", 500);

                $pta = $this->filtreByPpm($attributs);
            }

            else if(array_key_exists('annee', $attributs) && !(array_key_exists('mois', $attributs)))
            {
               $pta = $this->filtreByAnnee($attributs);
            }

            /*else if(array_key_exists('mois', $attributs))
            {
               if(!(array_key_exists('annee', $attributs))) throw new Exception( "L'année est obligatoire", 500);

               $pta = $this->filtreByMois($attributs);
            }

            else if(array_key_exists('debut', $attributs) && array_key_exists('fin', $attributs))
            {
               $pta = $this->filtreByDate($attributs);
            }

            else
            {
                $pta = $this->filtreAll($attributs);
            }*/

            return response()->json(['statut' => 'success', 'message' => null, 'data' => $pta, 'statutCode' => Response::HTTP_OK], Response::HTTP_OK);
        }

        catch (\Throwable $th)
        {
            DB::rollback();
            return response()->json(['statut' => 'error', 'message' => $th->getMessage(), 'errors' => []], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
}
