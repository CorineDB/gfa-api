<?php

namespace App\Services;

use App\Http\Resources\ActiviteResource;
use App\Http\Resources\ComposanteResource;
use App\Http\Resources\plans\PlansDecaissementResource;
use App\Http\Resources\ProjetResource;
use App\Http\Resources\suivis\SuivisResource;
use App\Http\Resources\TacheResource;
use App\Models\Activite;
use App\Models\ArchiveActivite;
use App\Models\ArchiveComposante;
use App\Models\ArchivePlanDecaissement;
use App\Models\ArchiveProjet;
use App\Models\ArchiveSuiviFinancier;
use App\Models\ArchiveTache;
use App\Models\Composante;
use App\Models\PlanDecaissement;
use App\Models\Programme;
use App\Models\Projet;
use App\Models\PtabScope;
use App\Models\Suivi;
use App\Models\SuiviFinancier;
use App\Models\Tache;
use App\Repositories\ProgrammeRepository;
use App\Repositories\PtabScopeRepository;
use App\Traits\Eloquents\DBStatementTrait;
use App\Traits\Helpers\Pta;
use Core\Services\Contracts\BaseService;
use Core\Services\Interfaces\PtabScopeServiceInterface;
use Exception;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;

/**
* Interface PtabScopeServiceInterface
* @package Core\Services\Interfaces
*/
class PtabScopeService extends BaseService implements PtabScopeServiceInterface
{

    use Pta, DBStatementTrait;
    /**
     * @var service
     */
    protected $repository, $programmeRepository;

    public $tmp;

    /**
     * PtabScopeRepository constructor.
     *
     * @param PtabScopeRepository $ptabScopeRepository
     */
    public function __construct(PtabScopeRepository $ptabScopeRepository, ProgrammeRepository $programmeRepository)
    {
        parent::__construct($ptabScopeRepository);
        $this->repository = $ptabScopeRepository;
        $this->programmeRepository = $programmeRepository;
    }

    function array_except($array, $keys){
      foreach($keys as $key){
          unset($array[$key]);
      }
      return $array;
    }

    public function saveSuivis(Model $model, Model $resource)
    {
        $resource->suivis->each(function ($suivi) use ($model){
            $model->suivis()->create($this->formatArrayData(['poidsActuel', 'commentaire'], $suivi));
        });
    }

    public function savePlansDeDecaissement(Model $model, Model $resource)
    {
        $resource->planDeDecaissements->each(function ($planDeDecaissement) use ($model){
            $model->planDeDecaissements()->create(array_merge($this->formatArrayData(['trimestre', 'annee', 'pret', 'budgetNational'], $planDeDecaissement), ['ptabScopeId' => $model->ptabScopeId, 'activiteId' => $model->id, 'parentId' => $planDeDecaissement->id]));
        });
    }

    public function saveFichiers(Model $model, Model $resource)
    {
        $resource->fichiers->each(function ($fichier) use ($model){
            $model->fichiers()->create($this->formatArrayData(['nom', 'chemin', 'description', 'source', 'auteurId'], $fichier));
        });
    }

    public function saveCommentaires(Model $model, Model $resource)
    {
        $resource->commentaires->each(function ($commentaire) use ($model){
            $model->commentaires()->create($this->formatArrayData(['contenu', 'commentaireId'], $commentaire));
        });
    }

    public function saveStatuts(Model $model, Model $resource)
    {

        $resource->statuts->each(function ($statut) use ($model, $resource){
            $model->statuts()->create($this->formatArrayData(['etat'], $statut));
        });

    }

    public function saveDurees($model, $resource)
    {
        $resource->durees->each(function ($duree) use ($model){
            $model->durees()->create($this->formatArrayData(['debut', 'fin'], $duree));
        });/*

        $resource->durees->each(function ($duree) use ($model){
            $model->durees()->create(['debut' => $duree->debut, 'fin' => $duree->fin]);
        }); */
    }


