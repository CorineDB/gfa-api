<?php

namespace App\Services;

use App\Http\Resources\gouvernance\FichesDeSyntheseResource;
use App\Http\Resources\gouvernance\RecommandationsResource;
use App\Http\Resources\gouvernance\SoumissionsResource;
use App\Repositories\EvaluationDeGouvernanceRepository;
use App\Repositories\FormulaireDeGouvernanceRepository;
use App\Repositories\OptionDeReponseRepository;
use App\Repositories\OrganisationRepository;
use App\Repositories\ProgrammeRepository;
use App\Repositories\QuestionDeGouvernanceRepository;
use App\Repositories\SoumissionRepository;
use Core\Services\Contracts\BaseService;
use Core\Services\Interfaces\SoumissionServiceInterface;
use App\Traits\Helpers\HelperTrait;
use Exception;
use App\Traits\Helpers\LogActivity;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Response;

/**
* Interface SoumissionServiceInterface
* @package Core\Services\Interfaces
*/
class SoumissionService extends BaseService implements SoumissionServiceInterface
{
    use HelperTrait;

    /**
     * @var service
     */
    protected $repository;

    /**
     * SoumissionRepository constructor.
     *
     * @param SoumissionRepository $soumissionRepository
     */
    public function __construct(SoumissionRepository $soumissionRepository)
    {
        parent::__construct($soumissionRepository);
    }

