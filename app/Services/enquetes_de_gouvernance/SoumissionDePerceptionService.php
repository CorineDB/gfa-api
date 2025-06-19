<?php

namespace App\Services\enquetes_de_gouvernance;

use App\Http\Resources\enquetes_de_gouvernance\SoumissionDePerceptionResource;
use App\Jobs\AppJob;
use App\Models\enquetes_de_gouvernance\SoumissionDePerception;
use App\Models\Organisation;
use App\Repositories\enquetes_de_gouvernance\EvaluationDeGouvernanceRepository;
use App\Repositories\enquetes_de_gouvernance\FormulaireDePerceptionDeGouvernanceRepository;
use App\Repositories\enquetes_de_gouvernance\OptionDeReponseGouvernanceRepository;
use App\Repositories\OrganisationRepository;
use App\Repositories\enquetes_de_gouvernance\QuestionDePerceptionDeGouvernanceRepository;
use App\Repositories\enquetes_de_gouvernance\SoumissionDePerceptionRepository;
use Core\Services\Contracts\BaseService;
use Core\Services\Interfaces\enquetes_de_gouvernance\SoumissionDePerceptionServiceInterface;
use App\Traits\Helpers\HelperTrait;
use Exception;
use App\Traits\Helpers\LogActivity;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Artisan;

/**
 * Interface SoumissionDePerceptionServiceInterface
 * @package Core\Services\Interfaces
 */
class SoumissionDePerceptionService extends BaseService implements SoumissionDePerceptionServiceInterface
{
    use HelperTrait;

    /**
     * @var service
     */
    protected $repository;

    /**
     * SoumissionDePerceptionRepository constructor.
     *
     * @param SoumissionDePerceptionRepository $soumissionRepository
     */
    public function __construct(SoumissionDePerceptionRepository $soumissionRepository)
    {
        parent::__construct($soumissionRepository);
    }

