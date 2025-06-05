<?php

namespace App\Services\enquetes_de_gouvernance;

use App\Http\Resources\enquetes_de_gouvernance\formulaires_de_gouvernance_de_perception\ListFormulaireDeGouvernanceDePerceptionResource;
use App\Repositories\enquetes_de_gouvernance\FormulaireDePerceptionDeGouvernanceRepository;
use App\Repositories\enquetes_de_gouvernance\QuestionOperationnelleRepository;
use App\Repositories\enquetes_de_gouvernance\OptionDeReponseGouvernanceRepository;
use App\Repositories\enquetes_de_gouvernance\PrincipeDeGouvernancePerceptionRepository;

use Core\Services\Contracts\BaseService;
use Core\Services\Interfaces\enquetes_de_gouvernance\FormulaireDePerceptionDeGouvernanceServiceInterface;
use Exception;
use App\Traits\Helpers\LogActivity;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Response;

/**
* Interface FormulaireDeGouvernanceServiceInterface
* @package Core\Services\Interfaces
*/
class FormulaireDePerceptionDeGouvernanceService extends BaseService implements FormulaireDePerceptionDeGouvernanceServiceInterface
{

    /**
     * @var service
     */
    protected $repository;

    /**
     * FormulaireDePerceptionDeGouvernanceRepository constructor.
     *
     * @param FormulaireDePerceptionDeGouvernanceRepository $formulaireDeGouvernanceRepository
     */
    public function __construct(FormulaireDePerceptionDeGouvernanceRepository $formulaireDeGouvernanceRepository)
    {
        parent::__construct($formulaireDeGouvernanceRepository);
    }