    public function all(array $columns = ['*'], array $relations = []): JsonResponse
    {
        try
        {
            if(Auth::user()->hasRole('administrateur')){
                $soumissions = $this->repository->all();
            }
            else{
                //$projets = $this->repository->allFiltredBy([['attribut' => 'programmeId', 'operateur' => '=', 'valeur' => auth()->user()->programme->id]]);
                $soumissions = Auth::user()->programme->soumissions;
            }

            return response()->json(['statut' => 'success', 'message' => null, 'data' => SoumissionsResource::collection($soumissions), 'statutCode' => Response::HTTP_OK], Response::HTTP_OK);
        }

        catch (\Throwable $th)
        {
            return response()->json(['statut' => 'error', 'message' => $th->getMessage(), 'errors' => []], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    public function findById($soumissions, array $columns = ['*'], array $relations = [], array $appends = []): JsonResponse
    {
        try
        {
            if(!is_object($soumissions) && !($soumissions = $this->repository->findById($soumissions))) throw new Exception("Evaluation de gouvernance inconnue.", 500);

            return response()->json(['statut' => 'success', 'message' => null, 'data' => new SoumissionsResource($soumissions), 'statutCode' => Response::HTTP_OK], Response::HTTP_OK);
        }

        catch (\Throwable $th)
        {
            return response()->json(['statut' => 'error', 'message' => $th->getMessage(), 'errors' => []], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    public function create(array $attributs) : JsonResponse
    {
        DB::beginTransaction();

        try {

            if(isset($attributs['programmeId']) && empty($attributs['programmeId'])){
                $programme = $evaluationDeGouvernance = app(ProgrammeRepository::class)->findById($attributs['programmeId']);
            }
            else{
                $programme = Auth::user()->programme;
            }

            $attributs = array_merge($attributs, ['programmeId' => $programme->id]);

            if(isset($attributs['evaluationId'])){
                if(!(($evaluationDeGouvernance = app(EvaluationDeGouvernanceRepository::class)->findById($attributs['evaluationId'])) && $evaluationDeGouvernance->programmeId == $programme->id))
                {
                    throw new Exception( "Evaluation de gouvernance est introuvable dans le programme.", Response::HTTP_NOT_FOUND);
                }
            
                $attributs = array_merge($attributs, ['evaluationId' => $evaluationDeGouvernance->id]);
            }

            if(isset($attributs['formulaireDeGouvernanceId'])){
                if(!(($formulaireDeGouvernance = app(FormulaireDeGouvernanceRepository::class)->findById($attributs['formulaireDeGouvernanceId'])) && $formulaireDeGouvernance->programmeId == $programme->id))
                {
                    throw new Exception( "Formulaire de gouvernance est introuvable dans le programme.", Response::HTTP_NOT_FOUND);
                }
            
                $attributs = array_merge($attributs, ['formulaireDeGouvernanceId' => $formulaireDeGouvernance->id]);
            }
            
            if(isset($attributs['organisationId'])){

                if(!(($organisation = app(OrganisationRepository::class)->findById($attributs['organisationId'])) && $organisation->user->programmeId == $programme->id))
                {
                    throw new Exception( "Organisation introuvable dans le programme.", Response::HTTP_NOT_FOUND);
                }
            }
            else if(Auth::user()->hasRole('organisation')){
                $organisation = Auth::user()->profilable;
            }

            $attributs = array_merge($attributs, ['organisationId' => $organisation->id]);

            /*dd(Soumission::where("evaluationId", $evaluationDeGouvernance->id)->where("organisationId", $organisation->id)->where("formulaireDeGouvernanceId", $formulaireDeGouvernance->id)->get());

            dd($attributs);*/

            if(($soumission = $this->repository->getInstance()->where("evaluationId", $evaluationDeGouvernance->id)->where("organisationId", $organisation->id)->where("formulaireDeGouvernanceId", $formulaireDeGouvernance->id)->first()) == null)
            {
                $soumission = $this->repository->create($attributs);
            }
            else{
                $soumission->fill($attributs);
                $soumission->save();
                if($soumission->statut){
                    return response()->json(['statut' => 'success', 'message' => "La soumission a déjà été validée.", 'data' => null, 'statutCode' => Response::HTTP_OK], Response::HTTP_OK);
                }
            }

            $soumission->refresh();

            $soumission->type = $soumission->formulaireDeGouvernance->type;

            $soumission->save();

            if(isset($attributs['factuel']) && !empty($attributs['factuel'])){
                $soumission->fill($attributs['factuel']);
                $soumission->save();

                foreach ($attributs['factuel']['response_data'] as $key => $item) {

                    if(!(($questionDeGouvernance = app(QuestionDeGouvernanceRepository::class)->findById($item['questionId'])) && $questionDeGouvernance->programmeId == $programme->id))
                    {
                        throw new Exception( "Question de gouvernance introuvable dans le programme.", Response::HTTP_NOT_FOUND);
                    }

                    //$option = app(OptionDeReponseRepository::class)->findById($item['optionDeReponseId'])->where("programmeId", $programme->id)->first();
                    $option = app(OptionDeReponseRepository::class)->findById($item['optionDeReponseId']);
                    
                    if(!$option && $option->programmeId == $programme->id) throw new Exception( "Cette option n'est pas dans le programme", Response::HTTP_NOT_FOUND);

                    if(!($reponseDeLaCollecte = $soumission->reponses_de_la_collecte()->where(['programmeId' => $programme->id, 'questionId' => $questionDeGouvernance->id])->first())){
                        $reponseDeLaCollecte = $soumission->reponses_de_la_collecte()->create(array_merge($item, ['formulaireDeGouvernanceId' => $soumission->formulaireDeGouvernance->id, 'optionDeReponseId' => $option->id, 'questionId' => $questionDeGouvernance->id, 'type' => 'indicateur', 'programmeId' => $programme->id, 'point' => $option->formulaires_de_gouvernance()->wherePivot("formulaireDeGouvernanceId", $soumission->formulaireDeGouvernance->id)->first()->pivot->point]));
                    }
                    else{
                        unset($item['questionId']);
                        $reponseDeLaCollecte->fill(array_merge($item, ['formulaireDeGouvernanceId' => $soumission->formulaireDeGouvernance->id, 'optionDeReponseId' => $option->id, 'type' => 'indicateur', 'programmeId' => $programme->id, 'point' => $option->formulaires_de_gouvernance()->wherePivot("formulaireDeGouvernanceId", $soumission->formulaireDeGouvernance->id)->first()->pivot->point]));

                        return response()->json(['statut' => 'success', 'message' => "Enregistrement", 'data' => $reponseDeLaCollecte, 'statutCode' => Response::HTTP_INTERNAL_SERVER_ERROR], Response::HTTP_INTERNAL_SERVER_ERROR);

                        $reponseDeLaCollecte->save();
                    }

                    /*if(isset($item['preuves']) && !empty($item['preuves']))
                    {
                        foreach($item['preuves'] as $preuve)
                        {
                            $this->storeFile($preuve, 'soumissions/preuves', $reponseDeLaCollecte, null, 'preuves');
                        }
                    }*/
                }

            }/*
            else if(isset($attributs['perception']) && !empty($attributs['perception'])){
                $soumission->fill($attributs['perception']);
                $soumission->save();
                foreach ($attributs['perception']['response_data'] as $key => $item) {

                    if(!(($questionDeGouvernance = app(QuestionDeGouvernanceRepository::class)->findById($item['questionId'])) && $questionDeGouvernance->programmeId == $programme->id))
                    {
                        throw new Exception( "Question de gouvernance introuvable dans le programme.", Response::HTTP_NOT_FOUND);
                    }

                    //$option = app(OptionDeReponseRepository::class)->findById($item['optionDeReponseId'])->where("programmeId", $programme->id)->first();
                    $option = app(OptionDeReponseRepository::class)->findById($item['optionDeReponseId']);

                    if(!$option && $option->programmeId == $programme->id) throw new Exception( "Cette option n'est pas dans le programme", Response::HTTP_NOT_FOUND);

                    if(!($reponseDeLaCollecte = $soumission->reponses_de_la_collecte()->where(['programmeId' => $programme->id, 'questionId' => $questionDeGouvernance->id])->first())){
                        $reponseDeLaCollecte = $soumission->reponses_de_la_collecte()->create(array_merge($item, ['formulaireDeGouvernanceId' => $soumission->formulaireDeGouvernance->id, 'questionId' => $questionDeGouvernance->id, 'optionDeReponseId' => $option->id, 'type' => 'question_operationnelle', 'programmeId' => $programme->id, 'point' => $option->formulaires_de_gouvernance()->wherePivot("formulaireDeGouvernanceId", $soumission->formulaireDeGouvernance->id)->first()->pivot->point]));
                    }
                    else{
                        unset($item['questionId']);
                        $reponseDeLaCollecte->fill(array_merge($item, ['formulaireDeGouvernanceId' => $soumission->formulaireDeGouvernance->id, 'optionDeReponseId' => $option->id, 'type' => 'question_operationnelle', 'programmeId' => $programme->id, 'point' => $option->formulaires_de_gouvernance()->wherePivot("formulaireDeGouvernanceId", $soumission->formulaireDeGouvernance->id)->first()->pivot->point]));
                        $reponseDeLaCollecte->save();
                    }
                }
            }*/

            //return response()->json(['statut' => 'success', 'message' => "Enregistrement réussir", 'data' => 'responseCount', 'statutCode' => Response::HTTP_INTERNAL_SERVER_ERROR], Response::HTTP_INTERNAL_SERVER_ERROR);

            if(($soumission->formulaireDeGouvernance->type == 'factuel' && $soumission->comite_members !== null) || ($soumission->formulaireDeGouvernance->type == 'perception' && $soumission->commentaire !== null && $soumission->sexe !== null && $soumission->age !== null && $soumission->categorieDeParticipant !== null)){
                
                $soumission->refresh();
                
                $responseCount = $soumission->formulaireDeGouvernance->questions_de_gouvernance()->whereHas('reponses', function($query) use ($soumission) {
                    $query->where(function($query){
                        $query->whereNotNull('sourceDeVerificationId')->orWhereNotNull('sourceDeVerification');
                    });

                    //$query->whereNotNull('sourceDeVerificationId')->orWhereNotNull('sourceDeVerification');

                    // Conditionally apply whereHas('preuves_de_verification') if formulaireDeGouvernance type is 'factuel'
                    if ($soumission->formulaireDeGouvernance->type == 'factuel') {
                        $query->whereHas('preuves_de_verification');
                    }

                })->count();

                if($responseCount === $soumission->formulaireDeGouvernance->questions_de_gouvernance->count()){
                    $soumission->submitted_at = now();
                    $soumission->submittedBy  = auth()->id();
                    $soumission->statut       = true;
    
                    $soumission->save();
                }
            }

            $acteur = Auth::check() ? Auth::user()->nom . " ". Auth::user()->prenom : "Inconnu";

            $message = $message ?? Str::ucfirst($acteur) . " a créé un " . strtolower(class_basename($soumission));

            LogActivity::addToLog("Enrégistrement", $message, get_class($soumission), $soumission->id);

            DB::commit();

            return response()->json(['statut' => 'success', 'message' => "Enregistrement réussir", 'data' => $soumission, 'statutCode' => Response::HTTP_CREATED], Response::HTTP_CREATED);

        } catch (\Throwable $th) {

            DB::rollBack();

            //throw $th;
            return response()->json(['statut' => 'error', 'message' => $th->getMessage(), 'errors' => [], 'statutCode' => Response::HTTP_INTERNAL_SERVER_ERROR], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    public function update($soumission, array $attributs) : JsonResponse
    {
        DB::beginTransaction();

        try {

            if(!is_object($soumission) && !($soumission = $this->repository->findById($soumission))) throw new Exception("Evaluation de gouvernance inconnue.", 500);

            $this->repository->update($soumission->id, $attributs);

            $soumission->refresh();

            $acteur = Auth::check() ? Auth::user()->nom . " ". Auth::user()->prenom : "Inconnu";

            $message = $message ?? Str::ucfirst($acteur) . " a modifié un " . strtolower(class_basename($soumission));

            LogActivity::addToLog("Modification", $message, get_class($soumission), $soumission->id);

            DB::commit();

            return response()->json(['statut' => 'success', 'message' => "Enregistrement réussir", 'data' => new SoumissionsResource($soumissions), 'statutCode' => Response::HTTP_CREATED], Response::HTTP_CREATED);

        } catch (\Throwable $th) {

            DB::rollBack();

            //throw $th;
            return response()->json(['statut' => 'error', 'message' => $th->getMessage(), 'errors' => [], 'statutCode' => Response::HTTP_INTERNAL_SERVER_ERROR], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }


    /**
     * Liste des soumissions d'une evaluation de gouvernance
     * 
     * return JsonResponse
     */
    public function fiche_de_synthese($soumission, array $columns = ['*'], array $relations = [], array $appends = []): JsonResponse
    {
        try
        {
            if(!is_object($soumission) && !($soumission = $this->repository->findById($soumission))) throw new Exception("Evaluation de gouvernance inconnue.", 500);

            return response()->json(['statut' => 'success', 'message' => null, 'data' => $soumission->fiche_de_synthese ? new FichesDeSyntheseResource($soumission->fiche_de_synthese) : null, 'statutCode' => Response::HTTP_OK], Response::HTTP_OK);
        }

        catch (\Throwable $th)
        {
            return response()->json(['statut' => 'error', 'message' => $th->getMessage(), 'errors' => []], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
    
    /**
     * Liste des soumissions d'une evaluation de gouvernance
     * 
     * return JsonResponse
     */
    public function recommandations($soumission, array $columns = ['*'], array $relations = [], array $appends = []): JsonResponse
    {
        try
        {
            if(!is_object($soumission) && !($soumission = $this->repository->findById($soumission))) throw new Exception("Evaluation de gouvernance inconnue.", 500);

            return response()->json(['statut' => 'success', 'message' => null, 'data' => RecommandationsResource::collection($soumission->recommandations), 'statutCode' => Response::HTTP_OK], Response::HTTP_OK);
        }

        catch (\Throwable $th)
        {
            return response()->json(['statut' => 'error', 'message' => $th->getMessage(), 'errors' => []], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
}