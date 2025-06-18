<?php

namespace App\Services\enquetes_de_gouvernance;

use App\Http\Resources\gouvernance\ActionsAMenerResource;
use App\Models\enquetes_de_gouvernance\PrincipeDeGouvernancePerception;
use App\Models\Organisation;
use App\Models\enquetes_de_gouvernance\Recommandation;
use App\Models\UniteeDeGestion;
use App\Repositories\enquetes_de_gouvernance\ActionAMenerRepository;
use App\Repositories\enquetes_de_gouvernance\EvaluationDeGouvernanceRepository;
use App\Repositories\enquetes_de_gouvernance\IndicateurDeGouvernanceFactuelRepository;
use App\Repositories\enquetes_de_gouvernance\QuestionOperationnelleRepository;
use App\Repositories\enquetes_de_gouvernance\PrincipeDeGouvernanceFactuelRepository;
use App\Repositories\enquetes_de_gouvernance\RecommandationRepository;
use App\Traits\Helpers\HelperTrait;
use Core\Services\Contracts\BaseService;
use Exception;
use App\Traits\Helpers\LogActivity;
use Carbon\Carbon;
use Core\Services\Interfaces\enquetes_de_gouvernance\ActionAMenerServiceInterface;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Response;

/**
* Interface ActionAMenerServiceInterface
* @package Core\Services\Interfaces
*/
class ActionAMenerService extends BaseService implements ActionAMenerServiceInterface
{
    use HelperTrait;

    /**
     * @var service
     */
    protected $repository;

    /**
     * ActionAMenerRepository constructor.
     *
     * @param ActionAMenerRepository $actionAMenerRepository
     */
    public function __construct(ActionAMenerRepository $actionAMenerRepository)
    {
        parent::__construct($actionAMenerRepository);
    }

