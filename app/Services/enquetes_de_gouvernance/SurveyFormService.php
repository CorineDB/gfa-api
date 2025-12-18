<?php

namespace App\Services\enquetes_de_gouvernance;

use App\Http\Resources\gouvernance\SurveyFormResource;
use App\Models\Organisation;
use App\Models\UniteeDeGestion;
use App\Repositories\enquetes_de_gouvernance\SurveyFormRepository;
use Core\Services\Contracts\BaseService;
use Core\Services\Interfaces\enquetes_de_gouvernance\SurveyFormServiceInterface;
use Exception;
use App\Traits\Helpers\LogActivity;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Response;

/**
* Interface SurveyFormServiceInterface
* @package Core\Services\Interfaces
*/
class SurveyFormService extends BaseService implements SurveyFormServiceInterface
{

    /**
     * @var service
     */
    protected $repository;

    /**
     * SurveyFormRepository constructor.
     *
     * @param SurveyFormRepository $surveyFormRepository
     */
    public function __construct(SurveyFormRepository $surveyFormRepository)
    {
        parent::__construct($surveyFormRepository);
    }

    public function all(array $columns = ['*'], array $relations = []): JsonResponse
    {
        try
        {
            $surveyForms = [];

            if(Auth::user()->hasRole('organisation') || ( get_class(auth()->user()->profilable) == Organisation::class)){
                $surveyForms = Auth::user()->programme->survey_forms;

                //Auth::user()->profilable->surveys->mapWithKeys(fn($survey) => $survey->survey_reponses);
            }
            else if(Auth::user()->hasRole("unitee-de-gestion") || ( get_class(auth()->user()->profilable) == UniteeDeGestion::class)){
                $surveyForms = Auth::user()->programme->survey_forms;
            }

            return response()->json(['statut' => 'success', 'message' => null, 'data' => SurveyFormResource::collection($surveyForms), 'statutCode' => Response::HTTP_OK], Response::HTTP_OK);
        }

        catch (\Throwable $th)
        {
            return response()->json(['statut' => 'error', 'message' => $th->getMessage(), 'errors' => []], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    public function findById($surveyForm, array $columns = ['*'], array $relations = [], array $appends = []): JsonResponse
    {
        try
        {
            if(!is_object($surveyForm) && !($surveyForm = $this->repository->findById($surveyForm))) throw new Exception("Enquete individuelle inexistante", 500);

            return response()->json(['statut' => 'success', 'message' => null, 'data' => new SurveyFormResource($surveyForm), 'statutCode' => Response::HTTP_OK], Response::HTTP_OK);
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

            $attributs = array_merge($attributs, ['programmeId' => $programme->id, 'created_by_type' => get_class(auth()->user()->profilable), 'created_by_id' => auth()->user()->profilable->id]);

            $surveyForm = $this->repository->create($attributs);

            $acteur = Auth::check() ? Auth::user()->nom . " ". Auth::user()->prenom : "Inconnu";

            $message = $message ?? Str::ucfirst($acteur) . " a créé un " . strtolower(class_basename($surveyForm));

            //LogActivity::addToLog("Enrégistrement", $message, get_class($surveyForm), $surveyForm->id);

            DB::commit();

            return response()->json(['statut' => 'success', 'message' => "Enregistrement réussir", 'data' => new SurveyFormResource($surveyForm), 'statutCode' => Response::HTTP_CREATED], Response::HTTP_CREATED);

        } catch (\Throwable $th) {

            DB::rollBack();

            //throw $th;
            return response()->json(['statut' => 'error', 'message' => $th->getMessage(), 'errors' => [], 'statutCode' => Response::HTTP_INTERNAL_SERVER_ERROR], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    public function update($surveyForm, array $attributs) : JsonResponse
    {
        DB::beginTransaction();

        try {

            if(!is_object($surveyForm) && !($surveyForm = $this->repository->findById($surveyForm))) throw new Exception("Enquete inexistante", 500);

            $this->repository->update($surveyForm->id, $attributs);

            $surveyForm->refresh();

            $acteur = Auth::check() ? Auth::user()->nom . " ". Auth::user()->prenom : "Inconnu";

            $message = $message ?? Str::ucfirst($acteur) . " a modifié un " . strtolower(class_basename($surveyForm));

            //LogActivity::addToLog("Modification", $message, get_class($surveyForm), $surveyForm->id);

            DB::commit();

            return response()->json(['statut' => 'success', 'message' => "Enregistrement réussir", 'data' => new SurveyFormResource($surveyForm), 'statutCode' => Response::HTTP_CREATED], Response::HTTP_CREATED);

        } catch (\Throwable $th) {

            DB::rollBack();

            //throw $th;
            return response()->json(['statut' => 'error', 'message' => $th->getMessage(), 'errors' => [], 'statutCode' => Response::HTTP_INTERNAL_SERVER_ERROR], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

}