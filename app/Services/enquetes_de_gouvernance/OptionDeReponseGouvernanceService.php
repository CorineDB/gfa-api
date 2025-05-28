<?php

namespace App\Services\enquetes_de_gouvernance;

use App\Http\Resources\gouvernance\OptionsDeReponseResource;
use App\Repositories\enquetes_de_gouvernance\OptionDeReponseGouvernanceRepository;
use Core\Services\Contracts\BaseService;
use Core\Services\Interfaces\enquetes_de_gouvernance\OptionDeReponseGouvernanceServiceInterface;
use Exception;
use App\Traits\Helpers\LogActivity;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Response;

/**
* Interface OptionDeReponseGouvernanceServiceInterface
* @package Core\Services\Interfaces
*/
class OptionDeReponseGouvernanceService extends BaseService implements OptionDeReponseGouvernanceServiceInterface
{

    /**
     * @var service
     */
    protected $repository;

    /**
     * OptionDeReponseGouvernanceRepository constructor.
     *
     * @param OptionDeReponseGouvernanceRepository $optionDeReponseRepository
     */
    public function __construct(OptionDeReponseGouvernanceRepository $optionDeReponseRepository)
    {
        parent::__construct($optionDeReponseRepository);
    }

    public function all(array $columns = ['*'], array $relations = []): JsonResponse
    {
        try
        {
            $optionsDeReponse = collect([]);

            if(!(Auth::user()->hasRole('administrateur') || auth()->user()->profilable_type == "App\\Models\\Administrateur")){
                //$projets = $this->repository->allFiltredBy([['attribut' => 'programmeId', 'operateur' => '=', 'valeur' => auth()->user()->programme->id]]);
                $optionsDeReponse = Auth::user()->programme->options_de_reponse_gouvernance;
            }

            return response()->json(['statut' => 'success', 'message' => null, 'data' => OptionsDeReponseResource::collection($optionsDeReponse), 'statutCode' => Response::HTTP_OK], Response::HTTP_OK);
        }

        catch (\Throwable $th)
        {
            return response()->json(['statut' => 'error', 'message' => $th->getMessage(), 'errors' => []], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
    /**
     * Liste des options de gouvernance factuel
     *
     * return JsonResponse
     */
    public function options_factuel(array $columns = ['*'], array $relations = []): JsonResponse
    {
        try
        {
            $optionsDeReponse = collect([]);

            if(!(Auth::user()->hasRole('administrateur') || auth()->user()->profilable_type == "App\\Models\\Administrateur")){
                //$projets = $this->repository->allFiltredBy([['attribut' => 'programmeId', 'operateur' => '=', 'valeur' => auth()->user()->programme->id]]);
                $optionsDeReponse = Auth::user()->programme->options_de_reponse_factuel_gouvernance;
            }

            return response()->json(['statut' => 'success', 'message' => null, 'data' => OptionsDeReponseResource::collection($optionsDeReponse), 'statutCode' => Response::HTTP_OK], Response::HTTP_OK);
        }

        catch (\Throwable $th)
        {
            return response()->json(['statut' => 'error', 'message' => $th->getMessage(), 'errors' => []], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * Liste des options de gouvernance perception
     *
     * return JsonResponse
     */
    public function options_de_perception(array $columns = ['*'], array $relations = []): JsonResponse
    {
        try
        {
            $optionsDeReponse = collect([]);

            if(!(Auth::user()->hasRole('administrateur') || auth()->user()->profilable_type == "App\\Models\\Administrateur")){
                //$projets = $this->repository->allFiltredBy([['attribut' => 'programmeId', 'operateur' => '=', 'valeur' => auth()->user()->programme->id]]);
                $optionsDeReponse = Auth::user()->programme->options_de_reponse_de_perception_gouvernance;
            }

            return response()->json(['statut' => 'success', 'message' => null, 'data' => OptionsDeReponseResource::collection($optionsDeReponse), 'statutCode' => Response::HTTP_OK], Response::HTTP_OK);
        }

        catch (\Throwable $th)
        {
            return response()->json(['statut' => 'error', 'message' => $th->getMessage(), 'errors' => []], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    public function findById($optionDeReponse, array $columns = ['*'], array $relations = [], array $appends = []): JsonResponse
    {
        try
        {
            if(!is_object($optionDeReponse) && !($optionDeReponse = $this->repository->findById($optionDeReponse))) throw new Exception("Cette option de reponse n'existe pas", 500);

            return response()->json(['statut' => 'success', 'message' => null, 'data' => new OptionsDeReponseResource($optionDeReponse), 'statutCode' => Response::HTTP_OK], Response::HTTP_OK);
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

            $optionDeReponse = $this->repository->create($attributs);

            $acteur = Auth::check() ? Auth::user()->nom . " ". Auth::user()->prenom : "Inconnu";

            $message = $message ?? Str::ucfirst($acteur) . " a créé un " . strtolower(class_basename($optionDeReponse));

            //LogActivity::addToLog("Enrégistrement", $message, get_class($optionDeReponse), $optionDeReponse->id);

            DB::commit();

            return response()->json(['statut' => 'success', 'message' => "Enregistrement réussir", 'data' => new OptionsDeReponseResource($optionDeReponse), 'statutCode' => Response::HTTP_CREATED], Response::HTTP_CREATED);

        } catch (\Throwable $th) {

            DB::rollBack();

            //throw $th;
            return response()->json(['statut' => 'error', 'message' => $th->getMessage(), 'errors' => [], 'statutCode' => Response::HTTP_INTERNAL_SERVER_ERROR], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    public function update($optionDeReponse, array $attributs) : JsonResponse
    {
        DB::beginTransaction();

        try {

            if(!is_object($optionDeReponse) && !($optionDeReponse = $this->repository->findById($optionDeReponse))) throw new Exception("Cette option de reponse n'existe pas", 500);

            $this->repository->update($optionDeReponse->id, $attributs);

            $optionDeReponse->refresh();

            $acteur = Auth::check() ? Auth::user()->nom . " ". Auth::user()->prenom : "Inconnu";

            $message = $message ?? Str::ucfirst($acteur) . " a modifié un " . strtolower(class_basename($optionDeReponse));

            //LogActivity::addToLog("Modification", $message, get_class($optionDeReponse), $optionDeReponse->id);

            DB::commit();

            return response()->json(['statut' => 'success', 'message' => "Enregistrement réussir", 'data' => new OptionsDeReponseResource($optionDeReponse), 'statutCode' => Response::HTTP_CREATED], Response::HTTP_CREATED);

        } catch (\Throwable $th) {

            DB::rollBack();

            //throw $th;
            return response()->json(['statut' => 'error', 'message' => $th->getMessage(), 'errors' => [], 'statutCode' => Response::HTTP_INTERNAL_SERVER_ERROR], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

}