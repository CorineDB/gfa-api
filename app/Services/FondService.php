<?php

namespace App\Services;

use App\Http\Resources\FondsResource;
use App\Models\UniteeDeGestion;
use App\Repositories\FondRepository;
use Core\Services\Contracts\BaseService;
use Core\Services\Interfaces\FondServiceInterface;
use Exception;
use App\Traits\Helpers\LogActivity;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Response;

/**
* Interface FondServiceInterface
* @package Core\Services\Interfaces
*/
class FondService extends BaseService implements FondServiceInterface
{

    /**
     * @var service
     */
    protected $repository;

    /**
     * FondRepository constructor.
     *
     * @param FondRepository $fondRepository
     */
    public function __construct(FondRepository $fondRepository)
    {
        parent::__construct($fondRepository);
    }

    public function all(array $columns = ['*'], array $relations = []): JsonResponse
    {
        try
        {
            $fonds = [];
            if(Auth::user()->hasRole('unitee-de-gestion') || ( get_class(auth()->user()->profilable) == UniteeDeGestion::class)){

                $fonds = Auth::user()->programme->fonds;
            }

            return response()->json(['statut' => 'success', 'message' => null, 'data' => FondsResource::collection($fonds), 'statutCode' => Response::HTTP_OK], Response::HTTP_OK);
        }

        catch (\Throwable $th)
        {
            return response()->json(['statut' => 'error', 'message' => $th->getMessage(), 'errors' => []], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    public function findById($fond, array $columns = ['*'], array $relations = [], array $appends = []): JsonResponse
    {
        try
        {
            if(!is_object($fond) && !($fond = $this->repository->findById($fond))) throw new Exception("Fond inconnue.", 500);

            return response()->json(['statut' => 'success', 'message' => null, 'data' => new FondsResource($fond), 'statutCode' => Response::HTTP_OK], Response::HTTP_OK);
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
            
            $fond = $this->repository->create($attributs);

            $acteur = Auth::check() ? Auth::user()->nom . " ". Auth::user()->prenom : "Inconnu";

            $message = $message ?? Str::ucfirst($acteur) . " a créé un " . strtolower(class_basename($fond));

            //LogActivity::addToLog("Enrégistrement", $message, get_class($fond), $fond->id);

            DB::commit();

            return response()->json(['statut' => 'success', 'message' => "Enregistrement réussir", 'data' => new FondsResource($fond), 'statutCode' => Response::HTTP_CREATED], Response::HTTP_CREATED);

        } catch (\Throwable $th) {

            DB::rollBack();

            //throw $th;
            return response()->json(['statut' => 'error', 'message' => $th->getMessage(), 'errors' => [], 'statutCode' => Response::HTTP_INTERNAL_SERVER_ERROR], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    public function update($fond, array $attributs) : JsonResponse
    {
        DB::beginTransaction();

        try {

            if(!is_object($fond) && !($fond = $this->repository->findById($fond))) throw new Exception("Ce fond n'existe pas", 500);

            $this->repository->update($fond->id, $attributs);

            $fond->refresh();

            $acteur = Auth::check() ? Auth::user()->nom . " ". Auth::user()->prenom : "Inconnu";

            $message = $message ?? Str::ucfirst($acteur) . " a modifié un " . strtolower(class_basename($fond));

            //LogActivity::addToLog("Modification", $message, get_class($fond), $fond->id);

            DB::commit();

            return response()->json(['statut' => 'success', 'message' => "Enregistrement réussir", 'data' => new FondsResource($fond), 'statutCode' => Response::HTTP_CREATED], Response::HTTP_CREATED);

        } catch (\Throwable $th) {

            DB::rollBack();

            //throw $th;
            return response()->json(['statut' => 'error', 'message' => $th->getMessage(), 'errors' => [], 'statutCode' => Response::HTTP_INTERNAL_SERVER_ERROR], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

}