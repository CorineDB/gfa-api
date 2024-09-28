<?php

namespace App\Services;

use App\Http\Resources\gouvernance\PrincipesDeGouvernanceResource;
use App\Repositories\TypeDeGouvernanceRepository;
use Core\Services\Contracts\BaseService;
use Core\Services\Interfaces\TypeDeGouvernanceServiceInterface;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Response;

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

    /**
     * Liste des principes de gouvernance
     * 
     * return JsonResponse
     */
    public function principes($typeDeGouvernanceId, array $attributs = ['*'], array $relations = []): JsonResponse
    {
        try {
            if (!($typeDeGouvernance = $this->repository->findById($typeDeGouvernanceId)))
                throw new Exception("Ce type de gouvernance n'existe pas", 500);

            return response()->json(['statut' => 'success', 'message' => null, 'data' => PrincipesDeGouvernanceResource::collection($typeDeGouvernance->principes_de_gouvernance), 'statutCode' => Response::HTTP_OK], Response::HTTP_OK);
        } catch (\Throwable $th) {
            return response()->json(['statut' => 'error', 'message' => $th->getMessage(), 'errors' => []], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

}