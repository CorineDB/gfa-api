<?php

namespace App\Services;

use App\Http\Resources\gouvernance\IndicateursDeGouvernanceResource;
use App\Repositories\CritereDeGouvernanceRepository;
use Core\Services\Contracts\BaseService;
use Core\Services\Interfaces\CritereDeGouvernanceServiceInterface;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Response;

/**
* Interface CritereDeGouvernanceServiceInterface
* @package Core\Services\Interfaces
*/
class CritereDeGouvernanceService extends BaseService implements CritereDeGouvernanceServiceInterface
{

    /**
     * @var service
     */
    protected $repository;

    /**
     * CritereDeGouvernanceRepository constructor.
     *
     * @param CritereDeGouvernanceRepository $critereDeGouvernanceRepository
     */
    public function __construct(CritereDeGouvernanceRepository $critereDeGouvernanceRepository)
    {
        parent::__construct($critereDeGouvernanceRepository);
    }


    /**
     * Liste des indicateurs de gouvernance d'un critere
     * 
     * return JsonResponse
     */
    public function indicateurs($critereDeGouvernanceId, array $attributs = ['*'], array $relations = []): JsonResponse
    {
        try {
            if (!($critereDeGouvernance = $this->repository->findById($critereDeGouvernanceId)))
                throw new Exception("Ce critere de gouvernance n'existe pas", 500);

            return response()->json(['statut' => 'success', 'message' => null, 'data' => IndicateursDeGouvernanceResource::collection($critereDeGouvernance->indicateurs_de_gouvernance), 'statutCode' => Response::HTTP_OK], Response::HTTP_OK);
        } catch (\Throwable $th) {
            return response()->json(['statut' => 'error', 'message' => $th->getMessage(), 'errors' => []], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
}