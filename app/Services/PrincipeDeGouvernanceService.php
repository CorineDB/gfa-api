<?php

namespace App\Services;

use App\Http\Resources\gouvernance\CritereDeGouvernanceResource;
use App\Http\Resources\gouvernance\FormulaireDePerceptionResource;
use App\Http\Resources\gouvernance\FormulaireFactuelResource;
use App\Http\Resources\gouvernance\IndicateursDeGouvernanceResource;
use App\Http\Resources\gouvernance\PrincipesDeGouvernanceResource;
use App\Repositories\EnqueteDeCollecteRepository;
use App\Repositories\OrganisationRepository;
use App\Repositories\PrincipeDeGouvernanceRepository;
use App\Repositories\ProgrammeRepository;
use Core\Services\Contracts\BaseService;
use Core\Services\Interfaces\PrincipeDeGouvernanceServiceInterface;
use Exception;
use App\Traits\Helpers\LogActivity;
use Illuminate\Support\Str;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

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

    public function all(array $columns = ['*'], array $relations = []): JsonResponse
    {
        try
        {
            $principes_de_gouvernance = collect([]);
            
            if(!(Auth::user()->hasRole('administrateur') || auth()->user()->profilable_type == "App\\Models\\Administrateur")){
                $principes_de_gouvernance = Auth::user()->programme->principes_de_gouvernance;
            }

            return response()->json(['statut' => 'success', 'message' => null, 'data' => PrincipesDeGouvernanceResource::collection($principes_de_gouvernance), 'statutCode' => Response::HTTP_OK], Response::HTTP_OK);
        }

        catch (\Throwable $th)
        {
            return response()->json(['statut' => 'error', 'message' => $th->getMessage(), 'errors' => []], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    public function findById($principeDeGouvernance, array $columns = ['*'], array $relations = [], array $appends = []): JsonResponse
    {
        try
        {
            if(!is_object($principeDeGouvernance) && !($principeDeGouvernance = $this->repository->findById($principeDeGouvernance))) throw new Exception("Ce principe de gouvernance n'existe pas.", Response::HTTP_NOT_FOUND);

            return response()->json(['statut' => 'success', 'message' => null, 'data' => new PrincipesDeGouvernanceResource($principeDeGouvernance), 'statutCode' => Response::HTTP_OK], Response::HTTP_OK);
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

            $principeDeGouvernance = $this->repository->create($attributs);

            $acteur = Auth::check() ? Auth::user()->nom . " ". Auth::user()->prenom : "Inconnu";

            $message = $message ?? Str::ucfirst($acteur) . " a créé un " . strtolower(class_basename($principeDeGouvernance));

            //LogActivity::addToLog("Enrégistrement", $message, get_class($principeDeGouvernance), $principeDeGouvernance->id);

            DB::commit();

            return response()->json(['statut' => 'success', 'message' => "Enregistrement réussir", 'data' => new PrincipesDeGouvernanceResource($principeDeGouvernance), 'statutCode' => Response::HTTP_CREATED], Response::HTTP_CREATED);

        } catch (\Throwable $th) {

            DB::rollBack();

            //throw $th;
            return response()->json(['statut' => 'error', 'message' => $th->getMessage(), 'errors' => [], 'statutCode' => Response::HTTP_INTERNAL_SERVER_ERROR], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    public function update($principeDeGouvernance, array $attributs) : JsonResponse
    {
        DB::beginTransaction();

        try {

            if(!is_object($principeDeGouvernance) && !($principeDeGouvernance = $this->repository->findById($principeDeGouvernance))) throw new Exception("Ce principe de gouvernance n'existe pas", Response::HTTP_NOT_FOUND);

            $this->repository->update($principeDeGouvernance->id, $attributs);

            $principeDeGouvernance->refresh();

            $acteur = Auth::check() ? Auth::user()->nom . " ". Auth::user()->prenom : "Inconnu";

            $message = $message ?? Str::ucfirst($acteur) . " a modifié un " . strtolower(class_basename($principeDeGouvernance));

            //LogActivity::addToLog("Modification", $message, get_class($principeDeGouvernance), $principeDeGouvernance->id);

            DB::commit();

            return response()->json(['statut' => 'success', 'message' => "Enregistrement réussir", 'data' => new PrincipesDeGouvernanceResource($principeDeGouvernance), 'statutCode' => Response::HTTP_CREATED], Response::HTTP_CREATED);

        } catch (\Throwable $th) {

            DB::rollBack();

            //throw $th;
            return response()->json(['statut' => 'error', 'message' => $th->getMessage(), 'errors' => [], 'statutCode' => Response::HTTP_INTERNAL_SERVER_ERROR], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
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
                throw new Exception("Ce principe de gouvernance n'existe pas", Response::HTTP_NOT_FOUND);

            return response()->json(['statut' => 'success', 'message' => null, 'data' => CritereDeGouvernanceResource::collection($principeDeGouvernance->criteres_de_gouvernance), 'statutCode' => Response::HTTP_OK], Response::HTTP_OK);
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
                throw new Exception("Ce principe de gouvernance n'existe pas", Response::HTTP_NOT_FOUND);

            return response()->json(['statut' => 'success', 'message' => null, 'data' => IndicateursDeGouvernanceResource::collection($principeDeGouvernance->indicateurs_de_gouvernance), 'statutCode' => Response::HTTP_OK], Response::HTTP_OK);
        } catch (\Throwable $th) {
            return response()->json(['statut' => 'error', 'message' => $th->getMessage(), 'errors' => []], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * Charger le formulaire de l'outil de perception du programme associé à l'utilisateur connecté
     * @param array $attributs Liste des attributs à récupérer
     * @param array $relations Liste des relations à charger
     * @return JsonResponse
     */
    public function formulaire_factuel($enqueteId = null, $organisationId = null): JsonResponse
    {
        try {

            $programme = Auth::user()->programme;

            if($enqueteId != null && $enqueteId != 'null'){
                if (!($enqueteId = app(EnqueteDeCollecteRepository::class)->findById($enqueteId)))
                    throw new Exception("Cette enquete n'existe pas", Response::HTTP_NOT_FOUND);
                $enqueteId=$enqueteId->id;
            }

            if($organisationId != null && $organisationId != 'null'){
                if (!($organisationId = app(OrganisationRepository::class)->findById($organisationId)))
                    throw new Exception("Cette organisation n'existe pas", Response::HTTP_NOT_FOUND);

                $organisationId=$organisationId->id;
            }
            return response()->json(['statut' => 'success', 'message' => null, 'data' => $programme->types_de_gouvernance->map(function($item) use ($enqueteId, $organisationId) {
                return new FormulaireFactuelResource($item, $enqueteId, $organisationId);
            }), 'statutCode' => Response::HTTP_OK], Response::HTTP_OK);
        } catch (\Throwable $th) {
            return response()->json(['statut' => 'error', 'message' => $th->getMessage(), 'errors' => []], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * Charger le formulaire de l'outil de perception du programme associé à l'utilisateur connecté
     * 
     * @param array $attributs Liste des attributs à récupérer
     * @param array $relations Liste des relations à charger
     * @return JsonResponse
     */
    public function formulaire_de_perception($enqueteId = null, $organisationId = null): JsonResponse
    {
        try {
            // Vérifier si le programme existe
            /*if (!($programme = app(ProgrammeRepository::class)->findById($programmeId)))
                throw new Exception("Ce programme n'existe pas", Response::HTTP_NOT_FOUND);*/

            $programme = Auth::user()->programme;

            if($enqueteId != null && $enqueteId != 'null'){
                if (!($enqueteId = app(EnqueteDeCollecteRepository::class)->findById($enqueteId)))
                    throw new Exception("Cette enquete n'existe pas", Response::HTTP_NOT_FOUND);
                $enqueteId=$enqueteId->id;
            }

            if($organisationId != null && $organisationId != 'null'){
                if (!($organisationId = app(OrganisationRepository::class)->findById($organisationId)))
                    throw new Exception("Cette organisation n'existe pas", Response::HTTP_NOT_FOUND);

                $organisationId=$organisationId->id;
            }
            
            return response()->json(['statut' => 'success', 'message' => null, 'data' => $programme->principes_de_gouvernance->map(function($principeDeGouvernance) use ($enqueteId, $organisationId) {
                return new FormulaireDePerceptionResource($principeDeGouvernance, $enqueteId, $organisationId);
            }), 'statutCode' => Response::HTTP_OK], Response::HTTP_OK);
            
            // Retourner le formulaire de perception
            return response()->json(
                [
                    'statut' => 'success',
                    'message' => null,
                    'data' => FormulaireDePerceptionResource::collection(
                        $programme->principes_de_gouvernance // Les principes de gouvernance
                    ),
                    'statutCode' => Response::HTTP_OK
                ],
                Response::HTTP_OK
            );
        } catch (\Throwable $th) {
            // Gestion des erreurs
            return response()->json(
                [
                    'statut' => 'error',
                    'message' => $th->getMessage(),
                    'errors' => []
                ],
                Response::HTTP_INTERNAL_SERVER_ERROR
            );
        }
    }


}