    public function all(array $columns = ['*'], array $relations = []): JsonResponse
    {
        try
        {
            $programme = Auth::user()->programme;

            if((Auth::user()->hasRole('administrateur') || auth()->user()->profilable_type == 'App\\Models\\Administrateur')){
                $formulaires_de_perception_de_gouvernance = $this->repository->all();
            }
            else{
                $formulaires_de_perception_de_gouvernance = $programme->formulaires_de_perception_gouvernance;
            }

            return response()->json(['statut' => 'success', 'message' => null, 'data' => ListFormulaireDeGouvernanceDePerceptionResource::collection($formulaires_de_perception_de_gouvernance), 'statutCode' => Response::HTTP_OK], Response::HTTP_OK);
        }

        catch (\Throwable $th)
        {
            return response()->json(['statut' => 'error', 'message' => $th->getMessage(), 'errors' => []], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    public function allFiltredBy(array $filtres = [], array $columns = ['*'], array $relations = []) : JsonResponse
    {
        try
        {
            $filtres = array_merge($filtres, ['programmeId__eq' => auth()->user()->programmeId]);
            return response()->json(['statut' => 'success', 'message' => null, 'data' => ListFormulaireDeGouvernanceDePerceptionResource::collection($this->repository->filterBy($filtres, $columns, $relations)), 'statutCode' => Response::HTTP_OK], Response::HTTP_OK);
        }

        catch (\Throwable $th)
        {
            return response()->json(['statut' => 'error', 'message' => $th->getMessage(), 'errors' => []], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    public function findById($formulaireDeGouvernance, array $columns = ['*'], array $relations = [], array $appends = []): JsonResponse
    {
        try
        {
            if(!is_object($formulaireDeGouvernance) && !($formulaireDeGouvernance = $this->repository->findById($formulaireDeGouvernance))) throw new Exception("Formulaire de gouvernance inconnue.", 500);

            return response()->json(['statut' => 'success', 'message' => null, 'data' => new ListFormulaireDeGouvernanceDePerceptionResource($formulaireDeGouvernance), 'statutCode' => Response::HTTP_OK], Response::HTTP_OK);
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

            $programmeId = Auth::user()->programme->id;

            $attributs = array_merge($attributs, ['programmeId' => $programmeId, 'created_by' => Auth::id()]);

            $formulaireDeGouvernance = $this->repository->create($attributs);

            if(isset($attributs['perception']) && $attributs['perception'] !== null){

                $options = [];

                foreach ($attributs['perception']["options_de_reponse"] as $key => $option_de_reponse) {

                    $option = app(OptionDeReponseGouvernanceRepository::class)->findById($option_de_reponse['id']);

                    if(!$option && $option->programmeId == $programmeId) throw new Exception( "Cette option n'est pas dans le programme", Response::HTTP_NOT_FOUND);

                    $options[$option->id] = ['point' => $option_de_reponse['point'], 'programmeId' => $programmeId];
                }

                $formulaireDeGouvernance->options_de_reponse()->attach($options);

                foreach ($attributs['perception']["principes_de_gouvernance"] as $key => $principe_de_gouvernance) {

                    //if(!($principeDeGouvernance = app(PrincipeDeGouvernancePerceptionRepository::class)->findById($principe_de_gouvernance['id']))->where("programmeId", $programmeId)->first()) throw new Exception( "Ce principe de gouvernance n'est pas dans le programme", Response::HTTP_NOT_FOUND);
                    if(!(($principeDeGouvernance = app(PrincipeDeGouvernancePerceptionRepository::class)->findById($principe_de_gouvernance['id'])) && $principeDeGouvernance->programmeId == $programmeId))
                    {
                        throw new Exception( "Ce principe de gouvernance n'est pas dans le programme", Response::HTTP_NOT_FOUND);
                    }

                    /*$principeDeGouvernanceCategorie = $principeDeGouvernance->categories_de_gouvernance()->whereNull("categorieDePerceptionDeGouvernanceId")->where("position", $principe_de_gouvernance['position'])->whereHas("formulaire_de_gouvernance", function($query) use ($programmeId){
                        $query->where('programmeId', $programmeId);
                    })->first();

                    if(!$principeDeGouvernanceCategorie){*/
                        $position = isset($principe_de_gouvernance['position']) ? $principe_de_gouvernance['position'] : 0;
                        $principeDeGouvernanceCategorie = $principeDeGouvernance->categories_de_gouvernance()->create(['programmeId' => $programmeId, "position" => $position, 'categorieDePerceptionDeGouvernanceId' => null, 'formulaireDePerceptionId' => $formulaireDeGouvernance->id]);
                    //}

                    foreach ($principe_de_gouvernance["questions_operationnelle"] as $key => $question_operationnelle) {

                        //if(!($indicateurDeGouvernance = app(QuestionOperationnelleRepository::class)->findById($question_operationnelle))) throw new Exception( "Cet indicateur de gouvernance n'est pas dans le programme", Response::HTTP_NOT_FOUND);

                        $questionOperationnelleId = $question_operationnelle;
                        if(isset($question_operationnelle['id']) ){

                            $questionOperationnelleId = $question_operationnelle['id'];
                        }

                        if(!(($questionOperationnelle = app(QuestionOperationnelleRepository::class)->findById($questionOperationnelleId))))
                        {
                            throw new Exception( "Cette question operationnelle n'est pas dans le programme", Response::HTTP_NOT_FOUND);
                        }

                        $position = isset($question_operationnelle['position']) ? $question_operationnelle['position'] : 0;
                        $question_operationnelle = $principeDeGouvernanceCategorie->questions_de_gouvernance()->create(["position" => $position, 'formulaireDePerceptionId' => $formulaireDeGouvernance->id, 'programmeId' => $programmeId, 'questionOperationnelleId' => $questionOperationnelle->id]);
                    }

                }
            }

            $formulaireDeGouvernance->save();
            $formulaireDeGouvernance->refresh();

            $acteur = Auth::check() ? Auth::user()->nom . " ". Auth::user()->prenom : "Inconnu";

            $message = $message ?? Str::ucfirst($acteur) . " a créé un " . strtolower(class_basename($formulaireDeGouvernance));

            //LogActivity::addToLog("Enrégistrement", $message, get_class($formulaireDeGouvernance), $formulaireDeGouvernance->id);

            DB::commit();

            return response()->json(['statut' => 'success', 'message' => "Enregistrement réussir", 'data' => new ListFormulaireDeGouvernanceDePerceptionResource($formulaireDeGouvernance), 'statutCode' => Response::HTTP_CREATED], Response::HTTP_CREATED);

        }
        catch (\Throwable $th) {

            DB::rollBack();

            //throw $th;
            return response()->json(['statut' => 'error', 'message' => $th->getMessage(), 'errors' => [], 'statutCode' => Response::HTTP_INTERNAL_SERVER_ERROR], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    public function update($formulaireDeGouvernance, array $attributs) : JsonResponse
    {
        DB::beginTransaction();

        try {

            if(!is_object($formulaireDeGouvernance) && !($formulaireDeGouvernance = $this->repository->findById($formulaireDeGouvernance))) throw new Exception("Cette option de reponse n'existe pas", 500);

            $programmeId = Auth::user()->programme->id;

            $attributs = array_merge($attributs, ['programmeId' => $programmeId, 'created_by' => Auth::id()]);

            $this->repository->update($formulaireDeGouvernance->id, $attributs);

            $formulaireDeGouvernance->refresh();

            if(isset($attributs['perception']) && $attributs['perception'] !== null){

                $options = [];

                if(isset($attributs['perception']["options_de_reponse"])) {
                    foreach ($attributs['perception']["options_de_reponse"] as $key => $option_de_reponse) {

                        $option = app(OptionDeReponseGouvernanceRepository::class)->findById($option_de_reponse['id']);

                        if(!$option && $option->programmeId == $programmeId) throw new Exception( "Cette option n'est pas dans le programme", Response::HTTP_NOT_FOUND);

                        $options[$option->id] = ['point' => $option_de_reponse['point'], 'programmeId' => $programmeId];

                    }

                    $formulaireDeGouvernance->options_de_reponse()->sync($options);
                }

                if(isset($attributs['perception']["principes_de_gouvernance"]) && $attributs['perception']["principes_de_gouvernance"] !== null){
                    $categories_de_gouvernance = [];

                    foreach ($attributs['perception']["principes_de_gouvernance"] as $key => $principe_de_gouvernance) {

                        if(!(($principeDeGouvernance = app(PrincipeDeGouvernancePerceptionRepository::class)->findById($principe_de_gouvernance['id'])) && $principeDeGouvernance->programmeId == $programmeId))
                        {
                            throw new Exception( "Ce principe de gouvernance n'est pas dans le programme", Response::HTTP_NOT_FOUND);
                        }

                        $principeDeGouvernanceCategorie = $principeDeGouvernance->categories_de_gouvernance()->whereNull("categorieDePerceptionDeGouvernanceId")->where('programmeId', $programmeId)/* ->where("position", $principe_de_gouvernance['position']) */->whereHas("formulaire_de_gouvernance", function($query) use ($formulaireDeGouvernance, $programmeId){
                            $query->where('id', $formulaireDeGouvernance->id)->where('programmeId', $programmeId);
                        })->first();

                        if(!$principeDeGouvernanceCategorie){

                            $position = isset($principe_de_gouvernance['position']) ? $principe_de_gouvernance['position'] : $formulaireDeGouvernance->categories_de_gouvernance->count() + 1;

                            $principeDeGouvernanceCategorie = $principeDeGouvernance->categories_de_gouvernance()->create(['programmeId' => $programmeId, "position" => $position, 'categorieDePerceptionDeGouvernanceId' => null, 'formulaireDePerceptionId' => $formulaireDeGouvernance->id]);
                        }else{
                            $position = isset($principe_de_gouvernance['position']) ? $principe_de_gouvernance['position'] : $principeDeGouvernanceCategorie->position;

                            $principeDeGouvernanceCategorie->position = $position;
                            $principeDeGouvernanceCategorie->save();
                        }

                        $categories_de_gouvernance[] = $principeDeGouvernanceCategorie->id;

                        $questions = [];

                        foreach ($principe_de_gouvernance["questions_operationnelle"] as $key => $question_operationnelle) {

                            $questionOperationnelleId = $question_operationnelle;
                            if(isset($question_operationnelle['id']) ){
                                $questionOperationnelleId = $question_operationnelle['id'];
                            }

                            if(!(($questionOperationnelle = app(QuestionOperationnelleRepository::class)->findById($questionOperationnelleId))))
                            {
                                throw new Exception( "Cette question operationnelle n'est pas dans le programme", Response::HTTP_NOT_FOUND);
                            }

                            $questionDeGouvernance = $principeDeGouvernanceCategorie->questions_de_gouvernance()->where('questionOperationnelleId', $questionOperationnelle->id)->where('programmeId', $programmeId)/* ->where("position", $principe_de_gouvernance['position']) */->whereHas("formulaire_de_gouvernance", function($query) use ($formulaireDeGouvernance, $programmeId){
                                $query->where('id', $formulaireDeGouvernance->id)->where('programmeId', $programmeId);
                            })->first();

                            if(!$questionDeGouvernance){

                                $position = isset($question_operationnelle['position']) ? $question_operationnelle['position'] : $principeDeGouvernanceCategorie->questions_de_gouvernance->count() + 1;

                                $questionDeGouvernance = $principeDeGouvernanceCategorie->questions_de_gouvernance()->create(["position" => $position, 'formulaireDePerceptionId' => $formulaireDeGouvernance->id, 'programmeId' => $programmeId, 'questionOperationnelleId' => $questionOperationnelle->id]);
                            }else{
                                $position = isset($question_operationnelle['position']) ? $question_operationnelle['position'] : $questionDeGouvernance->position;

                                $questionDeGouvernance->position = $position;
                                $questionDeGouvernance->save();
                            }

                            $questions[] = $questionDeGouvernance->id;
                        }

                        $formulaireDeGouvernance->questions_de_gouvernance()->where('categorieDePerceptionDeGouvernanceId', $principeDeGouvernanceCategorie->id)->whereNotIn('id', $questions)->delete();
                    }

                    $formulaireDeGouvernance->categories_de_gouvernance()->whereNotIn('id', $categories_de_gouvernance)->delete();
                }
            }

            $acteur = Auth::check() ? Auth::user()->nom . " ". Auth::user()->prenom : "Inconnu";

            $message = $message ?? Str::ucfirst($acteur) . " a modifié un " . strtolower(class_basename($formulaireDeGouvernance));

            //LogActivity::addToLog("Modification", $message, get_class($formulaireDeGouvernance), $formulaireDeGouvernance->id);

            DB::commit();

            return response()->json(['statut' => 'success', 'message' => "Enregistrement réussir", 'data' => new ListFormulaireDeGouvernanceDePerceptionResource($formulaireDeGouvernance), 'statutCode' => Response::HTTP_CREATED], Response::HTTP_CREATED);

        } catch (\Throwable $th) {

            DB::rollBack();

            //throw $th;
            return response()->json(['statut' => 'error', 'message' => $th->getMessage(), 'errors' => [], 'statutCode' => Response::HTTP_INTERNAL_SERVER_ERROR], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

}