<?php

namespace App\Services;

use App\Http\Resources\gouvernance\PrincipeDeGouvernanceResource;
use App\Http\Resources\gouvernance\TypesDeGouvernanceResource;
use App\Repositories\TypeDeGouvernanceRepository;
use Core\Services\Contracts\BaseService;
use Core\Services\Interfaces\TypeDeGouvernanceServiceInterface;
use Exception;
use App\Traits\Helpers\LogActivity;
use Illuminate\Support\Str;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

/**
* Interface TypeDeGouvernanceServiceInterface
* @package Core\Services\Interfaces
*/
class TypeDeGouvernanceService extends BaseService implements TypeDeGouvernanceServiceInterface
{

    /**
     * @var service
     */
    protected $repository;

    /**
     * TypeDeGouvernanceRepository constructor.
     *
     * @param TypeDeGouvernanceRepository $typeDeGouvernanceRepository
     */
    public function __construct(TypeDeGouvernanceRepository $typeDeGouvernanceRepository)
    {
        parent::__construct($typeDeGouvernanceRepository);
    }

    public function all(array $columns = ['*'], array $relations = []): JsonResponse
    {
        try
        {
            $types_de_gouvernance = collect([]);
            
            if(!(Auth::user()->hasRole('administrateur') || auth()->user()->profilable_type == "App\\Models\\Administrateur")){
                $types_de_gouvernance = Auth::user()->programme->types_de_gouvernance;
            }

            return response()->json(['statut' => 'success', 'message' => null, 'data' => TypesDeGouvernanceResource::collection($types_de_gouvernance), 'statutCode' => Response::HTTP_OK], Response::HTTP_OK);
        }

        catch (\Throwable $th)
        {
            return response()->json(['statut' => 'error', 'message' => $th->getMessage(), 'errors' => []], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    public function findById($typeDeGouvernance, array $columns = ['*'], array $relations = [], array $appends = []): JsonResponse
    {
        try
        {
            if(!is_object($typeDeGouvernance) && !($typeDeGouvernance = $this->repository->findById($typeDeGouvernance))) throw new Exception("Ce type de gouvernance n'existe pas.", Response::HTTP_NOT_FOUND);

            return response()->json(['statut' => 'success', 'message' => null, 'data' => new TypesDeGouvernanceResource($typeDeGouvernance), 'statutCode' => Response::HTTP_OK], Response::HTTP_OK);
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

            $attributs = array_merge($attributs, ['programmeId' => $programme->id]);

            $type = $this->repository->create($attributs);

            $acteur = Auth::check() ? Auth::user()->nom . " ". Auth::user()->prenom : "Inconnu";

            $message = $message ?? Str::ucfirst($acteur) . " a créé un " . strtolower(class_basename($type));

            //LogActivity::addToLog("Enrégistrement", $message, get_class($type), $type->id);

            DB::commit();

            return response()->json(['statut' => 'success', 'message' => "Enregistrement réussir", 'data' => new TypesDeGouvernanceResource($type), 'statutCode' => Response::HTTP_CREATED], Response::HTTP_CREATED);

        } catch (\Throwable $th) {

            DB::rollBack();

            //throw $th;
            return response()->json(['statut' => 'error', 'message' => $th->getMessage(), 'errors' => [], 'statutCode' => Response::HTTP_INTERNAL_SERVER_ERROR], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    public function update($typeDeGouvernance, array $attributs) : JsonResponse
    {
        DB::beginTransaction();

        try {
            
            if(!is_object($typeDeGouvernance) && !($typeDeGouvernance = $this->repository->findById($typeDeGouvernance))) throw new Exception("Ce type de gouvernance n'existe pas", Response::HTTP_NOT_FOUND);

            unset($attributs["programmeId"]);
            
            $this->repository->update($typeDeGouvernance->id, $attributs);

            $typeDeGouvernance->refresh();

            $acteur = Auth::check() ? Auth::user()->nom . " ". Auth::user()->prenom : "Inconnu";

            $message = $message ?? Str::ucfirst($acteur) . " a modifié un " . strtolower(class_basename($typeDeGouvernance));

            //LogActivity::addToLog("Modification", $message, get_class($typeDeGouvernance), $typeDeGouvernance->id);

            DB::commit();

            return response()->json(['statut' => 'success', 'message' => "Enregistrement réussir", 'data' => new TypesDeGouvernanceResource($typeDeGouvernance), 'statutCode' => Response::HTTP_CREATED], Response::HTTP_CREATED);

        } catch (\Throwable $th) {

            DB::rollBack();

            //throw $th;
            return response()->json(['statut' => 'error', 'message' => $th->getMessage(), 'errors' => [], 'statutCode' => Response::HTTP_INTERNAL_SERVER_ERROR], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * Liste des principes de gouvernance
     * 
     * return JsonResponse
     */
    public function principes($typeDeGouvernanceId, array $attributs = ['*'], array $relations = []): JsonResponse
    {
        try {
            if (!($typeDeGouvernance = $this->repository->findById($typeDeGouvernanceId)))
                throw new Exception("Ce type de gouvernance n'existe pas", Response::HTTP_NOT_FOUND);

            return response()->json(['statut' => 'success', 'message' => null, 'data' => PrincipeDeGouvernanceResource::collection($typeDeGouvernance->principes_de_gouvernance), 'statutCode' => Response::HTTP_OK], Response::HTTP_OK);
        } catch (\Throwable $th) {
            return response()->json(['statut' => 'error', 'message' => $th->getMessage(), 'errors' => []], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

}