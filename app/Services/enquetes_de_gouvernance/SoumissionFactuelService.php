<?php

namespace App\Services\enquetes_de_gouvernance;

use App\Http\Resources\enquetes_de_gouvernance\SoumissionFactuelResource;
use App\Jobs\AppJob;
use App\Models\enquetes_de_gouvernance\SoumissionFactuel;
use App\Models\Organisation;
use App\Repositories\enquetes_de_gouvernance\EvaluationDeGouvernanceRepository;
use App\Repositories\enquetes_de_gouvernance\FormulaireFactuelDeGouvernanceRepository;
use App\Repositories\enquetes_de_gouvernance\OptionDeReponseGouvernanceRepository;
use App\Repositories\OrganisationRepository;
use App\Repositories\enquetes_de_gouvernance\QuestionFactuelDeGouvernanceRepository;
use App\Repositories\enquetes_de_gouvernance\SoumissionFactuelRepository;
use App\Repositories\enquetes_de_gouvernance\SourceDeVerificationRepository;
use Core\Services\Contracts\BaseService;
use Core\Services\Interfaces\enquetes_de_gouvernance\SoumissionFactuelServiceInterface;
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
 * Interface SoumissionFactuelServiceInterface
 * @package Core\Services\Interfaces
 */
class SoumissionFactuelService extends BaseService implements SoumissionFactuelServiceInterface
{
    use HelperTrait;

    /**
     * @var service
     */
    protected $repository;

    /**
     * SoumissionFactuelRepository constructor.
     *
     * @param SoumissionFactuelRepository $soumissionFactuelRepository
     */
    public function __construct(SoumissionFactuelRepository $soumissionFactuelRepository)
    {
        parent::__construct($soumissionFactuelRepository);
    }

