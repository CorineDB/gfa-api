<?php

namespace App\Services\enquetes_de_gouvernance;

use App\Http\Resources\gouvernance\IndicateursDeGouvernanceResource;
use App\Traits\Helpers\LogActivity;
use App\Repositories\enquetes_de_gouvernance\IndicateurDeGouvernanceFactuelRepository as IndicateurDeGouvernanceRepository;
use Core\Services\Contracts\BaseService;
use Core\Services\Interfaces\enquetes_de_gouvernance\IndicateurDeGouvernanceFactuelServiceInterface;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

/**
* Interface IndicateurDeGouvernanceFactuelServiceInterface
* @package Core\Services\Interfaces
*/
class IndicateurDeGouvernanceFactuelService extends BaseService implements IndicateurDeGouvernanceFactuelServiceInterface
{
    /**
     * @var service
     */
    protected $repository;

    /**
     * IndicateurDeGouvernanceRepository constructor.
     *
     * @param IndicateurDeGouvernanceRepository $indicateurDeGouvernanceRepository
     */
    public function __construct(IndicateurDeGouvernanceRepository $indicateurDeGouvernanceRepository)
    {
        parent::__construct($indicateurDeGouvernanceRepository);
    }

    public function all(array $columns = ['*'], array $relations = []): JsonResponse
    {
        try
        {

            $indicateurs_de_gouvernance = collect([]);

            if(!(Auth::user()->hasRole('administrateur') || auth()->user()->profilable_type == "App\\Models\\Administrateur")){
                $indicateurs_de_gouvernance = Auth::user()->programme->indicateurs_de_gouvernance_factuel;
            }

            return response()->json(['statut' => 'success', 'message' => null, 'data' => IndicateursDeGouvernanceResource::collection($indicateurs_de_gouvernance), 'statutCode' => Response::HTTP_OK], Response::HTTP_OK);
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
            return response()->json(['statut' => 'success', 'message' => null, 'data' => IndicateursDeGouvernanceResource::collection($this->repository->filterBy($filtres, $columns, $relations)), 'statutCode' => Response::HTTP_OK], Response::HTTP_OK);
        }

        catch (\Throwable $th)
        {
            return response()->json(['statut' => 'error', 'message' => $th->getMessage(), 'errors' => []], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    public function findById($indicateurId, array $columns = ['*'], array $relations = [], array $appends = []): JsonResponse
    {
        try
        {
            if(!is_object($indicateurId) && !($indicateurId = $this->repository->findById($indicateurId))) throw new Exception("Indicateur introuvable", Response::HTTP_NOT_FOUND);

            return response()->json(['statut' => 'success', 'message' => null, 'data' => new IndicateursDeGouvernanceResource($indicateurId), 'statutCode' => Response::HTTP_OK], Response::HTTP_OK);
        }

        catch (\Throwable $th)
        {
            return response()->json(['statut' => 'error', 'message' => $th->getMessage(), 'errors' => []], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    public function create(array $attributs, $message = null) : JsonResponse
    {
        DB::beginTransaction();

        try {

            $programme = Auth::user()->programme;

            $attributs = array_merge($attributs, ['programmeId' => $programme->id]);

            unset($attributs["can_have_multiple_reponse"]);

            $indicateur = $this->repository->create($attributs);

            DB::commit();

            $acteur = Auth::check() ? Auth::user()->nom . " ". Auth::user()->prenom : "Inconnu";

            $message = Str::ucfirst($acteur) . " a créé l'indicateur de gouvernance {$indicateur->nom}.";

            //LogActivity::addToLog("Enrégistrement", $message, get_class($indicateur), $indicateur->id);

            return response()->json(['statut' => 'success', 'message' => "Création du mod réussir", 'data' => new IndicateursDeGouvernanceResource($indicateur), 'statutCode' => Response::HTTP_OK], Response::HTTP_OK);

        } catch (\Throwable $th) {

            DB::rollBack();

            //throw $th;
            return response()->json(['statut' => 'error', 'message' => $th->getMessage(), 'errors' => [], 'statutCode' => Response::HTTP_INTERNAL_SERVER_ERROR], Response::HTTP_INTERNAL_SERVER_ERROR);
        }

    }

    public function update($indicateurId, array $attributs) : JsonResponse
    {
        DB::beginTransaction();

        try {

            if(is_string($indicateurId))
            {
                $indicateur = $this->repository->findById($indicateurId);
            }
            else{
                $indicateur = $indicateurId;
            }

            unset($attributs["can_have_multiple_reponse"]);

            $indicateur->fill($attributs)->save();

            $indicateur->refresh();

            DB::commit();

            $acteur = Auth::check() ? Auth::user()->nom . " ". Auth::user()->prenom : "Inconnu";

            $message = Str::ucfirst($acteur) . " a modifié l'indicateur de gouvernance {$indicateur->nom}.";

            //LogActivity::addToLog("Modification", $message, get_class($indicateur), $indicateur->id);

            return response()->json(['statut' => 'success', 'message' => "Indicateur modifié", 'data' => new IndicateursDeGouvernanceResource($indicateur), 'statutCode' => Response::HTTP_OK], Response::HTTP_OK);

        } catch (\Throwable $th) {

            DB::rollBack();

            //throw $th;
            return response()->json(['statut' => 'error', 'message' => $th->getMessage(), 'errors' => [], 'statutCode' => Response::HTTP_INTERNAL_SERVER_ERROR], Response::HTTP_INTERNAL_SERVER_ERROR);
        }

    }
}