    public function all(array $columns = ['*'], array $relations = []): JsonResponse
    {
        try
        {
            $actions_a_mener = [];

            if ((Auth::user()->hasRole('organisation') || ( get_class(auth()->user()->profilable) == Organisation::class))) {
                $actions_a_mener = Auth::user()->profilable->actions_a_mener;
            }
            else if(Auth::user()->hasRole("unitee-de-gestion") || ( get_class(auth()->user()->profilable) == UniteeDeGestion::class)){
                $actions_a_mener = Auth::user()->programme->actions_a_mener;
            }

            return response()->json(['statut' => 'success', 'message' => null, 'data' => ActionsAMenerResource::collection($actions_a_mener), 'statutCode' => Response::HTTP_OK], Response::HTTP_OK);
        }

        catch (\Throwable $th)
        {
            return response()->json(['statut' => 'error', 'message' => $th->getMessage(), 'errors' => []], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    public function findById($action_a_mener, array $columns = ['*'], array $relations = [], array $appends = []): JsonResponse
    {
        try
        {
            if(!is_object($action_a_mener) && !($action_a_mener = $this->repository->findById($action_a_mener))) throw new Exception("ActionAMener inconnue.", 500);

            return response()->json(['statut' => 'success', 'message' => null, 'data' => new ActionsAMenerResource($action_a_mener), 'statutCode' => Response::HTTP_OK], Response::HTTP_OK);
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

            $programme = Auth::user()->programme;

            $organisation = Auth::user()->profilable;

            $attributs = array_merge($attributs, ['organisationId' => $organisation->id, 'programmeId' => $programme->id, 'statut' => -1]);

            $action_a_mener = null;

            /* if(isset($attributs['evaluationId'])){
                if(($evaluation = app(EvaluationDeGouvernanceRepository::class)->findById($attributs['evaluationId']))){
                    $action_a_mener = $evaluation->actions_a_mener()->create($attributs);
                }
            } */

            if(isset($attributs['evaluationId'])){
                if(!($evaluation = app(EvaluationDeGouvernanceRepository::class)->findById($attributs['evaluationId']))){
                    throw new Exception("Cette evaluation n'existe pas", 500);
                }
            }

            if(isset($attributs['recommandationId'])){
                if(!($recommandation = app(RecommandationRepository::class)->findById($attributs['recommandationId']))){
                    throw new Exception("Cette recommandation n'existe pas", 500);
                }
                else{
                    $attributs = array_merge($attributs, ['actionable_id' => $attributs['recommandationId'], 'actionable_type' => Recommandation::class]);
                }
            }

            $action_a_mener = $this->repository->create($attributs);

            if(isset($attributs['indicateurs'])){

                $indicateurs = [];

                foreach($attributs['indicateurs'] as $id)
                {
                    if(!($indicateur = app(IndicateurDeGouvernanceFactuelRepository::class)->findById($id))) throw new Exception("Indicateur introuvable", Response::HTTP_NOT_FOUND);

                    array_push($indicateurs, $indicateur->id);
                }

                $action_a_mener->indicateurs()->attach($indicateurs, ["programmeId" => $attributs['programmeId']]);

            }

            if(isset($attributs['questions_operationnelle'])){

                $questions_operationnelle = [];

                foreach($attributs['questions_operationnelle'] as $id)
                {
                    if(!($question_operationnelle = app(QuestionOperationnelleRepository::class)->findById($id))) throw new Exception("Question Operationnelle introuvable", Response::HTTP_NOT_FOUND);

                    array_push($questions_operationnelle, $question_operationnelle->id);
                }

                $action_a_mener->questions_operationnelle()->attach($questions_operationnelle, ["programmeId" => $attributs['programmeId']]);

            }

            if(isset($attributs['principes_factuel_de_gouvernance'])){

                $principes_factuel_de_gouvernance = [];

                foreach($attributs['principes_factuel_de_gouvernance'] as $id)
                {
                    if(!($principe_factuel_de_gouvernance = app(PrincipeDeGouvernanceFactuelRepository::class)->findById($id))) throw new Exception("Principe factuel introuvable", Response::HTTP_NOT_FOUND);

                    array_push($principes_factuel_de_gouvernance, $principe_factuel_de_gouvernance->id);
                }

                $action_a_mener->principes_factuel_de_gouvernance()->attach($principes_factuel_de_gouvernance, ["programmeId" => $attributs['programmeId']]);

            }

            if(isset($attributs['principes_de_perception_de_gouvernance'])){

                $principes_de_perception_de_gouvernance = [];

                foreach($attributs['principes_de_perception_de_gouvernance'] as $id)
                {
                    if(!($principe_de_perception_de_gouvernance = app(PrincipeDeGouvernancePerception::class)->findById($id))) throw new Exception("Principe de perception introuvable", Response::HTTP_NOT_FOUND);

                    array_push($principes_de_perception_de_gouvernance, $principe_de_perception_de_gouvernance->id);
                }

                $action_a_mener->principes_de_perception_de_gouvernance()->attach($principes_de_perception_de_gouvernance, ["programmeId" => $attributs['programmeId']]);

            }

            $acteur = Auth::check() ? Auth::user()->nom . " ". Auth::user()->prenom : "Inconnu";

            $message = $message ?? Str::ucfirst($acteur) . " a créé un " . strtolower(class_basename($action_a_mener));

            //LogActivity::addToLog("Enrégistrement", $message, get_class($action_a_mener), $action_a_mener->id);

            DB::commit();

            return response()->json(['statut' => 'success', 'message' => "Enregistrement réussir", 'data' => new ActionsAMenerResource($action_a_mener), 'statutCode' => Response::HTTP_CREATED], Response::HTTP_CREATED);

        } catch (\Throwable $th) {

            DB::rollBack();

            //throw $th;
            return response()->json(['statut' => 'error', 'message' => $th->getMessage(), 'errors' => [], 'statutCode' => Response::HTTP_INTERNAL_SERVER_ERROR], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    public function update($action_a_mener, array $attributs) : JsonResponse
    {
        DB::beginTransaction();

        try {

            if(!is_object($action_a_mener) && !($action_a_mener = $this->repository->findById($action_a_mener))) throw new Exception("Ce fond n'existe pas", 500);

            if(isset($attributs['evaluationId'])){
                if(!($evaluation = app(EvaluationDeGouvernanceRepository::class)->findById($attributs['evaluationId']))){
                    throw new Exception("Cette evaluation n'existe pas", 500);
                }
            }

            if(isset($attributs['recommandationId'])){
                if(!($recommandation = app(RecommandationRepository::class)->findById($attributs['recommandationId']))){
                    throw new Exception("Cette recommandation n'existe pas", 500);
                }
                else{

                    $attributs = array_merge($attributs, ['actionable_id' => $attributs['recommandationId'], 'actionable_type' => Recommandation::class]);
                }
            }

            $this->repository->update($action_a_mener->id, $attributs);

            if(isset($attributs['indicateurs'])){

                $indicateurs = [];

                foreach($attributs['indicateurs'] as $id)
                {
                    if(!($indicateur = app(IndicateurDeGouvernanceFactuelRepository::class)->findById($id))) throw new Exception("Indicateur introuvable", Response::HTTP_NOT_FOUND);

                    array_push($indicateurs, $indicateur->id);
                }

                $action_a_mener->indicateurs()->sync($indicateurs, ["programmeId" => $attributs['programmeId']]);

            }

            if(isset($attributs['questions_operationnelle'])){

                $questions_operationnelle = [];

                foreach($attributs['questions_operationnelle'] as $id)
                {
                    if(!($question_operationnelle = app(QuestionOperationnelleRepository::class)->findById($id))) throw new Exception("Question Operationnelle introuvable", Response::HTTP_NOT_FOUND);

                    array_push($questions_operationnelle, $question_operationnelle->id);
                }

                $action_a_mener->questions_operationnelle()->sync($questions_operationnelle, ["programmeId" => $attributs['programmeId']]);

            }

            if(isset($attributs['principes_factuel_de_gouvernance'])){

                $principes_factuel_de_gouvernance = [];

                foreach($attributs['principes_factuel_de_gouvernance'] as $id)
                {
                    if(!($principe_factuel_de_gouvernance = app(PrincipeDeGouvernanceFactuelRepository::class)->findById($id))) throw new Exception("Principe factuel introuvable", Response::HTTP_NOT_FOUND);

                    array_push($principes_factuel_de_gouvernance, $principe_factuel_de_gouvernance->id);
                }

                $action_a_mener->principes_factuel_de_gouvernance()->sync($principes_factuel_de_gouvernance, ["programmeId" => $attributs['programmeId']]);

            }

            if(isset($attributs['principes_de_perception_de_gouvernance'])){

                $principes_de_perception_de_gouvernance = [];

                foreach($attributs['principes_de_perception_de_gouvernance'] as $id)
                {
                    if(!($principe_de_perception_de_gouvernance = app(PrincipeDeGouvernancePerception::class)->findById($id))) throw new Exception("Principe de perception introuvable", Response::HTTP_NOT_FOUND);

                    array_push($principes_de_perception_de_gouvernance, $principe_de_perception_de_gouvernance->id);
                }

                $action_a_mener->principes_de_perception_de_gouvernance()->sync($principes_de_perception_de_gouvernance, ["programmeId" => $attributs['programmeId']]);

            }

            $action_a_mener->refresh();

            $acteur = Auth::check() ? Auth::user()->nom . " ". Auth::user()->prenom : "Inconnu";

            $message = $message ?? Str::ucfirst($acteur) . " a modifié un " . strtolower(class_basename($action_a_mener));

            //LogActivity::addToLog("Modification", $message, get_class($action_a_mener), $action_a_mener->id);

            DB::commit();

            return response()->json(['statut' => 'success', 'message' => "Enregistrement réussir", 'data' => new ActionsAMenerResource($action_a_mener), 'statutCode' => Response::HTTP_CREATED], Response::HTTP_CREATED);

        } catch (\Throwable $th) {

            DB::rollBack();

            //throw $th;
            return response()->json(['statut' => 'error', 'message' => $th->getMessage(), 'errors' => [], 'statutCode' => Response::HTTP_INTERNAL_SERVER_ERROR], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     *
     * @param array $attributs
     * @return JsonResponse
     */
    public function valider($action_a_mener, array $attributs): JsonResponse
    {
        DB::beginTransaction();

        try {

            if(!is_object($action_a_mener) && !($action_a_mener = $this->repository->findById($action_a_mener))) throw new Exception("Cette action a mener n'existe pas", 500);

            if(!Auth::user()->hasRole('unitee-de-gestion')){
                return response()->json(['statut' => 'error', 'message' => "Pas la permission pour", 'data' => null, 'statutCode' => Response::HTTP_FORBIDDEN], Response::HTTP_FORBIDDEN);
            }

            if($action_a_mener->statut != 2 && !$action_a_mener->has_upload_preuves){
                return response()->json(['statut' => 'error', 'message' => "Action pas encore terminer", 'data' => null, 'statutCode' => Response::HTTP_OK], Response::HTTP_OK);
            }

            if($action_a_mener->est_valider == true){
                return response()->json(['statut' => 'error', 'message' => "Action deje valider", 'data' => null, 'statutCode' => Response::HTTP_OK], Response::HTTP_OK);
            }

            if(isset($attributs['est_valider'])){

                $action_a_mener->est_valider = $attributs['est_valider'];

                if(isset($attributs['est_valider']) && $attributs['est_valider']){
                    $action_a_mener->validated_at = Carbon::now();
                }

                if($action_a_mener->validated_at){

                    $action_a_mener->statut = 1;
                }

                $action_a_mener->save();

                if(isset($attributs['commentaire']))
                {
                    $action_a_mener->commentaires()->create(['contenu' => $attributs['commentaire'], 'auteurId' => Auth::user()->id]);
                }

                $action_a_mener->refresh();

                $acteur = Auth::check() ? Auth::user()->nom . " ". Auth::user()->prenom : "Inconnu";

                $message = $message ?? Str::ucfirst($acteur) . " a modifié un " . strtolower(class_basename($action_a_mener));

                //LogActivity::addToLog("Modification", $message, get_class($action_a_mener), $action_a_mener->id);

                DB::commit();

                return response()->json(['statut' => 'success', 'message' => "Enregistrement réussir", 'data' => new ActionsAMenerResource($action_a_mener), 'statutCode' => Response::HTTP_CREATED], Response::HTTP_CREATED);

            }

            return response()->json(['statut' => 'success', 'message' => null, 'data' => null, 'statutCode' => Response::HTTP_OK], Response::HTTP_OK);

        } catch (\Throwable $th) {

            DB::rollback();

            //throw $th;
            return response()->json(['statut' => 'error', 'message' => $th->getMessage(), 'errors' => []], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     *
     * @param array $attributs
     * @return JsonResponse
     */
    public function notifierActionAMenerEstTerminer($action_a_mener, array $attributs): JsonResponse
    {
        DB::beginTransaction();

        try {

            if(!is_object($action_a_mener) && !($action_a_mener = $this->repository->findById($action_a_mener))) throw new Exception("Ce fond n'existe pas", 500);

            if((!Auth::user()->hasRole('organisation')) && ( get_class(auth()->user()->profilable) != Organisation::class)){
                return response()->json(['statut' => 'error', 'message' => "Pas la permission pour", 'data' => null, 'statutCode' => Response::HTTP_FORBIDDEN], Response::HTTP_FORBIDDEN);
            }

            if($action_a_mener->statut > -1 && $action_a_mener->statut < 2){
                return response()->json(['statut' => 'error', 'message' => "Action pas encore demarrer ou deja notifier", 'data' => null, 'statutCode' => Response::HTTP_OK], Response::HTTP_OK);
            }

            if($action_a_mener->est_valider == true){
                return response()->json(['statut' => 'error', 'message' => "Action deje valider", 'data' => null, 'statutCode' => Response::HTTP_OK], Response::HTTP_OK);
            }

            if(isset($attributs['preuves'])){

                if (isset($item['preuves']) && !empty($item['preuves'])) {
                    foreach ($item['preuves'] as $preuve) {
                        $this->storeFile($preuve, 'actions_a_mener/preuves', $action_a_mener, null, 'preuves');
                    }
                }

                $action_a_mener->has_upload_preuves = true;

                $action_a_mener->statut = 2;

                $action_a_mener->save();

                if(isset($attributs['commentaire']))
                {
                    $action_a_mener->commentaires()->create(['contenu' => $attributs['commentaire'], 'auteurId' => Auth::user()->id]);
                }

                $action_a_mener->refresh();

                $acteur = Auth::check() ? Auth::user()->nom . " ". Auth::user()->prenom : "Inconnu";

                $message = $message ?? Str::ucfirst($acteur) . " a modifié un " . strtolower(class_basename($action_a_mener));

                //LogActivity::addToLog("Modification", $message, get_class($action_a_mener), $action_a_mener->id);

                DB::commit();

                return response()->json(['statut' => 'success', 'message' => "Enregistrement réussir", 'data' => new ActionsAMenerResource($action_a_mener), 'statutCode' => Response::HTTP_CREATED], Response::HTTP_CREATED);

            }

            return response()->json(['statut' => 'success', 'message' => null, 'data' => null, 'statutCode' => Response::HTTP_OK], Response::HTTP_OK);
        } catch (\Throwable $th) {

            DB::rollback();

            //throw $th;
            return response()->json(['statut' => 'error', 'message' => $th->getMessage(), 'errors' => []], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

}