    public function all(array $columns = ['*'], array $relations = []): JsonResponse
    {

        try {

            $soumissions = collect([]);

            if (!(Auth::user()->hasRole('administrateur') || auth()->user()->profilable_type == "App\\Models\\Administrateur")) {
                $soumissions = Auth::user()->programme->soumissions_factuel;
            }

            return response()->json(['statut' => 'success', 'message' => null, 'data' => SoumissionFactuelResource::collection($soumissions), 'statutCode' => Response::HTTP_OK], Response::HTTP_OK);
        } catch (\Throwable $th) {
            return response()->json(['statut' => 'error', 'message' => $th->getMessage(), 'errors' => []], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    public function findById($soumission, array $columns = ['*'], array $relations = [], array $appends = []): JsonResponse
    {
        try {
            if (!is_object($soumission)) {
                $soumission = SoumissionFactuel::findByKey($soumission)->first();
            }

            if (!$soumission) throw new Exception("Soumission de gouvernance inconnue.", 500);

            return response()->json(['statut' => 'success', 'message' => null, 'data' => new SoumissionFactuelResource($soumission), 'statutCode' => Response::HTTP_OK], Response::HTTP_OK);
        } catch (\Throwable $th) {
            return response()->json(['statut' => 'error', 'message' => $th->getMessage(), 'errors' => []], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    public function create(array $attributs): JsonResponse
    {
        DB::beginTransaction();

        try {

            $programme = Auth::user()->programme;

            $attributs = array_merge($attributs, ['programmeId' => $programme->id]);

            if (isset($attributs['evaluationId'])) {
                if (!(($evaluationDeGouvernance = app(EvaluationDeGouvernanceRepository::class)->findById($attributs['evaluationId'])) && $evaluationDeGouvernance->programmeId == $programme->id)) {
                    throw new Exception("Evaluation de gouvernance est introuvable dans le programme.", Response::HTTP_NOT_FOUND);
                }

                $attributs = array_merge($attributs, ['evaluationId' => $evaluationDeGouvernance->id]);
            }

            if (isset($attributs['formulaireDeGouvernanceId'])) {
                if (!(($formulaireDeGouvernance = app(FormulaireFactuelDeGouvernanceRepository::class)->findById($attributs['formulaireDeGouvernanceId'])) && $formulaireDeGouvernance->programmeId == $programme->id)) {
                    throw new Exception("Formulaire de gouvernance est introuvable dans le programme.", Response::HTTP_NOT_FOUND);
                }

                $attributs = array_merge($attributs, ['formulaireFactuelId' => $formulaireDeGouvernance->id]);
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

            if (($soumission = $evaluationDeGouvernance->soumissionFactuel($organisation->id)->first()) && $soumission->statut) {
                return response()->json(['statut' => 'success', 'message' => "Quota des soumissions atteints", 'data' => ['terminer' => true, 'soumission' => new SoumissionFactuelResource($soumission)], 'statutCode' => Response::HTTP_PARTIAL_CONTENT], Response::HTTP_PARTIAL_CONTENT);
            }

            $soumission  = $this->repository->getInstance()->where("evaluationId", $evaluationDeGouvernance->id)->where("organisationId", $organisation->id)->where("formulaireFactuelId", $formulaireDeGouvernance->id)->first();

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


                    throw new Exception("Evaluation de gouvernance : ". json_encode($attributs) . ". Soumission : ". $soumission, 500);


            if (isset($attributs['factuel']) && !empty($attributs['factuel'])) {
                $soumission->fill($attributs['factuel']);
                $soumission->save();
                $soumission->refresh();

                foreach ($attributs['factuel']['response_data'] as $key => $item) {

                    if (!(($questionDeGouvernance = app(QuestionFactuelDeGouvernanceRepository::class)->findById($item['questionId'])) && $questionDeGouvernance->programmeId == $programme->id)) {
                        throw new Exception("Question de gouvernance introuvable dans le programme.", Response::HTTP_NOT_FOUND);
                    }

                    //$option = app(OptionDeReponseGouvernanceRepository::class)->findById($item['optionDeReponseId'])->where("programmeId", $programme->id)->first();
                    $option = app(OptionDeReponseGouvernanceRepository::class)->findById($item['optionDeReponseId']);

                    if (!$option && $option->programmeId == $programme->id) throw new Exception("Cette option n'est pas dans le programme", Response::HTTP_NOT_FOUND);

                    if (isset($item['sourceDeVerificationId']) && (!empty($item['sourceDeVerificationId'])) && $item['sourceDeVerificationId'] != 'null') {
                        //$sourceDeVerification = app(SourceDeVerificationRepository::class)->findById($item['sourceDeVerificationId']);
                        //if (!$sourceDeVerification && $sourceDeVerification->programmeId == $programme->id) throw new Exception("Source de verification inconnue du programme.", Response::HTTP_NOT_FOUND);

                        if (!(($sourceDeVerification = app(SourceDeVerificationRepository::class)->findById($item['sourceDeVerificationId'])) && optional($sourceDeVerification)->programmeId == $programme->id)) {
                            throw new Exception("Source de verification inconnue du programme.", Response::HTTP_NOT_FOUND);
                        }

                        $item = array_merge($item, ['sourceDeVerificationId' => $sourceDeVerification->id, 'sourceDeVerification' => null]);
                    } else if (isset($item['sourceDeVerification']) && (!empty($item['sourceDeVerification'])) && $item['sourceDeVerification'] != 'null') {

                        $item = array_merge($item, ['sourceDeVerificationId' => null, 'sourceDeVerification' => $item['sourceDeVerification']]);
                    } else {

                        $item = array_merge($item, ['sourceDeVerificationId' => null, 'sourceDeVerification' => null]);
                    }

		   /*if (isset($item['description']) && (!empty($item['description'])){
			$item = array_merge($item, ['description' => $item['description']]);
		   }*/

                    $pivot = $option->formulaires_factuel_de_gouvernance()->wherePivot("formulaireFactuelId", $soumission->formulaireDeGouvernance->id)->first()->pivot;
                    //$pivot = $option->formulaires_de_gouvernance()->wherePivot("formulaireFactuelId", $soumission->formulaireDeGouvernance->id)->first()->pivot;

                    throw new Exception("Evaluation de gouvernance : ". json_encode($attributs) . ". Soumission : ". $soumission . ". Piivot : " . $pivot, 500);

                    if (!($reponseDeLaCollecte = $soumission->reponses_de_la_collecte()->where(['programmeId' => $programme->id, 'questionId' => $questionDeGouvernance->id])->first())) {
                        $reponseDeLaCollecte = $soumission->reponses_de_la_collecte()->create(array_merge($item, ['formulaireFactuelId' => $soumission->formulaireDeGouvernance->id, 'optionDeReponseId' => $option->id, 'questionId' => $questionDeGouvernance->id, 'programmeId' => $programme->id, 'point' => $pivot->point, 'preuveIsRequired' => $pivot->preuveIsRequired]));
                    } else {
                        unset($item['questionId']);
                        $reponseDeLaCollecte->fill(array_merge($item, ['formulaireFactuelId' => $soumission->formulaireDeGouvernance->id, 'optionDeReponseId' => $option->id, 'programmeId' => $programme->id, 'point' => $pivot->point, 'preuveIsRequired' => $pivot->preuveIsRequired]));
                        $reponseDeLaCollecte->save();
                    }

                    if (isset($item['preuves']) && !empty($item['preuves'])) {
                        foreach ($item['preuves'] as $preuve) {

                            // On suppose que $preuve est un fichier de type UploadedFile
                            $filenameWithExt = $preuve->getClientOriginalName();
                            $filename = strtolower(str_replace(' ', '-', time() . '-' . $filenameWithExt));

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
            }

            if ($soumission->comite_members !== null) {

                $soumission->refresh();

                $responseCount = $soumission->formulaireDeGouvernance->questions_de_gouvernance()
                    ->whereHas('reponses', function ($query) use ($soumission) {

                        $query->where('soumissionId', $soumission->id)
                            ->where(function ($query) {
                                $query->where(function ($query) {
                                    $query->where('preuveIsRequired', true)
                                        ->whereHas('preuves_de_verification')
                                        ->where(function ($query) {
                                            $query->whereNotNull('sourceDeVerificationId')
                                                ->orWhereNotNull('sourceDeVerification');
                                        });
                                })
                                    ->orWhere('preuveIsRequired', false);
                            });
                    })->count();

                if (($responseCount === $soumission->formulaireDeGouvernance->questions_de_gouvernance->count()) && (isset($attributs['validation']) && $attributs['validation'])) {
                    $soumission->submitted_at = now();
                    $soumission->submittedBy  = Auth::check() ? auth()->id() : null;
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

            return response()->json(['statut' => 'success', 'message' => "Enregistrement réussir", 'data' => new SoumissionFactuelResource($soumission), 'statutCode' => Response::HTTP_CREATED], Response::HTTP_CREATED);
        } catch (\Throwable $th) {

            DB::rollBack();

            //throw $th;
            return response()->json(['statut' => 'error', 'message' => $th->getMessage(), 'errors' => [], 'statutCode' => Response::HTTP_INTERNAL_SERVER_ERROR], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
}
