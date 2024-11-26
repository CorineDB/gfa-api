<?php

namespace App\Services;

use App\Http\Resources\FichesDeSyntheseResource;
use App\Http\Resources\gouvernance\ActionsAMenerResource;
use App\Repositories\ActionAMenerRepository;
use App\Repositories\EvaluationDeGouvernanceRepository;
use App\Traits\Helpers\HelperTrait;
use Core\Services\Contracts\BaseService;
use Core\Services\Interfaces\ActionAMenerServiceInterface;
use Exception;
use App\Traits\Helpers\LogActivity;
use Carbon\Carbon;
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
            if(Auth::user()->hasRole('administrateur')){
                $actions_a_mener = $this->repository->all();
            }
            else{
                //$projets = $this->repository->allFiltredBy([['attribut' => 'programmeId', 'operateur' => '=', 'valeur' => auth()->user()->programme->id]]);
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

            $attributs = array_merge($attributs, ['programmeId' => $programme->id, 'statut' => -1]);

            $action_a_mener = null;

            if(isset($attributs['evaluationId'])){
                if(($evaluation = app(EvaluationDeGouvernanceRepository::class)->findById($attributs['evaluationId']))){
                    $action_a_mener = $evaluation->actions_a_mener()->create($attributs);
                }
            }

            $acteur = Auth::check() ? Auth::user()->nom . " ". Auth::user()->prenom : "Inconnu";

            $message = $message ?? Str::ucfirst($acteur) . " a créé un " . strtolower(class_basename($action_a_mener));

            LogActivity::addToLog("Enrégistrement", $message, get_class($action_a_mener), $action_a_mener->id);

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

            $this->repository->update($action_a_mener->id, $attributs);

            $action_a_mener->refresh();

            $acteur = Auth::check() ? Auth::user()->nom . " ". Auth::user()->prenom : "Inconnu";

            $message = $message ?? Str::ucfirst($acteur) . " a modifié un " . strtolower(class_basename($action_a_mener));

            LogActivity::addToLog("Modification", $message, get_class($action_a_mener), $action_a_mener->id);

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

            if(!is_object($action_a_mener) && !($action_a_mener = $this->repository->findById($action_a_mener))) throw new Exception("Ce fond n'existe pas", 500);

            if(!Auth::user()->hasRole('unitee-de-gestion')){
                return response()->json(['statut' => 'error', 'message' => "Pas la permission pour", 'data' => null, 'statutCode' => Response::HTTP_FORBIDDEN], Response::HTTP_FORBIDDEN);
            }

            if($action_a_mener->statut != 2){
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

                $action_a_mener->save();

                if(isset($attributs['commentaire']))
                {
                    $action_a_mener->commentaires()->create(['contenu' => $attributs['commentaire'], 'auteurId' => Auth::user()->id]);
                }
                
                $action_a_mener->refresh();

                $acteur = Auth::check() ? Auth::user()->nom . " ". Auth::user()->prenom : "Inconnu";
    
                $message = $message ?? Str::ucfirst($acteur) . " a modifié un " . strtolower(class_basename($action_a_mener));
    
                LogActivity::addToLog("Modification", $message, get_class($action_a_mener), $action_a_mener->id);
    
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

            if(!Auth::user()->hasRole('organisation')){
                return response()->json(['statut' => 'error', 'message' => "Pas la permission pour", 'data' => null, 'statutCode' => Response::HTTP_FORBIDDEN], Response::HTTP_FORBIDDEN);
            }

            if($action_a_mener->statut < 0){
                return response()->json(['statut' => 'error', 'message' => "Action pas encore demarrer", 'data' => null, 'statutCode' => Response::HTTP_OK], Response::HTTP_OK);
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

                $action_a_mener->statut == 2;
                
                $action_a_mener->save();

                if(isset($attributs['commentaire']))
                {
                    $action_a_mener->commentaires()->create(['contenu' => $attributs['commentaire'], 'auteurId' => Auth::user()->id]);
                }
                
                $action_a_mener->refresh();

                $acteur = Auth::check() ? Auth::user()->nom . " ". Auth::user()->prenom : "Inconnu";
    
                $message = $message ?? Str::ucfirst($acteur) . " a modifié un " . strtolower(class_basename($action_a_mener));
    
                LogActivity::addToLog("Modification", $message, get_class($action_a_mener), $action_a_mener->id);
    
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