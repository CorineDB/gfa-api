<?php

namespace App\Services;

use App\Http\Resources\gouvernance\FichesDeSyntheseResource;
use App\Http\Resources\gouvernance\RecommandationsResource;
use App\Http\Resources\gouvernance\SoumissionsResource;
use App\Jobs\AppJob;
use App\Models\Organisation;
use App\Repositories\EvaluationDeGouvernanceRepository;
use App\Repositories\FormulaireDeGouvernanceRepository;
use App\Repositories\OptionDeReponseRepository;
use App\Repositories\OrganisationRepository;
use App\Repositories\ProgrammeRepository;
use App\Repositories\QuestionDeGouvernanceRepository;
use App\Repositories\SoumissionRepository;
use App\Repositories\SourceDeVerificationRepository;
use Core\Services\Contracts\BaseService;
use Core\Services\Interfaces\SoumissionServiceInterface;
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
 * Interface SoumissionServiceInterface
 * @package Core\Services\Interfaces
 */
class SoumissionService extends BaseService implements SoumissionServiceInterface
{
    use HelperTrait;

    /**
     * @var service
     */
    protected $repository;

    /**
     * SoumissionRepository constructor.
     *
     * @param SoumissionRepository $soumissionRepository
     */
    public function __construct(SoumissionRepository $soumissionRepository)
    {
        parent::__construct($soumissionRepository);
    }

