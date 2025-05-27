<?php

namespace App\Services\enquetes_de_gouvernance;

use App\Http\Resources\gouvernance\IndicateursDeGouvernanceResource;
use App\Traits\Helpers\LogActivity;
use App\Repositories\enquetes_de_gouvernance\QuestionDeGouvernanceRepository;
use App\Repositories\PrincipeDeGouvernanceRepository;
use Core\Services\Contracts\BaseService;
use Core\Services\Interfaces\IndicateurDeGouvernanceServiceInterface;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

/**
* Interface IndicateurDeGouvernanceServiceInterface
* @package Core\Services\Interfaces
*/
class QuestionOperationnelleService extends BaseService implements IndicateurDeGouvernanceServiceInterface
{
    /**
     * @var service
     */
    protected $repository;

    /**
     * QuestionDeGouvernanceRepository constructor.
     *
     * @param QuestionDeGouvernanceRepository $questionOperationnelleRepository
     */
    public function __construct(QuestionDeGouvernanceRepository $questionOperationnelleRepository)
    {
        parent::__construct($questionOperationnelleRepository);
    }

    public function all(array $columns = ['*'], array $relations = []): JsonResponse
    {
        try
        {

            $indicateurs_de_gouvernance = collect([]);

            if(!(Auth::user()->hasRole('administrateur') || auth()->user()->profilable_type == "App\\Models\\Administrateur")){
                $indicateurs_de_gouvernance = Auth::user()->programme->questions_operationnelle;
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

    public function findById($questionOperationnelleId, array $columns = ['*'], array $relations = [], array $appends = []): JsonResponse
    {
        try
        {
            if(!is_object($questionOperationnelleId) && !($questionOperationnelleId = $this->repository->findById($questionOperationnelleId))) throw new Exception("Question Operationnelle introuvable", Response::HTTP_NOT_FOUND);

            return response()->json(['statut' => 'success', 'message' => null, 'data' => new IndicateursDeGouvernanceResource($questionOperationnelleId), 'statutCode' => Response::HTTP_OK], Response::HTTP_OK);
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

            $questionOperationnelle = $this->repository->create($attributs);


            DB::commit();

            $acteur = Auth::check() ? Auth::user()->nom . " ". Auth::user()->prenom : "Inconnu";

            $message = Str::ucfirst($acteur) . " a créé la question operationnelle {$questionOperationnelle->nom}.";

            //LogActivity::addToLog("Enrégistrement", $message, get_class($questionOperationnelle), $questionOperationnelle->id);

            return response()->json(['statut' => 'success', 'message' => "Création question operationnelle réussir", 'data' => new IndicateursDeGouvernanceResource($questionOperationnelle), 'statutCode' => Response::HTTP_OK], Response::HTTP_OK);

        } catch (\Throwable $th) {

            DB::rollBack();

            //throw $th;
            return response()->json(['statut' => 'error', 'message' => $th->getMessage(), 'errors' => [], 'statutCode' => Response::HTTP_INTERNAL_SERVER_ERROR], Response::HTTP_INTERNAL_SERVER_ERROR);
        }

    }

    public function update($questionOperationnelleId, array $attributs) : JsonResponse
    {
        DB::beginTransaction();

        try {

            if(is_string($questionOperationnelleId))
            {
                $questionOperationnelle = $this->repository->findById($questionOperationnelleId);
            }
            else{
                $questionOperationnelle = $questionOperationnelleId;
            }

            unset($attributs["can_have_multiple_reponse"]);

            $questionOperationnelle->fill($attributs)->save();

            $questionOperationnelle->refresh();

            DB::commit();

            $acteur = Auth::check() ? Auth::user()->nom . " ". Auth::user()->prenom : "Inconnu";

            $message = Str::ucfirst($acteur) . " a modifié la question operationnelle {$questionOperationnelle->nom}.";

            //LogActivity::addToLog("Modification", $message, get_class($questionOperationnelle), $questionOperationnelle->id);

            return response()->json(['statut' => 'success', 'message' => "Question operationnelle modifié", 'data' => new IndicateursDeGouvernanceResource($questionOperationnelle), 'statutCode' => Response::HTTP_OK], Response::HTTP_OK);

        } catch (\Throwable $th) {

            DB::rollBack();

            //throw $th;
            return response()->json(['statut' => 'error', 'message' => $th->getMessage(), 'errors' => [], 'statutCode' => Response::HTTP_INTERNAL_SERVER_ERROR], Response::HTTP_INTERNAL_SERVER_ERROR);
        }

    }
}