    public function all(array $columns = ['*'], array $relations = []): JsonResponse
    {

        try {

            $soumissions = collect([]);

            if (!(Auth::user()->hasRole('administrateur') || auth()->user()->profilable_type == "App\\Models\\Administrateur")) {
                $soumissions = Auth::user()->programme->soumissions_de_perception;
            }

            return response()->json(['statut' => 'success', 'message' => null, 'data' => SoumissionDePerceptionResource::collection($soumissions), 'statutCode' => Response::HTTP_OK], Response::HTTP_OK);
        } catch (\Throwable $th) {
            return response()->json(['statut' => 'error', 'message' => $th->getMessage(), 'errors' => []], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    public function findById($soumissionDePerceptionId, array $columns = ['*'], array $relations = [], array $appends = []): JsonResponse
    {
        try {

            if (!is_object($soumissionDePerceptionId)) {
                $soumission = SoumissionDePerception::findByKey($soumissionDePerceptionId)->first();
            }

            if (!$soumission) throw new Exception("Soumission de gouvernance inconnue.", 404);
            return response()->json(['statut' => 'success', 'message' => null, 'data' => new SoumissionDePerceptionResource($soumission), 'statutCode' => Response::HTTP_OK], Response::HTTP_OK);
        } catch (\Throwable $th) {
            return response()->json(['statut' => 'error', 'message' => $th->getMessage(), 'errors' => []], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    public function create(array $attributs): JsonResponse
    {
        DB::beginTransaction();

        try {

            dd($attributs);

            if (isset($attributs['evaluationId'])) {
                if (!($evaluationDeGouvernance = app(EvaluationDeGouvernanceRepository::class)->findById($attributs['evaluationId']))) {
                    throw new Exception("Evaluation de gouvernance est introuvable dans le programme.", Response::HTTP_NOT_FOUND);
                }

                $attributs = array_merge($attributs, ['evaluationId' => $evaluationDeGouvernance->id]);
            }

            $programme = $evaluationDeGouvernance->programme;

            $attributs = array_merge($attributs, ['programmeId' => $programme->id]);

            if (isset($attributs['formulaireDeGouvernanceId'])) {
                if (!(($formulaireDeGouvernance = app(FormulaireDePerceptionDeGouvernanceRepository::class)->findById($attributs['formulaireDeGouvernanceId'])) && $formulaireDeGouvernance->programmeId == $programme->id)) {
                    throw new Exception("Formulaire de gouvernance est introuvable dans le programme.", Response::HTTP_NOT_FOUND);
                }

                $attributs = array_merge($attributs, ['formulaireDeGouvernanceId' => $formulaireDeGouvernance->id]);
            }

            if (isset($attributs['organisationId'])) {

                if (!(($organisation = app(OrganisationRepository::class)->findById($attributs['organisationId'])) && $organisation->user->programmeId == $programme->id)) {
                    throw new Exception("Organisation introuvable dans le programme.", Response::HTTP_NOT_FOUND);
                }

                if (!($organisation = $evaluationDeGouvernance->organisations($organisation->id)->first())) {
                    throw new Exception("Cette organisation n'est pas de cette evaluation.", Response::HTTP_NOT_FOUND);
                }
            } else if (auth()->check()) {
                if (Auth::user()->hasRole('organisation') || (get_class(auth()->user()->profilable) == Organisation::class)) {
                    $organisation = Auth::user()->profilable;
                }
            } else {
                throw new Exception("Organisation introuvable dans le programme.", Response::HTTP_NOT_FOUND);
            }

            $attributs = array_merge($attributs, ['organisationId' => $organisation->id]);

            $evaluationOrganisation = $evaluationDeGouvernance->organisations($organisation->id)->first();

            /* if ($evaluationDeGouvernance->soumissionsDePerception($organisation->id)->where('statut', true)->count() == $evaluationOrganisation->pivot->nbreParticipants) {
                return response()->json(['statut' => 'success', 'message' => "Quota des soumissions atteints", 'data' => ['terminer' => true], 'statutCode' => Response::HTTP_PARTIAL_CONTENT], Response::HTTP_PARTIAL_CONTENT);
            } */

            $soumission = $this->repository->getInstance()->where("evaluationId", $evaluationDeGouvernance->id)->where("organisationId", $organisation->id)->where("formulaireDePerceptionId", $formulaireDeGouvernance->id)->where('identifier_of_participant', $attributs['identifier_of_participant'])->first();

            if ($soumission == null) {
                $soumission = $this->repository->create($attributs);
            } else {
                if ($soumission->statut) {
                    return response()->json(['statut' => 'success', 'message' => "La soumission a déjà été validée.", 'data' => ['terminer' => true], 'statutCode' => Response::HTTP_PARTIAL_CONTENT], Response::HTTP_PARTIAL_CONTENT);
                }
                $soumission->fill($attributs);
                $soumission->save();

                $soumission->refresh();
            }

            if (isset($attributs['perception']) && !empty($attributs['perception'])) {
                $soumission->fill($attributs['perception']);
                $soumission->save();
                $soumission->refresh();

                foreach ($attributs['perception']['response_data'] as $key => $item) {

                    if (!(($questionDeGouvernance = app(QuestionDePerceptionDeGouvernanceRepository::class)->findById($item['questionId'])) && $questionDeGouvernance->programmeId == $programme->id)) {
                        throw new Exception("Question de gouvernance introuvable dans le programme.", Response::HTTP_NOT_FOUND);
                    }

                    //$option = app(OptionDeReponseGouvernanceRepository::class)->findById($item['optionDeReponseId'])->where("programmeId", $programme->id)->first();
                    $option = app(OptionDeReponseGouvernanceRepository::class)->findById($item['optionDeReponseId']);

                    if (!$option && $option->programmeId == $programme->id) throw new Exception("Cette option n'est pas dans le programme", Response::HTTP_NOT_FOUND);

                    $pivot = $option->formulaires_de_perception_de_gouvernance()->wherePivot("formulaireDePerceptionId", $soumission->formulaireDeGouvernance->id)->first()->pivot;

                    if (!($reponseDeLaCollecte = $soumission->reponses_de_la_collecte()->where(['programmeId' => $programme->id, 'questionId' => $questionDeGouvernance->id])->first())) {
                        $reponseDeLaCollecte = $soumission->reponses_de_la_collecte()->create(array_merge($item, ['formulaireDePerceptionId' => $soumission->formulaireDeGouvernance->id, 'questionId' => $questionDeGouvernance->id, 'optionDeReponseId' => $option->id, 'programmeId' => $programme->id, 'point' => $pivot->point]));
                    } else {
                        unset($item['questionId']);
                        $reponseDeLaCollecte->fill(array_merge($item, ['formulaireDePerceptionId' => $soumission->formulaireDeGouvernance->id, 'optionDeReponseId' => $option->id, 'programmeId' => $programme->id, 'point' => $pivot->point]));
                        $reponseDeLaCollecte->save();
                    }
                }
            }

            if ($soumission->commentaire !== null && $soumission->sexe !== null && $soumission->age !== null && $soumission->categorieDeParticipant !== null) {

                $soumission->refresh();

                $responseCount = $soumission->formulaireDeGouvernance->questions_de_gouvernance()->whereHas('reponses')->count();

                if (($responseCount === $soumission->formulaireDeGouvernance->questions_de_gouvernance->count()) && (isset($attributs['validation']) && $attributs['validation'])) {

                    $soumission->submittedBy  = null;
                    $soumission->statut       = true;

                    $soumission->save();

                    AppJob::dispatch(
                        // Call the GenerateEvaluationResultats command with the evaluation ID
                        Artisan::call('generate:report-for-validated-soumissions')
                    )->delay(now()->addMinutes(3)); // Optionally add additional delay at dispatch time->addMinutes(10)

                }
            }

            $acteur = Auth::check() ? Auth::user()->nom . " " . Auth::user()->prenom : $attributs['identifier_of_participant'];

            $message = $message ?? Str::ucfirst($acteur) . " a créé un " . strtolower(class_basename($soumission));

            //LogActivity::addToLog("Enrégistrement", $message, get_class($soumission), $soumission->id);

            DB::commit();

            return response()->json(['statut' => 'success', 'message' => "Enregistrement réussir", 'data' => new SoumissionDePerceptionResource($soumission), 'statutCode' => Response::HTTP_CREATED], Response::HTTP_CREATED);
        } catch (\Throwable $th) {

            DB::rollBack();

            //throw $th;
            return response()->json(['statut' => 'error', 'message' => $th->getMessage(), 'errors' => [], 'statutCode' => Response::HTTP_INTERNAL_SERVER_ERROR], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
}
