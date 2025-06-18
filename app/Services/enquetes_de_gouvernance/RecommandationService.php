<?php

namespace App\Services\enquetes_de_gouvernance;

use App\Http\Resources\gouvernance\RecommandationsResource;
use App\Models\Organisation;
use App\Repositories\enquetes_de_gouvernance\EvaluationDeGouvernanceRepository;
use App\Repositories\enquetes_de_gouvernance\RecommandationRepository;
use Core\Services\Contracts\BaseService;
use Core\Services\Interfaces\enquetes_de_gouvernance\RecommandationServiceInterface;
use Exception;
use App\Traits\Helpers\LogActivity;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Response;

/**
* Interface RecommandationServiceInterface
* @package Core\Services\Interfaces
*/
class RecommandationService extends BaseService implements RecommandationServiceInterface
{

    /**
     * @var service
     */
    protected $repository;

    /**
     * RecommandationRepository constructor.
     *
     * @param RecommandationRepository $recommandationRepository
     */
    public function __construct(RecommandationRepository $recommandationRepository)
    {
        parent::__construct($recommandationRepository);
    }

    public function all(array $columns = ['*'], array $relations = []): JsonResponse
    {
        try
        {
            if((Auth::user()->hasRole('administrateur') || auth()->user()->profilable_type == "App\\Models\\Administrateur")){
                $recommandations = $this->repository->all();
            }
            else if ((Auth::user()->hasRole('organisation') || ( get_class(auth()->user()->profilable) == Organisation::class))) {
                $recommandations = Auth::user()->profilable->recommandations;
            }
            else{
                $recommandations = Auth::user()->programme->recommandations;
            }

            return response()->json(['statut' => 'success', 'message' => null, 'data' => RecommandationsResource::collection($recommandations), 'statutCode' => Response::HTTP_OK], Response::HTTP_OK);
        }

        catch (\Throwable $th)
        {
            return response()->json(['statut' => 'error', 'message' => $th->getMessage(), 'errors' => []], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    public function findById($recommandation, array $columns = ['*'], array $relations = [], array $appends = []): JsonResponse
    {
        try
        {
            if(!is_object($recommandation) && !($recommandation = $this->repository->findById($recommandation))) throw new Exception("Recommandation inconnue.", 500);

            return response()->json(['statut' => 'success', 'message' => null, 'data' => new RecommandationsResource($recommandation), 'statutCode' => Response::HTTP_OK], Response::HTTP_OK);
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

            dump($organisation);;

            $attributs = array_merge($attributs, ['organisationId' => $organisation->id, 'programmeId' => $programme->id, 'statut' => -1]);

            if(isset($attributs['evaluationId'])){
                if(!($evaluation = app(EvaluationDeGouvernanceRepository::class)->findById($attributs['evaluationId']))){
                    throw new Exception("Cette evaluation n'existe pas", 500);
                }
            }

            $recommandation = $this->repository->create($attributs);

            $acteur = Auth::check() ? Auth::user()->nom . " ". Auth::user()->prenom : "Inconnu";

            $message = $message ?? Str::ucfirst($acteur) . " a créé un " . strtolower(class_basename($recommandation));

            //LogActivity::addToLog("Enrégistrement", $message, get_class($recommandation), $recommandation->id);

            DB::commit();

            return response()->json(['statut' => 'success', 'message' => "Enregistrement réussir", 'data' => new RecommandationsResource($recommandation), 'statutCode' => Response::HTTP_CREATED], Response::HTTP_CREATED);

        } catch (\Throwable $th) {

            DB::rollBack();

            //throw $th;
            return response()->json(['statut' => 'error', 'message' => $th->getMessage(), 'errors' => [], 'statutCode' => Response::HTTP_INTERNAL_SERVER_ERROR], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    public function update($recommandation, array $attributs) : JsonResponse
    {
        DB::beginTransaction();

        try {

            if(!is_object($recommandation) && !($recommandation = $this->repository->findById($recommandation))) throw new Exception("Ce fond n'existe pas", 500);

            if(isset($attributs['evaluationId'])){
                if(!($evaluation = app(EvaluationDeGouvernanceRepository::class)->findById($attributs['evaluationId']))){
                    throw new Exception("Cette evaluation n'existe pas", 500);
                }
            }

            $this->repository->update($recommandation->id, $attributs);

            $recommandation->refresh();

            $acteur = Auth::check() ? Auth::user()->nom . " ". Auth::user()->prenom : "Inconnu";

            $message = $message ?? Str::ucfirst($acteur) . " a modifié un " . strtolower(class_basename($recommandation));

            //LogActivity::addToLog("Modification", $message, get_class($recommandation), $recommandation->id);

            DB::commit();

            return response()->json(['statut' => 'success', 'message' => "Enregistrement réussir", 'data' => new RecommandationsResource($recommandation), 'statutCode' => Response::HTTP_CREATED], Response::HTTP_CREATED);

        } catch (\Throwable $th) {

            DB::rollBack();

            //throw $th;
            return response()->json(['statut' => 'error', 'message' => $th->getMessage(), 'errors' => [], 'statutCode' => Response::HTTP_INTERNAL_SERVER_ERROR], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

}