<?php

namespace App\Services;

use App\Http\Resources\gouvernance\SurveyResource;
use App\Http\Resources\gouvernance\SurveyReponseResource;
use App\Http\Resources\gouvernance\SurveyFormResource;

use App\Repositories\SurveyRepository;
use App\Repositories\SurveyFormRepository;
use Core\Services\Contracts\BaseService;
use Core\Services\Interfaces\SurveyServiceInterface;
use Exception;
use App\Traits\Helpers\LogActivity;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Response;

/**
* Interface SurveyServiceInterface
* @package Core\Services\Interfaces
*/
class SurveyService extends BaseService implements SurveyServiceInterface
{

    /**
     * @var service
     */
    protected $repository;

    /**
     * SurveyRepository constructor.
     *
     * @param SurveyRepository $surveyRepository
     */
    public function __construct(SurveyRepository $surveyRepository)
    {
        parent::__construct($surveyRepository);
    }

    public function all(array $columns = ['*'], array $relations = []): JsonResponse
    {
        try
        {
            if(Auth::user()->hasRole('administrateur')){
                $surveys = $this->repository->all();
            }
            else{
                $surveys = Auth::user()->programme->surveys;
            }

            return response()->json(['statut' => 'success', 'message' => null, 'data' => SurveyResource::collection($surveys), 'statutCode' => Response::HTTP_OK], Response::HTTP_OK);
        }

        catch (\Throwable $th)
        {
            return response()->json(['statut' => 'error', 'message' => $th->getMessage(), 'errors' => []], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    public function survey_reponses($survey, array $columns = ['*'], array $relations = [], array $appends = []): JsonResponse
    {
        try
        {
            if(!is_object($survey) && !($survey = $this->repository->findById($survey))) throw new Exception("Enquete individuelle inexistante", 500);

            return response()->json(['statut' => 'success', 'message' => null, 'data' => SurveyReponseResource::collection($survey->survey_reponses), 'statutCode' => Response::HTTP_OK], Response::HTTP_OK);
        }

        catch (\Throwable $th)
        {
            return response()->json(['statut' => 'error', 'message' => $th->getMessage(), 'errors' => []], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    public function formulaire($survey, array $columns = ['*'], array $relations = [], array $appends = []): JsonResponse
    {
        try
        {
            if(!is_object($survey) && !($survey = $this->repository->findById($survey))) throw new Exception("Enquete individuelle inexistante", 500);

            return response()->json(['statut' => 'success', 'message' => null, 'data' => new SurveyFormResource($survey->survey_form), 'statutCode' => Response::HTTP_OK], Response::HTTP_OK);
        }

        catch (\Throwable $th)
        {
            return response()->json(['statut' => 'error', 'message' => $th->getMessage(), 'errors' => []], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    public function findById($survey, array $columns = ['*'], array $relations = [], array $appends = []): JsonResponse
    {
        try
        {
            if(!is_object($survey) && !($survey = $this->repository->findById($survey))) throw new Exception("Enquete individuelle inexistante", 500);

            return response()->json(['statut' => 'success', 'message' => null, 'data' => new SurveyResource($survey), 'statutCode' => Response::HTTP_OK], Response::HTTP_OK);
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

            if (!($surveyForm = app(SurveyFormRepository::class)->findById($attributs["surveyFormId"]))){
                throw new Exception("Ce formulaire n'existe pas", Response::HTTP_NOT_FOUND);
            }

            $programme = Auth::user()->programme;

            $attributs = array_merge($attributs, ['programmeId' => $programme->id, 'surveyFormId' => $surveyForm->id]);
            
            $survey = $this->repository->create($attributs);

            $acteur = Auth::check() ? Auth::user()->nom . " ". Auth::user()->prenom : "Inconnu";

            $message = $message ?? Str::ucfirst($acteur) . " a créé un " . strtolower(class_basename($survey));

            LogActivity::addToLog("Enrégistrement", $message, get_class($survey), $survey->id);

            DB::commit();

            return response()->json(['statut' => 'success', 'message' => "Enregistrement réussir", 'data' => new SurveyResource($survey), 'statutCode' => Response::HTTP_CREATED], Response::HTTP_CREATED);

        } catch (\Throwable $th) {

            DB::rollBack();

            //throw $th;
            return response()->json(['statut' => 'error', 'message' => $th->getMessage(), 'errors' => [], 'statutCode' => Response::HTTP_INTERNAL_SERVER_ERROR], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    public function update($survey, array $attributs) : JsonResponse
    {
        DB::beginTransaction();

        try {

            if(!is_object($survey) && !($survey = $this->repository->findById($survey))) throw new Exception("Enquete inexistante", 500);

            if (!($surveyForm = app(SurveyFormRepository::class)->findById($attributs["surveyFormId"]))){
                throw new Exception("Ce formulaire n'existe pas", Response::HTTP_NOT_FOUND);
            }

            $attributs = array_merge($attributs, ['surveyFormId' => $surveyForm->id]);

            $this->repository->update($survey->id, $attributs);

            $survey->refresh();

            $acteur = Auth::check() ? Auth::user()->nom . " ". Auth::user()->prenom : "Inconnu";

            $message = $message ?? Str::ucfirst($acteur) . " a modifié un " . strtolower(class_basename($survey));

            LogActivity::addToLog("Modification", $message, get_class($survey), $survey->id);

            DB::commit();

            return response()->json(['statut' => 'success', 'message' => "Enregistrement réussir", 'data' => new SurveyResource($survey), 'statutCode' => Response::HTTP_CREATED], Response::HTTP_CREATED);

        } catch (\Throwable $th) {

            DB::rollBack();

            //throw $th;
            return response()->json(['statut' => 'error', 'message' => $th->getMessage(), 'errors' => [], 'statutCode' => Response::HTTP_INTERNAL_SERVER_ERROR], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

}