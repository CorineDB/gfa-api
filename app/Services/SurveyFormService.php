<?php

namespace App\Services;

use App\Http\Resources\gouvernance\SurveyFormResource;
use App\Repositories\SurveyFormRepository;
use Core\Services\Contracts\BaseService;
use Core\Services\Interfaces\SurveyFormServiceInterface;
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
            if(Auth::user()->hasRole('administrateur')){
                $surveyForms = $this->repository->all();
            }
            else{
                $surveyForms = Auth::user()->programme->surveys;
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

            if (!($survey = app(SurveyRepository::class)->findById($attributs["surveyId"]))){
                throw new Exception("Ce formulaire n'existe pas", Response::HTTP_NOT_FOUND);
            }

            $programme = Auth::user()->programme;

            $attributs = array_merge($attributs, ['programmeId' => $programme->id, 'surveyFormId' => $surveyForm->id]);
            
            $surveyForm = $this->repository->create($attributs);

            $acteur = Auth::check() ? Auth::user()->nom . " ". Auth::user()->prenom : "Inconnu";

            $message = $message ?? Str::ucfirst($acteur) . " a créé un " . strtolower(class_basename($surveyForm));

            LogActivity::addToLog("Enrégistrement", $message, get_class($surveyForm), $surveyForm->id);

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

            LogActivity::addToLog("Modification", $message, get_class($surveyForm), $surveyForm->id);

            DB::commit();

            return response()->json(['statut' => 'success', 'message' => "Enregistrement réussir", 'data' => new SurveyFormResource($surveyForm), 'statutCode' => Response::HTTP_CREATED], Response::HTTP_CREATED);

        } catch (\Throwable $th) {

            DB::rollBack();

            //throw $th;
            return response()->json(['statut' => 'error', 'message' => $th->getMessage(), 'errors' => [], 'statutCode' => Response::HTTP_INTERNAL_SERVER_ERROR], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

}