    public function all(array $columns = ['*'], array $relations = []): JsonResponse
    {

        try {

            $soumissions = collect([]);

            if (!(Auth::user()->hasRole('administrateur') || auth()->user()->profilable_type == "App\\Models\\Administrateur")) {
                $soumissions = Auth::user()->programme->soumissions;
            }

            return response()->json(['statut' => 'success', 'message' => null, 'data' => SoumissionsResource::collection($soumissions), 'statutCode' => Response::HTTP_OK], Response::HTTP_OK);
        } catch (\Throwable $th) {
            return response()->json(['statut' => 'error', 'message' => $th->getMessage(), 'errors' => []], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    public function findById($soumissions, array $columns = ['*'], array $relations = [], array $appends = []): JsonResponse
    {
        try {
            if (!is_object($soumissions) && !($soumissions = $this->repository->findById($soumissions))) throw new Exception("Evaluation de gouvernance inconnue.", 500);

            return response()->json(['statut' => 'success', 'message' => null, 'data' => new SoumissionsResource($soumissions), 'statutCode' => Response::HTTP_OK], Response::HTTP_OK);
        } catch (\Throwable $th) {
            return response()->json(['statut' => 'error', 'message' => $th->getMessage(), 'errors' => []], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    public function create(array $attributs): JsonResponse
    {
        DB::beginTransaction();

        try {

            if (isset($attributs['programmeId']) && !empty($attributs['programmeId'])) {
                $programme = app(ProgrammeRepository::class)->findById($attributs['programmeId']);
            } else {
                $programme = Auth::user()->programme;
            }

            $attributs = array_merge($attributs, ['programmeId' => $programme->id]);

            if (isset($attributs['evaluationId'])) {
                if (!(($evaluationDeGouvernance = app(EvaluationDeGouvernanceRepository::class)->findById($attributs['evaluationId'])) && $evaluationDeGouvernance->programmeId == $programme->id)) {
                    throw new Exception("Evaluation de gouvernance est introuvable dans le programme.", Response::HTTP_NOT_FOUND);
                }

                $attributs = array_merge($attributs, ['evaluationId' => $evaluationDeGouvernance->id]);
            }

            if (isset($attributs['formulaireDeGouvernanceId'])) {
                if (!(($formulaireDeGouvernance = app(FormulaireDeGouvernanceRepository::class)->findById($attributs['formulaireDeGouvernanceId'])) && $formulaireDeGouvernance->programmeId == $programme->id)) {
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

            if ($formulaireDeGouvernance->type == 'factuel') {

                if (($soumission = $evaluationDeGouvernance->soumissionFactuel($organisation->id)->first()) && $soumission->statut) {
                    return response()->json(['statut' => 'success', 'message' => "Quota des soumissions atteints", 'data' => ['terminer' => true, 'soumission' => $soumission], 'statutCode' => Response::HTTP_PARTIAL_CONTENT], Response::HTTP_PARTIAL_CONTENT);
                }

                $soumission  = $this->repository->getInstance()->where('type', 'factuel')->where("evaluationId", $evaluationDeGouvernance->id)->where("organisationId", $organisation->id)->where("formulaireDeGouvernanceId", $formulaireDeGouvernance->id)->first();
            } else {

                $evaluationOrganisation = $evaluationDeGouvernance->organisations($organisation->id)->first();

                if ($evaluationDeGouvernance->soumissionsDePerception($organisation->id)->where('statut', true)->count() == $evaluationOrganisation->pivot->nbreParticipants) {
                    return response()->json(['statut' => 'success', 'message' => "Quota des soumissions atteints", 'data' => ['terminer' => true], 'statutCode' => Response::HTTP_PARTIAL_CONTENT], Response::HTTP_PARTIAL_CONTENT);
                }

                $soumission  = $this->repository->getInstance()->where('type', 'perception')->where("evaluationId", $evaluationDeGouvernance->id)->where("organisationId", $organisation->id)->where("formulaireDeGouvernanceId", $formulaireDeGouvernance->id)->where('identifier_of_participant', $attributs['identifier_of_participant'])->first();
            }

            if ($soumission == null) {
                $soumission = $this->repository->create($attributs);
            } else {
                if ($soumission->statut) {
                    return response()->json(['statut' => 'success', 'message' => "La soumission a déjà été validée.", 'data' => ['terminer' => true], 'statutCode' => Response::HTTP_PARTIAL_CONTENT], Response::HTTP_PARTIAL_CONTENT);
                }
                $soumission->fill($attributs);
                $soumission->save();
            }

            $soumission->refresh();

            $soumission->type = $soumission->formulaireDeGouvernance->type;

            $soumission->save();

            if (isset($attributs['factuel']) && !empty($attributs['factuel'])) {
                $soumission->fill($attributs['factuel']);
                $soumission->save();

                foreach ($attributs['factuel']['response_data'] as $key => $item) {

                    if (!(($questionDeGouvernance = app(QuestionDeGouvernanceRepository::class)->findById($item['questionId'])) && $questionDeGouvernance->programmeId == $programme->id)) {
                        throw new Exception("Question de gouvernance introuvable dans le programme.", Response::HTTP_NOT_FOUND);
                    }

                    //$option = app(OptionDeReponseRepository::class)->findById($item['optionDeReponseId'])->where("programmeId", $programme->id)->first();
                    $option = app(OptionDeReponseRepository::class)->findById($item['optionDeReponseId']);

                    if (!$option && $option->programmeId == $programme->id) throw new Exception("Cette option n'est pas dans le programme", Response::HTTP_NOT_FOUND);

                    if (isset($item['sourceDeVerificationId']) && !empty($item['sourceDeVerificationId'])) {

                        if (!(($sourceDeVerification = app(SourceDeVerificationRepository::class)->findById($item['sourceDeVerificationId'])) && optional($sourceDeVerification)->programmeId == $programme->id)) {
                            throw new Exception("Source de verification inconnue du programme.", Response::HTTP_NOT_FOUND);
                        }

                        $item = array_merge($item, ['sourceDeVerificationId' => $sourceDeVerification->id, 'sourceDeVerification' => null]);
                    } else if (isset($item['sourceDeVerification']) && !empty($item['sourceDeVerification'])) {
                        $item = array_merge($item, ['sourceDeVerificationId' => null, 'sourceDeVerification' => $item['sourceDeVerification']]);
                    }

                    $pivot = $option->formulaires_de_gouvernance()->wherePivot("formulaireDeGouvernanceId", $soumission->formulaireDeGouvernance->id)->first()->pivot;
                    //$pivot = $option->formulaires_de_gouvernance()->wherePivot("formulaireDeGouvernanceId", $soumission->formulaireDeGouvernance->id)->first()->pivot;

                    if (!($reponseDeLaCollecte = $soumission->reponses_de_la_collecte()->where(['programmeId' => $programme->id, 'questionId' => $questionDeGouvernance->id])->first())) {
                        $reponseDeLaCollecte = $soumission->reponses_de_la_collecte()->create(array_merge($item, ['formulaireDeGouvernanceId' => $soumission->formulaireDeGouvernance->id, 'optionDeReponseId' => $option->id, 'questionId' => $questionDeGouvernance->id, 'type' => 'indicateur', 'programmeId' => $programme->id, 'point' => $pivot->point, 'preuveIsRequired' => $pivot->preuveIsRequired]));
                    } else {
                        unset($item['questionId']);
                        $reponseDeLaCollecte->fill(array_merge($item, ['formulaireDeGouvernanceId' => $soumission->formulaireDeGouvernance->id, 'optionDeReponseId' => $option->id, 'type' => 'indicateur', 'programmeId' => $programme->id, 'point' => $pivot->point, 'preuveIsRequired' => $pivot->preuveIsRequired]));
                        $reponseDeLaCollecte->save();
                    }

                    if (isset($item['preuves']) && !empty($item['preuves'])) {
                        foreach ($item['preuves'] as $preuve) {

                            // On suppose que $preuve est un fichier de type UploadedFile
                            $filenameWithExt = $preuve->getClientOriginalName();
                            $filename = strtolower(str_replace(' ', '-',time() . '-'. $filenameWithExt));

                            // Vérifie si le fichier existe déjà pour cette réponse
                            $alreadyExists = $reponseDeLaCollecte->preuves_de_verification()
                                ->where('nom', $filename)
                                ->exists();

                            if (!$alreadyExists) {
                                $this->storeFile($preuve, 'soumissions/preuves', $reponseDeLaCollecte, null, 'preuves');
                            }
                        }
                    }
                }
            } else if (isset($attributs['perception']) && !empty($attributs['perception'])) {
                $soumission->fill($attributs['perception']);
                $soumission->save();
                foreach ($attributs['perception']['response_data'] as $key => $item) {

                    if (!(($questionDeGouvernance = app(QuestionDeGouvernanceRepository::class)->findById($item['questionId'])) && $questionDeGouvernance->programmeId == $programme->id)) {
                        throw new Exception("Question de gouvernance introuvable dans le programme.", Response::HTTP_NOT_FOUND);
                    }

                    //$option = app(OptionDeReponseRepository::class)->findById($item['optionDeReponseId'])->where("programmeId", $programme->id)->first();
                    $option = app(OptionDeReponseRepository::class)->findById($item['optionDeReponseId']);

                    if (!$option && $option->programmeId == $programme->id) throw new Exception("Cette option n'est pas dans le programme", Response::HTTP_NOT_FOUND);

                    if (!($reponseDeLaCollecte = $soumission->reponses_de_la_collecte()->where(['programmeId' => $programme->id, 'questionId' => $questionDeGouvernance->id])->first())) {
                        $reponseDeLaCollecte = $soumission->reponses_de_la_collecte()->create(array_merge($item, ['formulaireDeGouvernanceId' => $soumission->formulaireDeGouvernance->id, 'questionId' => $questionDeGouvernance->id, 'optionDeReponseId' => $option->id, 'type' => 'question_operationnelle', 'programmeId' => $programme->id, 'point' => $option->formulaires_de_gouvernance()->wherePivot("formulaireDeGouvernanceId", $soumission->formulaireDeGouvernance->id)->first()->pivot->point]));
                    } else {
                        unset($item['questionId']);
                        $reponseDeLaCollecte->fill(array_merge($item, ['formulaireDeGouvernanceId' => $soumission->formulaireDeGouvernance->id, 'optionDeReponseId' => $option->id, 'type' => 'question_operationnelle', 'programmeId' => $programme->id, 'point' => $option->formulaires_de_gouvernance()->wherePivot("formulaireDeGouvernanceId", $soumission->formulaireDeGouvernance->id)->first()->pivot->point]));
                        $reponseDeLaCollecte->save();
                    }
                }
            }

            if (($soumission->formulaireDeGouvernance->type == 'factuel' && $soumission->comite_members !== null) || ($soumission->formulaireDeGouvernance->type == 'perception' && $soumission->commentaire !== null && $soumission->sexe !== null && $soumission->age !== null && $soumission->categorieDeParticipant !== null)) {

                $soumission->refresh();

                $responseCount = $soumission->formulaireDeGouvernance->questions_de_gouvernance()->whereHas('reponses', function ($query) use ($soumission) {
                    $query->when($soumission->formulaireDeGouvernance->type == 'factuel', function ($query) {

                        $query->where(function ($query) {
                            $query->whereNotNull('sourceDeVerificationId')->orWhereNotNull('sourceDeVerification');
                        });

                        //$query->whereNotNull('sourceDeVerificationId')->orWhereNotNull('sourceDeVerification');

                        // Conditionally apply whereHas('preuves_de_verification') if formulaireDeGouvernance type is 'factuel'

                        $query->whereHas('preuves_de_verification');
                    });
                })->count();

                if (($responseCount === $soumission->formulaireDeGouvernance->questions_de_gouvernance->count()) && (isset($attributs['validation']) && $attributs['validation'])) {
                    $soumission->submitted_at = now();
                    $soumission->submittedBy  = Auth::check() ? auth()->id() : null;
                    $soumission->statut       = true;

                    $soumission->save();

                    AppJob::dispatch(
                        // Call the GenerateEvaluationResultats command with the evaluation ID
                        // Artisan::call('generate:report-for-validated-soumissions')
                        Artisan::call('gouvernance:generate-results')
                    )->delay(now()->addMinutes(3)); // Optionally add additional delay at dispatch time->addMinutes(10)

                }
            }

            $acteur = Auth::check() ? Auth::user()->nom . " " . Auth::user()->prenom : $attributs['identifier_of_participant'];

            $message = $message ?? Str::ucfirst($acteur) . " a créé un " . strtolower(class_basename($soumission));

            //LogActivity::addToLog("Enrégistrement", $message, get_class($soumission), $soumission->id);

            DB::commit();

            return response()->json(['statut' => 'success', 'message' => "Enregistrement réussir", 'data' => new SoumissionsResource($soumission), 'statutCode' => Response::HTTP_CREATED], Response::HTTP_CREATED);
        } catch (\Throwable $th) {

            DB::rollBack();

            //throw $th;
            return response()->json(['statut' => 'error', 'message' => $th->getMessage(), 'errors' => [], 'statutCode' => Response::HTTP_INTERNAL_SERVER_ERROR], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    public function update($soumission, array $attributs): JsonResponse
    {
        DB::beginTransaction();

        try {

            if (!is_object($soumission) && !($soumission = $this->repository->findById($soumission))) throw new Exception("Evaluation de gouvernance inconnue.", 500);

            $this->repository->update($soumission->id, $attributs);

            $soumission->refresh();

            $acteur = Auth::check() ? Auth::user()->nom . " " . Auth::user()->prenom : "Inconnu";

            $message = $message ?? Str::ucfirst($acteur) . " a modifié un " . strtolower(class_basename($soumission));

            //LogActivity::addToLog("Modification", $message, get_class($soumission), $soumission->id);

            DB::commit();

            return response()->json(['statut' => 'success', 'message' => "Enregistrement réussir", 'data' => new SoumissionsResource($soumission), 'statutCode' => Response::HTTP_CREATED], Response::HTTP_CREATED);
        } catch (\Throwable $th) {

            DB::rollBack();

            //throw $th;
            return response()->json(['statut' => 'error', 'message' => $th->getMessage(), 'errors' => [], 'statutCode' => Response::HTTP_INTERNAL_SERVER_ERROR], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
}
