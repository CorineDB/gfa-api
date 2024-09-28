<?php

namespace App\Services;

use App\Http\Resources\gouvernance\CriteresDeGouvernanceResource;
use App\Http\Resources\gouvernance\FormulaireDePerceptionResource;
use App\Http\Resources\gouvernance\FormulaireFactuelResource;
use App\Http\Resources\gouvernance\IndicateursDeGouvernanceResource;
use App\Repositories\PrincipeDeGouvernanceRepository;
use App\Repositories\ProgrammeRepository;
use Core\Services\Contracts\BaseService;
use Core\Services\Interfaces\PrincipeDeGouvernanceServiceInterface;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Response;

/**
* Interface PrincipeDeGouvernanceServiceInterface
* @package Core\Services\Interfaces
*/
class PrincipeDeGouvernanceService extends BaseService implements PrincipeDeGouvernanceServiceInterface
{

    /**
     * @var service
     */
    protected $repository;

    /**
     * PrincipeDeGouvernanceRepository constructor.
     *
     * @param PrincipeDeGouvernanceRepository $principeDeGouvernanceRepository
     */
    public function __construct(PrincipeDeGouvernanceRepository $principeDeGouvernanceRepository)
    {
        parent::__construct($principeDeGouvernanceRepository);
    }

    /**
     * Liste des criteres de gouvernance
     * 
     * return JsonResponse
     */
    public function criteres($principeDeGouvernanceId, array $attributs = ['*'], array $relations = []): JsonResponse
    {
        try {
            if (!($principeDeGouvernance = $this->repository->findById($principeDeGouvernanceId)))
                throw new Exception("Ce principe de gouvernance n'existe pas", 500);

            return response()->json(['statut' => 'success', 'message' => null, 'data' => CriteresDeGouvernanceResource::collection($principeDeGouvernance->criteres_de_gouvernance), 'statutCode' => Response::HTTP_OK], Response::HTTP_OK);
        } catch (\Throwable $th) {
            return response()->json(['statut' => 'error', 'message' => $th->getMessage(), 'errors' => []], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * Liste des indicateurs de gouvernance
     * 
     * return JsonResponse
     */
    public function indicateurs($principeDeGouvernanceId, array $attributs = ['*'], array $relations = []): JsonResponse
    {
        try {
            if (!($principeDeGouvernance = $this->repository->findById($principeDeGouvernanceId)))
                throw new Exception("Ce principe de gouvernance n'existe pas", 500);

            return response()->json(['statut' => 'success', 'message' => null, 'data' => IndicateursDeGouvernanceResource::collection($principeDeGouvernance->indicateurs_de_gouvernance), 'statutCode' => Response::HTTP_OK], Response::HTTP_OK);
        } catch (\Throwable $th) {
            return response()->json(['statut' => 'error', 'message' => $th->getMessage(), 'errors' => []], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * Charger le formulaire de l'outil factuel
     * 
     */
    public function formulaire_factuel($programmeId, array $attributs = ['*'], array $relations = []): JsonResponse
    {
        try {
            if (!($programme = app(ProgrammeRepository::class)->findById($programmeId)))
                throw new Exception("Ce programme n'existe pas", 500);

            return response()->json(['statut' => 'success', 'message' => null, 'data' => FormulaireFactuelResource::collection($programme->types_de_gouvernance), 'statutCode' => Response::HTTP_OK], Response::HTTP_OK);
        } catch (\Throwable $th) {
            return response()->json(['statut' => 'error', 'message' => $th->getMessage(), 'errors' => []], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * Charger le formulaire de l'outil de perception
     * 
     */
    public function formulaire_de_perception($programmeId, array $attributs = ['*'], array $relations = []): JsonResponse
    {
        try {
            if (!($programme = app(ProgrammeRepository::class)->findById($programmeId)))
                throw new Exception("Ce programme n'existe pas", 500);

            return response()->json(['statut' => 'success', 'message' => null, 'data' => FormulaireDePerceptionResource::collection($programme->principes_de_gouvernance), 'statutCode' => Response::HTTP_OK], Response::HTTP_OK);
        } catch (\Throwable $th) {
            return response()->json(['statut' => 'error', 'message' => $th->getMessage(), 'errors' => []], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }


}