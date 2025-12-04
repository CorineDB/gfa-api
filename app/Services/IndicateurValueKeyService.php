<?php

namespace App\Services;

use App\Http\Resources\indicateur\IndicateursValueKeyResource;
use App\Repositories\IndicateurValueKeyRepository;
use Core\Services\Contracts\BaseService;
use Core\Services\Interfaces\IndicateurValueKeyServiceInterface;
use Exception;
use App\Traits\Helpers\LogActivity;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Response;

/**
* Class IndicateurValueKeyService
* @package Core\Services
*/
class IndicateurValueKeyService extends BaseService implements IndicateurValueKeyServiceInterface
{

    /**
     * @var service
     */
    protected $repository;

    /**
     * IndicateurValueKeyRepository constructor.
     *
     * @param IndicateurValueKeyRepository $indicateurValueKeyRepository
     */
    public function __construct(IndicateurValueKeyRepository $indicateurValueKeyRepository)
    {
        parent::__construct($indicateurValueKeyRepository);
    }

    public function all(array $columns = ['*'], array $relations = []): JsonResponse
    {
        try
        {
            $indicateurs_values_keys = collect([]);

            if(!(Auth::user()->hasRole('administrateur') || auth()->user()->profilable_type == "App\\Models\\Administrateur")){
                //$projets = $this->repository->allFiltredBy([['attribut' => 'programmeId', 'operateur' => '=', 'valeur' => auth()->user()->programme->id]]);
                $indicateurs_values_keys = Auth::user()->programme->indicateurs_values_keys()->where('key', '!=', 'moy')->get();
            }

            return response()->json(['statut' => 'success', 'message' => null, 'data' => IndicateursValueKeyResource::collection($indicateurs_values_keys), 'statutCode' => Response::HTTP_OK], Response::HTTP_OK);
        }

        catch (\Throwable $th)
        {
            return response()->json(['statut' => 'error', 'message' => $th->getMessage(), 'errors' => []], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    public function findById($indicateurValueKey, array $columns = ['*'], array $relations = [], array $appends = []): JsonResponse
    {
        try
        {
            if(!is_object($indicateurValueKey) && !($indicateurValueKey = $this->repository->findById($indicateurValueKey))) throw new Exception("Cle d'indicateur introuvable.", Response::HTTP_NOT_FOUND);

            return response()->json(['statut' => 'success', 'message' => null, 'data' => new IndicateursValueKeyResource($indicateurValueKey), 'statutCode' => Response::HTTP_OK], Response::HTTP_OK);
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

            $indicateurValueKey = $this->repository->create(array_merge($attributs, ['programmeId' => Auth::user()->programme->id]));

            $acteur = Auth::check() ? Auth::user()->nom . " ". Auth::user()->prenom : "Inconnu";

            $message = $message ?? Str::ucfirst($acteur) . " a créé un " . strtolower(class_basename($indicateurValueKey));

            //LogActivity::addToLog("Enrégistrement", $message, get_class($indicateurValueKey), $indicateurValueKey->id);

            DB::commit();

            return response()->json(['statut' => 'success', 'message' => "Enregistrement réussir", 'data' => new IndicateursValueKeyResource($indicateurValueKey), 'statutCode' => Response::HTTP_CREATED], Response::HTTP_CREATED);

        } catch (\Throwable $th) {

            DB::rollBack();

            //throw $th;
            return response()->json(['statut' => 'error', 'message' => $th->getMessage(), 'errors' => [], 'statutCode' => Response::HTTP_INTERNAL_SERVER_ERROR], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    public function update($indicateurValueKey, array $attributs) : JsonResponse
    {
        DB::beginTransaction();

        try {

            if(!is_object($indicateurValueKey) && !($indicateurValueKey = $this->repository->findById($indicateurValueKey))) throw new Exception("Cle d'indicateur introuvable.", Response::HTTP_NOT_FOUND);

            $this->repository->update($indicateurValueKey->id, array_merge($attributs, ['programmeId' => Auth::user()->programme->id]));

            $indicateurValueKey->refresh();

            $acteur = Auth::check() ? Auth::user()->nom . " ". Auth::user()->prenom : "Inconnu";

            $message = $message ?? Str::ucfirst($acteur) . " a modifié un " . strtolower(class_basename($indicateurValueKey));

            //LogActivity::addToLog("Modification", $message, get_class($indicateurValueKey), $indicateurValueKey->id);

            DB::commit();

            return response()->json(['statut' => 'success', 'message' => "Enregistrement réussir", 'data' => new IndicateursValueKeyResource($indicateurValueKey), 'statutCode' => Response::HTTP_CREATED], Response::HTTP_CREATED);

        } catch (\Throwable $th) {

            DB::rollBack();

            //throw $th;
            return response()->json(['statut' => 'error', 'message' => $th->getMessage(), 'errors' => [], 'statutCode' => Response::HTTP_INTERNAL_SERVER_ERROR], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

}