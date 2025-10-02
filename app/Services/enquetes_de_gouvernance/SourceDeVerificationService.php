<?php

namespace App\Services\enquetes_de_gouvernance;

use App\Http\Resources\enquetes_de_gouvernance\SourcesDeVerificationResource;
use App\Repositories\enquetes_de_gouvernance\SourceDeVerificationRepository;
use Core\Services\Contracts\BaseService;
use Core\Services\Interfaces\enquetes_de_gouvernance\SourceDeVerificationServiceInterface;
use Exception;
use App\Traits\Helpers\LogActivity;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Response;

/**
* Interface SourceDeVerificationServiceInterface
* @package Core\Services\Interfaces
*/
class SourceDeVerificationService extends BaseService implements SourceDeVerificationServiceInterface
{

    /**
     * @var service
     */
    protected $repository;

    /**
     * SourceDeVerificationRepository constructor.
     *
     * @param SourceDeVerificationRepository $optionDeReponseRepository
     */
    public function __construct(SourceDeVerificationRepository $optionDeReponseRepository)
    {
        parent::__construct($optionDeReponseRepository);
    }

    public function all(array $columns = ['*'], array $relations = []): JsonResponse
    {
        try
        {

            $sourcesDeVerification = collect([]);

            if(!(Auth::user()->hasRole('administrateur') || auth()->user()->profilable_type == "App\\Models\\Administrateur")){
                //$projets = $this->repository->allFiltredBy([['attribut' => 'programmeId', 'operateur' => '=', 'valeur' => auth()->user()->programme->id]]);
                //$sourcesDeVerification = Auth::user()->programme->sources_de_verification;
                $sourcesDeVerification = Auth::user()->programme->enquete_sources_de_verification;
            }

            return response()->json(['statut' => 'success', 'message' => null, 'data' => SourcesDeVerificationResource::collection($sourcesDeVerification), 'statutCode' => Response::HTTP_OK], Response::HTTP_OK);
        }

        catch (\Throwable $th)
        {
            return response()->json(['statut' => 'error', 'message' => $th->getMessage(), 'errors' => []], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    public function findById($sourceDeVerification, array $columns = ['*'], array $relations = [], array $appends = []): JsonResponse
    {
        try
        {
            // throw new Exception("Error Processing Request " . $sourceDeVerification, 1);

            if(!is_object($sourceDeVerification) && !($sourceDeVerification = $this->repository->findById($sourceDeVerification))) throw new Exception("Source de verification inconnue.", 500);

            return response()->json(['statut' => 'success', 'message' => null, 'data' => new SourcesDeVerificationResource($sourceDeVerification), 'statutCode' => Response::HTTP_OK], Response::HTTP_OK);
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

            $sourceDeVerification = $this->repository->create($attributs);

            $acteur = Auth::check() ? Auth::user()->nom . " ". Auth::user()->prenom : "Inconnu";

            $message = $message ?? Str::ucfirst($acteur) . " a créé un " . strtolower(class_basename($sourceDeVerification));

            //LogActivity::addToLog("Enrégistrement", $message, get_class($sourceDeVerification), $sourceDeVerification->id);

            DB::commit();

            return response()->json(['statut' => 'success', 'message' => "Enregistrement réussir", 'data' => new SourcesDeVerificationResource($sourceDeVerification), 'statutCode' => Response::HTTP_CREATED], Response::HTTP_CREATED);

        } catch (\Throwable $th) {

            DB::rollBack();

            //throw $th;
            return response()->json(['statut' => 'error', 'message' => $th->getMessage(), 'errors' => [], 'statutCode' => Response::HTTP_INTERNAL_SERVER_ERROR], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    public function update($sourceDeVerification, array $attributs) : JsonResponse
    {
        DB::beginTransaction();

        try {

            if(!is_object($sourceDeVerification) && !($sourceDeVerification = $this->repository->findById($sourceDeVerification))) throw new Exception("Cette option de reponse n'existe pas", 500);

            $this->repository->update($sourceDeVerification->id, $attributs);

            $sourceDeVerification->refresh();

            $acteur = Auth::check() ? Auth::user()->nom . " ". Auth::user()->prenom : "Inconnu";

            $message = $message ?? Str::ucfirst($acteur) . " a modifié un " . strtolower(class_basename($sourceDeVerification));

            //LogActivity::addToLog("Modification", $message, get_class($sourceDeVerification), $sourceDeVerification->id);

            DB::commit();

            return response()->json(['statut' => 'success', 'message' => "Enregistrement réussir", 'data' => new SourcesDeVerificationResource($sourceDeVerification), 'statutCode' => Response::HTTP_CREATED], Response::HTTP_CREATED);

        } catch (\Throwable $th) {

            DB::rollBack();

            //throw $th;
            return response()->json(['statut' => 'error', 'message' => $th->getMessage(), 'errors' => [], 'statutCode' => Response::HTTP_INTERNAL_SERVER_ERROR], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

}
