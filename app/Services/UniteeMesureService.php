<?php

namespace App\Services;

use App\Http\Resources\UniteeMesureResource;
use App\Repositories\UniteeMesureRepository;
use App\Traits\Helpers\LogActivity;
use Core\Services\Contracts\BaseService;
use Core\Services\Interfaces\UniteeMesureServiceInterface;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Response;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

/**
* Interface UniteeMesureServiceInterface
* @package Core\Services\Interfaces
*/
class UniteeMesureService extends BaseService implements UniteeMesureServiceInterface
{

    /**
     * @var service
     */
    protected $repository;

    /**
     * UniteeMesureRepository constructor.
     *
     * @param UniteeMesureRepository $uniteeRepository
     */
    public function __construct(UniteeMesureRepository $uniteeRepository)
    {
        parent::__construct($uniteeRepository);
    }

    public function all(array $attributs = ['*'], array $relations = []): JsonResponse
    {
        try
        {
            $unitees_de_mesure = collect([]);

            if(!(Auth::user()->hasRole('administrateur') || auth()->user()->profilable_type == "App\\Models\\Administrateur")){
                //$projets = $this->repository->allFiltredBy([['attribut' => 'programmeId', 'operateur' => '=', 'valeur' => auth()->user()->programme->id]]);
                $unitees_de_mesure = Auth::user()->programme->unitees_de_mesure;
            }

            return response()->json(['statut' => 'success', 'message' => null, 'data' => UniteeMesureResource::collection($unitees_de_mesure), 'statutCode' => Response::HTTP_OK], Response::HTTP_OK);
        }
        catch (\Throwable $th)
        {
            return response()->json(['statut' => 'error', 'message' => $th->getMessage(), 'errors' => []], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    public function create($attributs) : JsonResponse
    {
        DB::beginTransaction();

        try {

            $attributs = array_merge($attributs, ['nom' => strtolower($attributs['nom']), 'programmeId' => Auth::user()->programme->id]);

            $unitee_de_mesure = $this->repository->create($attributs);

            $acteur = Auth::check() ? Auth::user()->nom . " ". Auth::user()->prenom : "Inconnu";

            $message = $message ?? Str::ucfirst($acteur) . " a créé un " . strtolower(class_basename($unitee_de_mesure));

            //LogActivity::addToLog("Enregistrement", $message, get_class($unitee_de_mesure), $unitee_de_mesure->id);

            DB::commit();

            return response()->json(['statut' => 'success', 'message' => "Unitee de mesure crée", 'data' => new UniteeMesureResource($unitee_de_mesure), 'statutCode' => Response::HTTP_OK], Response::HTTP_OK);

        } catch (\Throwable $th) {

            DB::rollback();

            //throw $th;
            return response()->json(['statut' => 'error', 'message' => $th->getMessage(), 'errors' => []], Response::HTTP_INTERNAL_SERVER_ERROR);
        }

    }

    public function update($unitee_de_mesure, array $attributs) : JsonResponse
    {

        DB::beginTransaction();

        try {

            if(is_string($unitee_de_mesure))
            {
                $unitee_de_mesure = $this->repository->findById($unitee_de_mesure);
            }
            else{
                $unitee_de_mesure = $unitee_de_mesure;
            }

            $attributs = array_merge($attributs, ['nom' => strtolower($attributs['nom']), 'programmeId' => Auth::user()->programme->id]);

            $unitee_de_mesure = $unitee_de_mesure->fill($attributs);

            $unitee_de_mesure->save();

            $acteur = Auth::check() ? Auth::user()->nom . " ". Auth::user()->prenom : "Inconnu";

            $message = $message ?? Str::ucfirst($acteur) . " a modifié un " . strtolower(class_basename($unitee_de_mesure));

            //LogActivity::addToLog("Modification", $message, get_class($unitee_de_mesure), $unitee_de_mesure->id);

            DB::commit();

            return response()->json(['statut' => 'success', 'message' => "Donnée de l'unitee de mesure modifié", 'data' => new UniteeMesureResource($unitee_de_mesure), 'statutCode' => Response::HTTP_OK], Response::HTTP_OK);

        } catch (\Throwable $th) {

            DB::rollback();

            //throw $th;
            return response()->json(['statut' => 'error', 'message' => $th->getMessage(), 'errors' => []], Response::HTTP_INTERNAL_SERVER_ERROR);
        }

    }

}