    public function formatArrayData($attributs, $resource)
    {
        $data = [];

        $this->tmp = $data;

        collect($attributs)->each(function ($attribut) use ($resource){
            $this->tmp = array_merge($this->tmp, ["$attribut" => $resource[$attribut]]);
        });

       $data =  $this->tmp;

        return $data;
    }

    public function all(array $columns = ['*'], array $relations = []): JsonResponse
    {

        try
        {
            return response()->json(['statut' => 'success', 'message' => null, 'data' => $this->repository->all(), 'statutCode' => Response::HTTP_OK], Response::HTTP_OK);
        }
        catch (\Throwable $th)
        {
            return response()->json(['statut' => 'error', 'message' => $th->getMessage(), 'errors' => []], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    public function generer(array $attributs) : JsonResponse
    {
        try
        {

            if(!($programme = $this->programmeRepository->findById($attributs['programmeId']))) throw new Exception( "Ce programme n'existe pas", 500);

            if(!($ptabScope = PtabScope::find($attributs['ptabScopeId']))) throw new Exception( "Scope inconnu", 500);

            $projets = ArchiveProjet::where('programmeId', $programme->id)->where('ptabScopeId', $ptabScope->id)
                               ->get();

            $pta = [];

            if(count($projets))
            {
                foreach($projets as $projet)
                {
                    if($projet->statut < -1) continue;

                    $debutTab = explode('-', $projet->debut);
                    $finTab = explode('-', $projet->fin);

                    if($debutTab[0] > $attributs['annee'] || $finTab[0] < $attributs['annee'])
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

                                        if($debutTab[0] <= $attributs['annee'] && $finTab[0] >= $attributs['annee'])
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

                                            if($debutTab[0] <= $attributs['annee'] && $finTab[0] >= $attributs['annee'])
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
                                            "durees" => $this->dureePta($tache->durees->where('debut', '>=', $attributs['annee'].'-01-01')->where('fin', '<=', $attributs['annee'].'-12-31')->toArray())
                                        ]);
                                    }

                                    array_push($activitestab, ["id" => $activite->secure_id,
                                                      "nom" => $activite->nom,
                                                      "code" => $activite->codePta,
                                                      "budgetNational" => $activite->budgetNational,
                                                      "pret" => $activite->pret,
                                                      "trimestre1" => $activite->planDeDecaissement(1, $attributs['annee']),
                                                      "trimestre2" => $activite->planDeDecaissement(2, $attributs['annee']),
                                                      "trimestre3" => $activite->planDeDecaissement(3, $attributs['annee']),
                                                      "trimestre4" => $activite->planDeDecaissement(4, $attributs['annee']),
                                                      "budgetise" => $activite->planDeDecaissementParAnnee($attributs['annee']),
                                                      "poids" => $activite->poids,
                                                      "poidsActuel" => optional($activite->suivis->last())->poidsActuel ?? 0,
                                                      "durees" => $this->dureePta($activite->durees->where('debut', '>=', $attributs['annee'].'-01-01')->where('fin', '<=', $attributs['annee'].'-12-31')->toArray()),
                                                      "structureResponsable" => $activite->structureResponsable()->nom,
                                                      "structureAssocie" => $activite->structureAssociee()->nom,
                                                      "taches" => $tachestab]);
                                }

                                array_push($sctab, ["id" => $sousComposante->secure_id,
                                                        "nom" => $sousComposante->nom,
                                                        "budgetNational" => $sousComposante->budgetNational,
                                                        "pret" => $sousComposante->pret,
                                                      "trimestre1" => $sousComposante->planDeDecaissement(1, $attributs['annee']),
                                                      "trimestre2" => $sousComposante->planDeDecaissement(2, $attributs['annee']),
                                                      "trimestre3" => $sousComposante->planDeDecaissement(3, $attributs['annee']),
                                                      "trimestre4" => $sousComposante->planDeDecaissement(4, $attributs['annee']),
                                                      "budgetise" => $sousComposante->planDeDecaissementParAnnee($attributs['annee']),
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

                                        if($debutTab[0] <= $attributs['annee'] && $finTab[0] >= $attributs['annee'])
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

                                            if($debutTab[0] <= $attributs['annee'] && $finTab[0] >= $attributs['annee'])
                                            {
                                                $controle = 0;
                                                break;
                                            }
                                        }

                                        if($controle)
                                        {
                                            continue;
                                        }

                                        array_push($tachestab, $tache);
                                    }

                                    array_push($act, ["id" => $activite->id,
                                                  "nom" => $activite->nom,
                                                  "code" => $activite->codePta,
                                                  "budgetNational" => $activite->budgetNational,
                                                  "pret" => $activite->pret,
                                                  "trimestre1" => $activite->planDeDecaissement(1, $attributs['annee']),
                                                  "trimestre2" => $activite->planDeDecaissement(2, $attributs['annee']),
                                                  "trimestre3" => $activite->planDeDecaissement(3, $attributs['annee']),
                                                      "trimestre4" => $activite->planDeDecaissement(4, $attributs['annee']),
                                                      "budgetise" => $activite->planDeDecaissementParAnnee($attributs['annee']),
                                                      "poids" => $activite->poids,
                                                      "poidsActuel" => optional($activite->suivis->last())->poidsActuel ?? 0,
                                                      "structureResponsable" => $activite->structureResponsable()->nom,
                                                      "structureAssocie" => $activite->structureAssociee()->nom,
                                                      "durees" => $this->dureePta($activite->durees->where('debut', '>=', $attributs['annee'].'-01-01')->where('fin', '<=', $attributs['annee'].'-12-31')->toArray()),
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
                                                      "trimestre1" => $composante->planDeDecaissement(1, $attributs['annee']),
                                                      "trimestre2" => $composante->planDeDecaissement(2, $attributs['annee']),
                                                      "trimestre3" => $composante->planDeDecaissement(3, $attributs['annee']),
                                                      "trimestre4" => $composante->planDeDecaissement(4, $attributs['annee']),
                                                      "budgetise" => $composante->planDeDecaissementParAnnee($attributs['annee']),
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

            return response()->json(['statut' => 'success', 'message' => null, 'data' => $pta, 'statutCode' => Response::HTTP_OK], Response::HTTP_OK);
        }
        catch (\Throwable $th)
        {
            DB::rollback();
            return response()->json(['statut' => 'error', 'message' => $th->getMessage(), 'errors' => []], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    public function getOldPtaReviser(array $attributs) : JsonResponse
    {
        try
        {

            return $this->generer($attributs);
        }
        catch (\Throwable $th)
        {
            return response()->json(['statut' => 'error', 'message' => $th->getMessage(), 'errors' => []], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    public function reviserPtab(array $attributs) : JsonResponse{

        DB::beginTransaction();

        try
        {
            if(!($programme = $this->programmeRepository->findById($attributs['programmeId']))) throw new Exception( "Ce programme n'existe pas", 500);

            $version =  $this->repository->all()->where('programmeId', $attributs['programmeId'])->count();

            $version += 1;

            $ptabScope = $this->repository->create(['nom' => "Revision v{$version}", 'programmeId' => $attributs['programmeId']]);

            $projets = $programme->projets;

            $this->archiverProjet($projets, $ptabScope->id);

            DB::commit();

            return response()->json(['statut' => 'success', 'message' => "Ptab réviser", 'data' => [], 'statutCode' => Response::HTTP_OK], Response::HTTP_OK);
        }
        catch (\Throwable $th)
        {
            DB::rollBack();
            return response()->json(['statut' => 'error', 'message' => $th->getMessage(), 'errors' => []], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    public function archiverProjet($projets, int $ptabScope){

        $projets->map(function($projet) use ($ptabScope){
            $archive_projet = ArchiveProjet::create(array_merge($projet->toArray(), ['ptabScopeId' => $ptabScope, 'parentId' => $projet->id]));

            $projet->decaissements->each(function ($decaissement) use ($archive_projet){
                $archive_projet->decaissements()->create(array_merge($decaissement->toArray(),["morphable_id" => $decaissement->decaissementable_id, "morphable_type" => $decaissement->decaissementable_type]));
            });

            $this->saveStatuts($archive_projet, $projet);

            $this->saveDurees($archive_projet, $projet);

            $this->saveFichiers($archive_projet, $projet);

            $projet->indicateurs->each(function ($indicateur) use ($archive_projet){
                $archive_projet->indicateurs()->create($this->formatArrayData(['nom', 'sourceDeVerification', 'hypothese'], $indicateur));
            });

            $projet->objectifSpecifiques->each(function ($objectifSpecifique) use ($archive_projet){
                $archive_projet->objectifSpecifiques()->create($this->formatArrayData(['nom', 'description', 'indicateurId'], $objectifSpecifique));
            });

            $projet->resultats->each(function ($resultat) use ($archive_projet){
                $archive_projet->resultats()->create($this->formatArrayData(['nom', 'description', 'indicateurId'], $resultat));
            });

            $this->archiver_composante($projet->composantes, $archive_projet, $ptabScope);
        });
    }

    public function archiver_composante($composantes, $archive_projet, $ptabScope){

        $composantes->map(function($composante) use ($ptabScope, $archive_projet){

            if($composante->composanteId === 0){
                $this->changeState(0);
                $archive_composante = ArchiveComposante::create(array_merge($composante->toArray(), ['ptabScopeId' => $ptabScope, 'composanteId' => 0, 'projetId' => $archive_projet->id, 'parentId' => $composante->id]));
                $this->changeState(1);
            }

            //$this->saveStatuts($archive_composante, $composante);

            $this->saveCommentaires($archive_composante, $composante);

            $this->saveFichiers($archive_composante, $composante);

            $this->saveSuivis($archive_composante, $composante);

            /*
                $archive_composante->statuts()->saveMany($composante->statuts);
                $archive_composante->suivis()->saveMany($composante->suivis);
                $archive_composante->fichiers()->saveMany($composante->fichiers);
                $archive_composante->commentaires()->saveMany($composante->commentaires);
            */
            $this->archiver_sous_composante($composante->sousComposantes, $archive_composante, $ptabScope);

            $this->archiver_activite($composante->activites, $archive_composante, $ptabScope);
        });
    }

    public function archiver_sous_composante($sous_composantes, $archive_composante, $ptabScope){

        $sous_composantes->map(function($sous_composante) use ($ptabScope, $archive_composante){

            if($sous_composante->composanteId != 0) {
                $archive_sous_composante = ArchiveComposante::create(array_merge($sous_composante->toArray(), ['ptabScopeId' => $ptabScope, 'composanteId' => $archive_composante->id, 'projetId' => $archive_composante->projet->id, 'parentId' => $sous_composante->id]));
            }

            //$this->saveStatuts($archive_sous_composante, $sous_composante);

            $this->saveCommentaires($archive_sous_composante, $sous_composante);

            $this->saveFichiers($archive_sous_composante, $sous_composante);

            $this->saveSuivis($archive_sous_composante, $sous_composante);

            $this->archiver_activite($sous_composante->activites, $archive_sous_composante, $ptabScope);

        });
    }

    public function archiver_activite($activites, $archive_composante, int $ptabScope){

        $activites->map(function($activite) use ($ptabScope, $archive_composante){

            $archive_activite = ArchiveActivite::create(array_merge($activite->toArray(), ['ptabScopeId' => $ptabScope, 'composanteId' => $archive_composante->id, 'parentId' => $activite->id]));

            //$this->saveStatuts($archive_activite, $activite);

            $this->saveDurees($archive_activite, $activite);

            $this->saveCommentaires($archive_activite, $activite);

            $this->saveFichiers($archive_activite, $activite);

            $this->saveSuivis($archive_activite, $activite);

            $this->savePlansDeDecaissement($archive_activite, $activite);

            if($activite->structureResponsable() !== null ){
                $archive_activite->structures()->attach($activite->structureResponsable()->pivot->userId, ['type' => 'Responsable']);
            }

            if($activite->structureAssociee() !== null )
                $archive_activite->structures()->attach($activite->structureAssociee()->pivot->userId, ['type' => 'Associée']);

            $this->archiver_tache($activite->taches, $archive_activite, $ptabScope);

        });
    }

    public function archiver_tache($taches, $archive_activite, int $ptabScope){
        $taches->map(function($tache) use ($ptabScope, $archive_activite){
            $archive_tache = ArchiveTache::create(array_merge($tache->toArray(), ['ptabScopeId' => $ptabScope, 'activiteId' => $archive_activite->id, 'parentId' => $tache->id]));

            //$this->saveStatuts($archive_tache, $tache);

            $this->saveDurees($archive_tache, $tache);

            $this->saveCommentaires($archive_tache, $tache);

            $this->saveFichiers($archive_tache, $tache);

            $this->saveSuivis($archive_tache, $tache);

        });
    }

    public function getPtabReviser($attributs) : JsonResponse
    {

        try
        {
            $projets = ArchiveProjet::where('ptabScopeId', $attributs['version'])->get();

            $ptab["projets"] = ProjetResource::collection($projets);

            if(array_key_exists('composantes', $attributs)){
                $composantes = ArchiveComposante::where('ptabScopeId', $attributs['version'])->where('composanteId', 0)->get();

                $suivis =  Suivi::whereHasMorph('suivitable', [ArchiveComposante::class], function ($query) use ($composantes){
                    $query->whereIn('id', $composantes->pluck('id'));
                })->get();

                $ptab["composantes"] = ComposanteResource::collection($this->triPta($composantes));

                $ptab["suivis"] = SuivisResource::collection($suivis);
            }

            if(array_key_exists('sous-composantes', $attributs)){
                $sousComposantes = ArchiveComposante::where('ptabScopeId', $attributs['version'])->where('composanteId', '!=', 0)->get();

                $suivis =  Suivi::whereHasMorph('suivitable', [ArchiveComposante::class], function ($query) use ($sousComposantes){
                    $query->whereIn('id', $sousComposantes->pluck('id'));
                })->get();

                $ptab["sous-composantes"] = ComposanteResource::collection($this->triPta($sousComposantes));

                $ptab["suivis"] = SuivisResource::collection($suivis);
            }

            if(array_key_exists('activites', $attributs)){
                $activites = ArchiveActivite::where('ptabScopeId', $attributs['version'])->get();

                $planDeDecaissements = ArchivePlanDecaissement::where('ptabScopeId', $attributs['version'])->get();

                $suivis =  Suivi::whereHasMorph('suivitable', [ArchiveActivite::class], function ($query) use ($activites){
                    $query->whereIn('id', $activites->pluck('id'));
                })->get();

                $ptab["activites"] = ActiviteResource::collection($this->triPta($activites));

                $ptab["planDeDecaissements"] = PlansDecaissementResource::collection($planDeDecaissements);

                $ptab["suivis"] = SuivisResource::collection($suivis);

            }

            if(array_key_exists('taches', $attributs)){
                $taches = ArchiveTache::where('ptabScopeId', $attributs['version'])->get();
                $suivis =  Suivi::whereHasMorph('suivitable', [ArchiveTache::class], function ($query) use ($taches){
                    $query->whereIn('id', $taches->pluck('id'));
                })->get();
                $ptab["taches"] = TacheResource::collection($this->triPta($taches));
                $ptab["suivis"] = SuivisResource::collection($suivis);
            }

            return response()->json(['statut' => 'success', 'message' => null, 'data' => $ptab, 'statutCode' => Response::HTTP_OK], Response::HTTP_OK);
        }
        catch (\Throwable $th)
        {
            return response()->json(['statut' => 'error', 'message' => $th->getMessage(), 'errors' => []], Response::HTTP_INTERNAL_SERVER_ERROR);
        }

    }

}
