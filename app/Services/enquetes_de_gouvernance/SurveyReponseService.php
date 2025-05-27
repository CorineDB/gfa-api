<?php

namespace App\Services\enquetes_de_gouvernance;

use App\Http\Resources\gouvernance\SurveyReponseResource;
use App\Models\Organisation;
use App\Models\UniteeDeGestion;
use App\Repositories\enquetes_de_gouvernance\SurveyReponseRepository;
use App\Repositories\enquetes_de_gouvernance\SurveyRepository;
use Core\Services\Contracts\BaseService;
use Core\Services\Interfaces\enquetes_de_gouvernance\SurveyReponseServiceInterface;
use Exception;
use App\Traits\Helpers\LogActivity;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Response;

/**
* Interface SurveyReponseServiceInterface
* @package Core\Services\Interfaces
*/
class SurveyReponseService extends BaseService implements SurveyReponseServiceInterface
{

    /**
     * @var service
     */
    protected $repository;

    /**
     * SurveyReponseRepository constructor.
     *
     * @param SurveyReponseRepository $surveyReponseRepository
     */
    public function __construct(SurveyReponseRepository $surveyReponseRepository)
    {
        parent::__construct($surveyReponseRepository);
    }

    public function all(array $columns = ['*'], array $relations = []): JsonResponse
    {
        try
        {
            $surveyReponses = [];

            if(Auth::user()->hasRole('organisation') || ( get_class(auth()->user()->profilable) == Organisation::class)){
                $surveyReponses = Auth::user()->profilable->surveys->flatMap(fn($survey) => $survey->survey_reponses ?? []);
            }
            else if(Auth::user()->hasRole("unitee-de-gestion") || ( get_class(auth()->user()->profilable) == UniteeDeGestion::class)){
                $surveyReponses = Auth::user()->programme->surveys->flatMap(fn($survey) => $survey->survey_reponses ?? []);
            }

            return response()->json(['statut' => 'success', 'message' => null, 'data' => SurveyReponseResource::collection($surveyReponses), 'statutCode' => Response::HTTP_OK], Response::HTTP_OK);
        }

        catch (\Throwable $th)
        {
            return response()->json(['statut' => 'error', 'message' => $th->getMessage(), 'errors' => []], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    public function findById($surveyReponse, array $columns = ['*'], array $relations = [], array $appends = []): JsonResponse
    {
        try
        {
            if(!is_object($surveyReponse) && !($surveyReponse = $this->repository->findById($surveyReponse))) throw new Exception("Enquete individuelle inexistante", 500);

            return response()->json(['statut' => 'success', 'message' => null, 'data' => new SurveyReponseResource($surveyReponse), 'statutCode' => Response::HTTP_OK], Response::HTTP_OK);
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

            if (!($survey = app(SurveyRepository::class)->findById($attributs["surveyId"]))){
                throw new Exception("Enquete introuvable", Response::HTTP_NOT_FOUND);
            }

            if(($surveyReponse = $survey->survey_reponses()->where('idParticipant', $attributs['idParticipant'])->first())){
                $surveyReponse->fill($attributs)->save();
                $surveyReponse->refresh();
            }
            else{

                $programme = Auth::user()->programme;

                $attributs = array_merge($attributs, ['programmeId' => $programme->id, 'surveyId' => $survey->id]);

                $surveyReponse = $this->repository->create($attributs);
            }

            $acteur = Auth::check() ? Auth::user()->nom . " ". Auth::user()->prenom : "Inconnu";

            $message = $message ?? Str::ucfirst($acteur) . " a créé un " . strtolower(class_basename($surveyReponse));

            //LogActivity::addToLog("Enrégistrement", $message, get_class($surveyReponse), $surveyReponse->id);

            DB::commit();

            return response()->json(['statut' => 'success', 'message' => "Enregistrement réussir", 'data' => new SurveyReponseResource($surveyReponse), 'statutCode' => Response::HTTP_CREATED], Response::HTTP_CREATED);

        } catch (\Throwable $th) {

            DB::rollBack();

            //throw $th;
            return response()->json(['statut' => 'error', 'message' => $th->getMessage(), 'errors' => [], 'statutCode' => Response::HTTP_INTERNAL_SERVER_ERROR], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    public function update($surveyReponse, array $attributs) : JsonResponse
    {
        DB::beginTransaction();

        try {

            if(!is_object($surveyReponse) && !($surveyReponse = $this->repository->findById($surveyReponse))) throw new Exception("Enquete inexistante", 500);

            if (!($survey = app(SurveyRepository::class)->findById($attributs["surveyId"]))){
                throw new Exception("Enquete introuvable", Response::HTTP_NOT_FOUND);
            }

            $attributs = array_merge($attributs, ['surveyId' => $survey->id]);

            $this->repository->update($surveyReponse->id, $attributs);

            $surveyReponse->refresh();

            $acteur = Auth::check() ? Auth::user()->nom . " ". Auth::user()->prenom : "Inconnu";

            $message = $message ?? Str::ucfirst($acteur) . " a modifié un " . strtolower(class_basename($surveyReponse));

            //LogActivity::addToLog("Modification", $message, get_class($surveyReponse), $surveyReponse->id);

            DB::commit();

            return response()->json(['statut' => 'success', 'message' => "Enregistrement réussir", 'data' => new SurveyReponseResource($surveyReponse), 'statutCode' => Response::HTTP_CREATED], Response::HTTP_CREATED);

        } catch (\Throwable $th) {

            DB::rollBack();

            //throw $th;
            return response()->json(['statut' => 'error', 'message' => $th->getMessage(), 'errors' => [], 'statutCode' => Response::HTTP_INTERNAL_SERVER_ERROR], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

}