<?php

namespace App\Services\enquetes_de_gouvernance;

use App\Http\Resources\gouvernance\CriteresDeGouvernanceResource;
use App\Http\Resources\gouvernance\IndicateursDeGouvernanceResource;
use App\Models\Organisation;
use App\Repositories\enquetes_de_gouvernance\CritereDeGouvernanceFactuelRepository;
use Core\Services\Contracts\BaseService;
use Core\Services\Interfaces\enquetes_de_gouvernance\CritereDeGouvernanceFactuelServiceInterface;
use Exception;
use App\Traits\Helpers\LogActivity;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Response;

/**
* Interface CritereDeGouvernanceFactuelServiceInterface
* @package Core\Services\Interfaces
*/
class CritereDeGouvernanceFactuelService extends BaseService implements CritereDeGouvernanceFactuelServiceInterface
{

    /**
     * @var service
     */
    protected $repository;

    /**
     * CritereDeGouvernanceFactuelRepository constructor.
     *
     * @param CritereDeGouvernanceFactuelRepository $critereDeGouvernanceRepository
     */
    public function __construct(CritereDeGouvernanceFactuelRepository $critereDeGouvernanceRepository)
    {
        parent::__construct($critereDeGouvernanceRepository);
    }

    public function all(array $columns = ['*'], array $relations = []): JsonResponse
    {
        try
        {
            $criteres_de_gouvernance = collect([]);

            if (Auth::user()->hasRole('administrateur') || auth()->user()->profilable_type == "App\\Models\\Administrateur") {
                $criteres_de_gouvernance = $this->repository->all();
            } else {
                $criteres_de_gouvernance = Auth::user()->programme->criteres_de_gouvernance_factuel;
            }

            return response()->json(['statut' => 'success', 'message' => null, 'data' => CriteresDeGouvernanceResource::collection($criteres_de_gouvernance), 'statutCode' => Response::HTTP_OK], Response::HTTP_OK);
        }

        catch (\Throwable $th)
        {
            return response()->json(['statut' => 'error', 'message' => $th->getMessage(), 'errors' => []], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    public function findById($critereDeGouvernance, array $columns = ['*'], array $relations = [], array $appends = []): JsonResponse
    {
        try
        {
            if(!is_object($critereDeGouvernance) && !($critereDeGouvernance = $this->repository->findById($critereDeGouvernance))) throw new Exception("Ce critere de gouvernance n'existe pas", Response::HTTP_NOT_FOUND);

            return response()->json(['statut' => 'success', 'message' => null, 'data' => new CriteresDeGouvernanceResource($critereDeGouvernance), 'statutCode' => Response::HTTP_OK], Response::HTTP_OK);
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

            $critereDeGouvernance = $this->repository->create($attributs);

            $acteur = Auth::check() ? Auth::user()->nom . " ". Auth::user()->prenom : "Inconnu";

            $message = $message ?? Str::ucfirst($acteur) . " a créé un " . strtolower(class_basename($critereDeGouvernance));

            //LogActivity::addToLog("Enrégistrement", $message, get_class($critereDeGouvernance), $critereDeGouvernance->id);

            DB::commit();

            return response()->json(['statut' => 'success', 'message' => "Enregistrement réussir", 'data' => new CriteresDeGouvernanceResource($critereDeGouvernance), 'statutCode' => Response::HTTP_CREATED], Response::HTTP_CREATED);

        } catch (\Throwable $th) {

            DB::rollBack();

            //throw $th;
            return response()->json(['statut' => 'error', 'message' => $th->getMessage(), 'errors' => [], 'statutCode' => Response::HTTP_INTERNAL_SERVER_ERROR], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    public function update($critereDeGouvernance, array $attributs) : JsonResponse
    {
        DB::beginTransaction();

        try {

            if(!is_object($critereDeGouvernance) && !($critereDeGouvernance = $this->repository->findById($critereDeGouvernance))) throw new Exception("Ce critere de gouvernance n'existe pas", Response::HTTP_NOT_FOUND);

            $this->repository->update($critereDeGouvernance->id, $attributs);

            $critereDeGouvernance->refresh();

            $acteur = Auth::check() ? Auth::user()->nom . " ". Auth::user()->prenom : "Inconnu";

            $message = $message ?? Str::ucfirst($acteur) . " a modifié un " . strtolower(class_basename($critereDeGouvernance));

            //LogActivity::addToLog("Modification", $message, get_class($critereDeGouvernance), $critereDeGouvernance->id);

            DB::commit();

            return response()->json(['statut' => 'success', 'message' => "Enregistrement réussir", 'data' => new CriteresDeGouvernanceResource($critereDeGouvernance), 'statutCode' => Response::HTTP_CREATED], Response::HTTP_CREATED);

        } catch (\Throwable $th) {

            DB::rollBack();

            //throw $th;
            return response()->json(['statut' => 'error', 'message' => $th->getMessage(), 'errors' => [], 'statutCode' => Response::HTTP_INTERNAL_SERVER_ERROR], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
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
                throw new Exception("Ce critere de gouvernance n'existe pas", Response::HTTP_NOT_FOUND);

            return response()->json(['statut' => 'success', 'message' => null, 'data' => IndicateursDeGouvernanceResource::collection($critereDeGouvernance->indicateurs_de_gouvernance), 'statutCode' => Response::HTTP_OK], Response::HTTP_OK);
        } catch (\Throwable $th) {
            return response()->json(['statut' => 'error', 'message' => $th->getMessage(), 'errors' => []], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
}