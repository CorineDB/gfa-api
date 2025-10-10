<?php

namespace App\Services\enquetes_de_gouvernance;

use App\Http\Resources\gouvernance\SurveyFormResource;
use App\Http\Resources\gouvernance\SurveyReponseResource;
use App\Http\Resources\gouvernance\SurveyResource;
use App\Http\Resources\gouvernance\SurveysResource;
use App\Mail\InfoEnquetteIndividuelleEmail;
use App\Models\Organisation;
use App\Models\UniteeDeGestion;
use App\Repositories\enquetes_de_gouvernance\SurveyRepository;
use App\Repositories\enquetes_de_gouvernance\SurveyFormRepository;
use App\Traits\Helpers\HelperTrait;
use Core\Services\Contracts\BaseService;
use Core\Services\Interfaces\enquetes_de_gouvernance\SurveyServiceInterface;
use Exception;
use App\Traits\Helpers\LogActivity;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;

/**
 * Interface SurveyServiceInterface
 * @package Core\Services\Interfaces
 */
class SurveyService extends BaseService implements SurveyServiceInterface
{
    use HelperTrait;

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
        try {
            $surveys = [];

            if(Auth::user()->hasRole('organisation') || ( get_class(auth()->user()->profilable) == Organisation::class)){
                $surveys = Auth::user()->profilable->surveys;
            }
            else if(Auth::user()->hasRole("unitee-de-gestion") || ( get_class(auth()->user()->profilable) == UniteeDeGestion::class)){
                $surveys = Auth::user()->profilable->surveys;
            }

            return response()->json(['statut' => 'success', 'message' => null, 'data' => SurveysResource::collection($surveys), 'statutCode' => Response::HTTP_OK], Response::HTTP_OK);
        } catch (\Throwable $th) {
            return response()->json(['statut' => 'error', 'message' => $th->getMessage(), 'errors' => []], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    public function survey_reponses($survey, array $columns = ['*'], array $relations = [], array $appends = []): JsonResponse
    {
        try {
            if (!is_object($survey) && !($survey = $this->repository->findById($survey))) throw new Exception("Enquete individuelle inexistante", 500);

            return response()->json(['statut' => 'success', 'message' => null, 'data' => SurveyReponseResource::collection($survey->survey_reponses), 'statutCode' => Response::HTTP_OK], Response::HTTP_OK);
        } catch (\Throwable $th) {
            return response()->json(['statut' => 'error', 'message' => $th->getMessage(), 'errors' => []], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    public function formulaire($survey, array $columns = ['*'], array $relations = [], array $appends = []): JsonResponse
    {
        try {
            if (!is_object($survey) && !($survey = $this->repository->findById($survey))) throw new Exception("Enquete individuelle inexistante", 500);

            return response()->json(['statut' => 'success', 'message' => null, 'data' => new SurveyFormResource($survey->survey_form), 'statutCode' => Response::HTTP_OK], Response::HTTP_OK);
        } catch (\Throwable $th) {
            return response()->json(['statut' => 'error', 'message' => $th->getMessage(), 'errors' => []], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    public function survey_form($token, $idParticipant, array $columns = ['*'], array $relations = [], array $appends = []): JsonResponse
    {
        try {
            if (!($survey = $this->repository->findByAttribute('token', $token))) throw new Exception("Enquete individuelle inexistante", 500);

            if (($survey->survey_reponses()->where('idParticipant', $idParticipant)->first())) {
                $response_data = new SurveyResource($survey->loadSurveyResponseForParticipant($idParticipant));
            } else {
                $response_data =  new SurveyResource($survey);
            }

            return response()->json(['statut' => 'success', 'message' => null, 'data' => $response_data, 'statutCode' => Response::HTTP_OK], Response::HTTP_OK);
        } catch (\Throwable $th) {
            return response()->json(['statut' => 'error', 'message' => $th->getMessage(), 'errors' => []], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    public function findById($survey, array $columns = ['*'], array $relations = [], array $appends = []): JsonResponse
    {
        try {
            if (!is_object($survey) && !($survey = $this->repository->findById($survey))) throw new Exception("Enquete individuelle inexistante", 500);

            return response()->json(['statut' => 'success', 'message' => null, 'data' => new SurveysResource($survey), 'statutCode' => Response::HTTP_OK], Response::HTTP_OK);
        } catch (\Throwable $th) {
            return response()->json(['statut' => 'error', 'message' => $th->getMessage(), 'errors' => []], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    public function create(array $attributs): JsonResponse
    {
        DB::beginTransaction();

        try {

            if (!($surveyForm = app(SurveyFormRepository::class)->findById($attributs["surveyFormId"]))) {
                throw new Exception("Ce formulaire n'existe pas", Response::HTTP_NOT_FOUND);
            }

            $programme = Auth::user()->programme;

            // Generate the token
            $token = str_replace(['/', '\\', '.'], '', Hash::make(
                Hash::make(request()->ip() . auth()->user()->secure_id) .
                    Hash::make(strtotime(now()))
            ));

            $attributs = array_merge($attributs, ['programmeId' => $programme->id, 'surveyable_type' => get_class(auth()->user()->profilable), 'surveyable_id' => auth()->user()->profilable->id, 'surveyFormId' => $surveyForm->id, 'token' => $token]);

            $survey = $this->repository->create($attributs);

            $survey->refresh();

            $acteur = Auth::check() ? Auth::user()->nom . " " . Auth::user()->prenom : "Inconnu";

            $message = $message ?? Str::ucfirst($acteur) . " a créé un " . strtolower(class_basename($survey));

            //LogActivity::addToLog("Enrégistrement", $message, get_class($survey), $survey->id);

            DB::commit();
            $url = $this->getUserTypeAppUrl($survey->surveyable->user);

            /* $url = config("app.url");

            // If the URL is localhost, append the appropriate IP address and port
            if (strpos($url, 'localhost') == false) {
                $url = 'http://192.168.1.16:3000';
                $url = $this->getUserTypeAppUrl($survey->user);
            } */

            $details['view'] = "emails.mail_template";

            $details['subject'] = "Confirmation : Création de l'enquête d'auto-évaluation de gouvernance";
            $details['content'] = [
                "greeting"      => "Bonjour, Monsieur/Madame!",

                "introduction" => "Nous avons le plaisir de vous informer que votre **enquête d'auto-évaluation de gouvernance** a été créée avec succès.",
                "body"          => "Vous pouvez accéder aux détails de l'enquête et les partager avec les participants concernés. Veuillez utiliser le lien ci-dessous pour consulter ou gérer cette enquête. N'hésitez pas à contacter l'équipe de support en cas de besoin.",

                "lien" => $url . "/dashboard/form-individuel/{$survey->token}",
                "survey_form_link_token"    => $survey->token,
                "cta_text" => "Accéder au formulaire de l'enquete individuelle",
                "signature" => "Cordialement, " . auth()->user()->nom,
            ];

            // Create the email instance
            $mailer = new InfoEnquetteIndividuelleEmail($details);

            // Send the email later after a delay
            $when = now()->addSeconds(5);
            Mail::to(auth()->user()->email)->later($when, $mailer);

            return response()->json(['statut' => 'success', 'message' => "Enregistrement réussir", 'data' => new SurveysResource($survey), 'statutCode' => Response::HTTP_CREATED], Response::HTTP_CREATED);
        } catch (\Throwable $th) {

            DB::rollBack();

            //throw $th;
            return response()->json(['statut' => 'error', 'message' => $th->getMessage(), 'errors' => [], 'statutCode' => Response::HTTP_INTERNAL_SERVER_ERROR], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    public function update($survey, array $attributs): JsonResponse
    {
        DB::beginTransaction();

        try {
            dd($attributs);

            if (!is_object($survey) && !($survey = $this->repository->findById($survey))) throw new Exception("Enquete inexistante", 500);

            if (!($surveyForm = app(SurveyFormRepository::class)->findById($attributs["surveyFormId"]))) {
                throw new Exception("Ce formulaire n'existe pas", Response::HTTP_NOT_FOUND);
            }

            $attributs = array_merge($attributs, ['surveyFormId' => $surveyForm->id]);

            $this->repository->update($survey->id, $attributs);

            $survey->refresh();

            $acteur = Auth::check() ? Auth::user()->nom . " " . Auth::user()->prenom : "Inconnu";

            $message = $message ?? Str::ucfirst($acteur) . " a modifié un " . strtolower(class_basename($survey));

            //LogActivity::addToLog("Modification", $message, get_class($survey), $survey->id);

            DB::commit();

            return response()->json(['statut' => 'success', 'message' => "Enregistrement réussir", 'data' => new SurveysResource($survey), 'statutCode' => Response::HTTP_CREATED], Response::HTTP_CREATED);
        } catch (\Throwable $th) {

            DB::rollBack();

            //throw $th;
            return response()->json(['statut' => 'error', 'message' => $th->getMessage(), 'errors' => [], 'statutCode' => Response::HTTP_INTERNAL_SERVER_ERROR], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
}
