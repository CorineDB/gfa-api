<?php

namespace App\Services\enquetes_de_gouvernance;

use App\Http\Resources\gouvernance\ActionsAMenerResource;
use App\Http\Resources\gouvernance\CategoriesDeGouvernanceResource;
use App\Http\Resources\enquetes_de_gouvernance\EvaluationsDeGouvernanceResource;
use App\Http\Resources\enquetes_de_gouvernance\formulaires_de_gouvernance_de_perception\ListFormulaireDeGouvernanceDePerceptionResource;
use App\Http\Resources\enquetes_de_gouvernance\formulaires_de_gouvernance_factuel\ListFormulaireDeGouvernanceFactuelResource;
use App\Http\Resources\enquetes_de_gouvernance\OrganisationsEnqueteResource;
use App\Http\Resources\enquetes_de_gouvernance\SoumissionDePerceptionResource;
use App\Http\Resources\enquetes_de_gouvernance\SoumissionFactuelResource;
use App\Http\Resources\gouvernance\FicheDeSyntheseResource;
use App\Http\Resources\gouvernance\FichesDeSyntheseResource;
use App\Http\Resources\gouvernance\PrincipeDeGouvernanceResource;
use App\Http\Resources\gouvernance\RecommandationsResource;
use App\Http\Resources\gouvernance\SoumissionsResource;
use App\Jobs\AppJob;
use App\Jobs\SendInvitationJob;
use App\Mail\InvitationEnqueteDeCollecteEmail;
use App\Models\enquetes_de_gouvernance\EvaluationDeGouvernance as EnqueteEvaluationDeGouvernance;
use App\Models\enquetes_de_gouvernance\SoumissionDePerception;
use App\Models\Organisation;
use App\Repositories\enquetes_de_gouvernance\EvaluationDeGouvernanceRepository;
use App\Repositories\OrganisationRepository;
use App\Traits\Helpers\ConfigueTrait;
use Core\Services\Contracts\BaseService;
use Core\Services\Interfaces\enquetes_de_gouvernance\EvaluationDeGouvernanceServiceInterface;
use Exception;
use App\Traits\Helpers\LogActivity;
use App\Traits\Helpers\SmsTrait;
use Carbon\Carbon;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

/**
 * Interface EvaluationDeGouvernanceServiceInterface
 * @package Core\Services\Interfaces
 */
class EvaluationDeGouvernanceService extends BaseService implements EvaluationDeGouvernanceServiceInterface
{
    use ConfigueTrait, SmsTrait;
    /**
     * @var service
     */
    protected $repository;

    /**
     * EvaluationDeGouvernanceRepository constructor.
     *
     * @param EvaluationDeGouvernanceRepository $evaluationDeGouvernanceRepository
     */
    public function __construct(EvaluationDeGouvernanceRepository $evaluationDeGouvernanceRepository)
    {
        parent::__construct($evaluationDeGouvernanceRepository);
    }

    public function all(array $columns = ['*'], array $relations = []): JsonResponse
    {
        try {
            if (Auth::user()->hasRole('administrateur') || auth()->user()->profilable_type == "App\\Models\\Administrateur") {
                $evaluationsDeGouvernance = $this->repository->all();
            } else if ((Auth::user()->hasRole('organisation') || (get_class(auth()->user()->profilable) == Organisation::class))) {
                $evaluationsDeGouvernance = Auth::user()->programme->enquetes_de_gouvernance()->whereHas('organisations', function ($query) {
                    $query->where('organisationId', Auth::user()->profilable->id);
                })->get();
            } else {
                //$projets = $this->repository->allFiltredBy([['attribut' => 'programmeId', 'operateur' => '=', 'valeur' => auth()->user()->programme->id]]);
                $evaluationsDeGouvernance = Auth::user()->programme->enquetes_de_gouvernance;
            }

            return response()->json(['statut' => 'success', 'message' => null, 'data' => EvaluationsDeGouvernanceResource::collection($evaluationsDeGouvernance), 'statutCode' => Response::HTTP_OK], Response::HTTP_OK);
        } catch (\Throwable $th) {
            return response()->json(['statut' => 'error', 'message' => $th->getMessage(), 'errors' => []], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    public function findById($evaluationDeGouvernance, array $columns = ['*'], array $relations = [], array $appends = []): JsonResponse
    {
        try {
            if (!is_object($evaluationDeGouvernance) && !($evaluationDeGouvernance = $this->repository->findById($evaluationDeGouvernance))) throw new Exception("Evaluation de gouvernance inconnue.", 500);

            return response()->json(['statut' => 'success', 'message' => null, 'data' => new EvaluationsDeGouvernanceResource($evaluationDeGouvernance), 'statutCode' => Response::HTTP_OK], Response::HTTP_OK);
        } catch (\Throwable $th) {
            return response()->json(['statut' => 'error', 'message' => $th->getMessage(), 'errors' => []], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    public function create(array $attributs): JsonResponse
    {
        DB::beginTransaction();

        try {

            if (!isset($attributs["formulaires_de_gouvernance"])) {
                throw new Exception("Veuillez soumettre le formulaire de gouvernance", 400);
            }

            if (!isset($attributs["organisations"])) {
                throw new Exception("Veuillez precisez les organisations pouvant participer a l'evaluation de gouvernance", 400);
            }

            $programme = Auth::user()->programme;

            $attributs = array_merge($attributs, ['programmeId' => $programme->id, 'statut' => -1]);

            $evaluationDeGouvernance = $this->repository->create($attributs);

            $formulaires = $attributs['formulaires_de_gouvernance'];

            if (isset($formulaires['perception'])) {
                $evaluationDeGouvernance->formulaires_de_perception_de_gouvernance()->attach([$attributs['formulaires_de_gouvernance']['perception']]);
            }
            if (isset($formulaires['factuel'])) {
                $evaluationDeGouvernance->formulaires_factuel_de_gouvernance()->attach([$attributs['formulaires_de_gouvernance']['factuel']]);
            }

            $organisationsId = [];
            foreach ($attributs['organisations'] as $organisation) {
                if (!($organisation = app(OrganisationRepository::class)->findById($organisation))) {
                    throw new Exception("Organisation inconnue du programme.", Response::HTTP_NOT_FOUND);
                }

                // Generate the token
                $token = str_replace(['/', '\\', '.'], '', Hash::make(
                    Hash::make($evaluationDeGouvernance->secure_id . $organisation->secure_id) .
                        Hash::make(strtotime(now()))
                ));

                // Add to the array in the correct format
                $organisationsId[$organisation->id] = ['token' => $token];
            }

            // Attach organisations with the additional pivot data
            $evaluationDeGouvernance->organisations()->attach($organisationsId);

            $acteur = Auth::check() ? Auth::user()->nom . " " . Auth::user()->prenom : "Inconnu";

            $message = $message ?? Str::ucfirst($acteur) . " a créé un " . strtolower(class_basename($evaluationDeGouvernance));

            //LogActivity::addToLog("Enrégistrement", $message, get_class($evaluationDeGouvernance), $evaluationDeGouvernance->id);

            DB::commit();

            AppJob::dispatch(
                Artisan::call('gouvernance:update-evaluation-statuses')
            )->delay(now()->addSeconds(30)); // Optionally add additional delay at dispatch time->addSeconds(30)

            return response()->json(['statut' => 'success', 'message' => "Enregistrement réussir", 'data' => new EvaluationsDeGouvernanceResource($evaluationDeGouvernance), 'statutCode' => Response::HTTP_CREATED], Response::HTTP_CREATED);
        } catch (\Throwable $th) {

            DB::rollBack();

            //throw $th;
            return response()->json(['statut' => 'error', 'message' => $th->getMessage(), 'errors' => [], 'statutCode' => Response::HTTP_INTERNAL_SERVER_ERROR], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    public function update($evaluationDeGouvernance, array $attributs): JsonResponse
    {
        DB::beginTransaction();

        try {

            if (!is_object($evaluationDeGouvernance) && !($evaluationDeGouvernance = $this->repository->findById($evaluationDeGouvernance))) throw new Exception("Evaluation de gouvernance inconnue.", 500);

            $this->repository->update($evaluationDeGouvernance->id, $attributs);

            if ($evaluationDeGouvernance->wasChanged('debut')) {

                $debut = Carbon::parse($evaluationDeGouvernance->debut); // Convertit en Carbon

                if ($debut->isAfter(today())) {

                    $evaluationDeGouvernance->update(['statut' => 0]);
                }
            }

            if ($evaluationDeGouvernance->isDirty('debut')) {

                if ($evaluationDeGouvernance->debut->after(today())) {

                    $evaluationDeGouvernance->statut = -1;
                    $evaluationDeGouvernance->save();
                } else {
                    $evaluationDeGouvernance->statut = 0;
                    $evaluationDeGouvernance->save();
                }
            }

            $evaluationDeGouvernance->refresh();

            if ($evaluationDeGouvernance->statut <= 0) {
                $evaluationDeGouvernance->organisations()->syncWithoutDetaching($attributs['organisations']);

                // Vérifier si toutes les organisations ont un token
                foreach ($evaluationDeGouvernance->organisations as $organisation) {
                    if (empty($organisation->pivot->token)) {
                        // Générer un nouveau token
                        $token = str_replace(['/', '\\', '.'], '', Hash::make(
                            Hash::make($evaluationDeGouvernance->secure_id . $organisation->secure_id) .
                                Hash::make(strtotime(now()))
                        ));

                        // Mettre à jour le pivot
                        $evaluationDeGouvernance->organisations()->updateExistingPivot(
                            $organisation->id,
                            ['token' => $token]
                        );
                    }
                }
            }

            if (isset($attributs['formulaires_de_gouvernance'])) {
                $formulaires = $attributs['formulaires_de_gouvernance'];

                if ($evaluationDeGouvernance->statut == -1) {

                    if (isset($formulaires['perception'])) {
                        $evaluationDeGouvernance->formulaires_de_perception_de_gouvernance()->syncWithoutDetaching([$formulaires['perception']]);
                    }
                    if (isset($formulaires['factuel'])) {
                        $evaluationDeGouvernance->formulaires_factuel_de_gouvernance()->syncWithoutDetaching([$formulaires['factuel']]);
                    }
                } else if ($evaluationDeGouvernance->statut == 0) {
                    if (isset($formulaires['factuel'])) {
                        if ($evaluationDeGouvernance->soumissionsFactuel->count() == 0) {
                            $evaluationDeGouvernance->formulaires_factuel_de_gouvernance()->syncWithoutDetaching([$formulaires['factuel']]);
                        }
                    }
                    if (isset($formulaires['perception'])) {
                        if ($evaluationDeGouvernance->soumissionsDePerception->count() == 0) {
                            $evaluationDeGouvernance->formulaires_de_perception_de_gouvernance()->syncWithoutDetaching([$formulaires['perception']]);
                        }
                    }
                }
            }

            $acteur = Auth::check() ? Auth::user()->nom . " " . Auth::user()->prenom : "Inconnu";

            $message = $message ?? Str::ucfirst($acteur) . " a modifié un " . strtolower(class_basename($evaluationDeGouvernance));

            //LogActivity::addToLog("Modification", $message, get_class($evaluationDeGouvernance), $evaluationDeGouvernance->id);

            DB::commit();

            AppJob::dispatch(
                Artisan::call('gouvernance:update-evaluation-statuses')
            )->delay(now()->addSeconds(30)); // Optionally add additional delay at dispatch time->addSeconds(30)

            return response()->json(['statut' => 'success', 'message' => "Enregistrement réussir", 'data' => new EvaluationsDeGouvernanceResource($evaluationDeGouvernance), 'statutCode' => Response::HTTP_CREATED], Response::HTTP_CREATED);
        } catch (\Throwable $th) {

            DB::rollBack();

            //throw $th;
            return response()->json(['statut' => 'error', 'message' => $th->getMessage(), 'errors' => [], 'statutCode' => Response::HTTP_INTERNAL_SERVER_ERROR], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * Renvoie les organisations liées à une evaluation de gouvernance
     *
     * @param mixed $evaluationDeGouvernance
     * @param array $columns
     * @param array $relations
     * @param array $appends
     * @return JsonResponse
     */
    public function organisations($evaluationDeGouvernance, array $columns = ['*'], array $relations = [], array $appends = []): JsonResponse
    {
        try {
            if (!is_object($evaluationDeGouvernance) && !($evaluationDeGouvernance = $this->repository->findById($evaluationDeGouvernance))) throw new Exception("Evaluation de gouvernance inconnue.", 500);

            return response()->json(['statut' => 'success', 'message' => null, 'data' => OrganisationsEnqueteResource::collection($evaluationDeGouvernance->organisations), 'statutCode' => Response::HTTP_OK], Response::HTTP_OK);
        } catch (\Throwable $th) {
            return response()->json(['statut' => 'error', 'message' => $th->getMessage(), 'errors' => []], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * Liste des soumissions d'une evaluation de gouvernance
     *
     * return JsonResponse
     */
    public function soumissions_enquete($evaluationDeGouvernance): JsonResponse
    {
        try {
            if (!is_object($evaluationDeGouvernance) && !($evaluationDeGouvernance = $this->repository->findById($evaluationDeGouvernance))) throw new Exception("Evaluation de gouvernance inconnue.", 500);

            $url = config("app.url");

            // If the URL is localhost, append the appropriate IP address and port
            if (strpos($url, 'localhost') == false) {
                $url = config("app.organisation_url");
            }

            $group_soumissions = [];

            if ((Auth::user()->hasRole('organisation') || (get_class(auth()->user()->profilable) == Organisation::class))) {

                $organisation = Auth::user()->profilable;

                $evaluationDeGouvernance
                    ->load([
                        "soumissionsFactuel" => function ($query) use ($organisation) {
                            $query->where('organisationId', $organisation->id);
                        },
                        "soumissionsDePerception" => function ($query) use ($organisation) {
                            $query->where('organisationId', $organisation->id);
                        }
                    ]);

                if (!$organisationEvaluation = $evaluationDeGouvernance->organisations()->where('organisations.id', $organisation->id)->first()) {
                    // declencher une exception
                    throw new Exception("L'organisation n'est pas liée aucune évaluation de gouvernance.", 404);
                }

                $formFactuel = $evaluationDeGouvernance->soumissionsFactuel->first();

                $group_soumissions = array_merge([
                    "id"                    => $organisation->secure_id,
                    'nom'                   => optional($organisation->user)->nom ?? null,
                    'sigle'                 => $organisation->sigle,
                    'code'                  => $organisation->code,
                    'nom_point_focal'       => $organisation->nom_point_focal,
                    'prenom_point_focal'    => $organisation->prenom_point_focal,
                    'contact_point_focal'   => $organisation->contact_point_focal,
                    "lien_factuel_token"          => $organisationEvaluation->pivot->token,
                    "lien_perception_token"       => $organisationEvaluation->pivot->token,
                    'organisation_token'          => $organisationEvaluation->pivot->token, // <-- Ajouté
                    'nbreDeParticipants'          => $organisationEvaluation->pivot->nbreParticipants ?? 0,
                    'participants'                => $organisationEvaluation->pivot->participants ? json_decode($organisationEvaluation->pivot->participants) : 0,
                    //'participants' => $participantsList, // <-- NOUVEAU : La liste détaillée des participants

                    'pourcentage_evolution' => $organisation->getSubmissionRateAttribute($evaluationDeGouvernance->id),
                ], ['factuel' => $formFactuel ? new SoumissionFactuelResource($formFactuel) : null, 'perception' => SoumissionDePerceptionResource::collection($evaluationDeGouvernance->soumissionsDePerception)]);
            } else {
                $group_soumissions = $evaluationDeGouvernance->organisations()
                    ->with(['sousmissions_enquete_factuel', 'sousmissions_enquete_de_perception'])
                    ->get()
                    ->map(function ($organisation) use ($evaluationDeGouvernance, $url) {

                        $soumissionsFactuel = SoumissionFactuelResource::collection($organisation->sousmissions_enquete_factuel->where('evaluationId', $evaluationDeGouvernance->id));
                        $soumissionsDePerception = SoumissionDePerceptionResource::collection($organisation->sousmissions_enquete_de_perception->where('evaluationId', $evaluationDeGouvernance->id));

                        // Fetch submissions for this organization
                        /* $soumissionsFactuel = $organisation->sousmissions_enquete_factuel()
                            ->where('evaluationId', $evaluationDeGouvernance->id)->get()
                            ->map(function ($soumissions) {
                                return SoumissionFactuelResource::collection($soumissions);
                            }); // Group submissions by type

                        $soumissionsDePerception = $organisation->sousmissions_enquete_de_perception
                            ->where('evaluationId', $evaluationDeGouvernance->id)
                            ->map(function ($soumissions) {
                                return SoumissionDePerceptionResource::collection($soumissions);
                            }); // Group submissions by type */

                        return array_merge([
                            "id"                    => $organisation->secure_id,
                            'nom'                   => optional($organisation->user)->nom ?? null,
                            'sigle'                 => $organisation->sigle,
                            'code'                  => $organisation->code,
                            'nom_point_focal'       => $organisation->nom_point_focal,
                            'prenom_point_focal'    => $organisation->prenom_point_focal,
                            'contact_point_focal'   => $organisation->contact_point_focal,
                            'pourcentage_evolution' => $organisation->getSubmissionRateAttribute($evaluationDeGouvernance->id),

                            "lien_factuel_token"          => $organisation->pivot->token,
                            "lien_perception_token"       => $organisation->pivot->token,
                            'organisation_token'          => $organisation->pivot->token, // <-- Ajouté

                            "lien_factuel"          => $url . "/tools-factuel/{$organisation->pivot->token}",
                            "lien_perception"       => $url . "/tools-perception/{$organisation->pivot->token}",
                            'nbreDeParticipants'          => $organisation->pivot->nbreParticipants ?? 0,
                            'nbreParticipants'            => $organisation->pivot->nbreParticipants ?? 0,
                            'participants'                => $organisation->pivot->participants ? json_decode($organisation->pivot->participants) : 0,
                        ], ['factuel' => $soumissionsFactuel, 'perception' => $soumissionsDePerception]);
                    });
            }

            return response()->json(['statut' => 'success', 'message' => null, 'data' => $group_soumissions, 'statutCode' => Response::HTTP_OK], Response::HTTP_OK);
        } catch (\Throwable $th) {
            return response()->json(['statut' => 'error', 'message' => $th->getMessage(), 'errors' => []], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * Liste des soumissions factuelles d'une evaluation de gouvernance
     *
     * return JsonResponse
     */
    public function soumissions_enquete_factuel($evaluationDeGouvernance): JsonResponse
    {
        try {
            if (!is_object($evaluationDeGouvernance) && !($evaluationDeGouvernance = $this->repository->findById($evaluationDeGouvernance))) {
                throw new Exception("Evaluation de gouvernance inconnue.", 500);
            }

            $url = config("app.url");
            if (strpos($url, 'localhost') == false) {
                $url = config("app.organisation_url");
            }

            $group_soumissions = [];

            // Cas: Utilisateur de type Organisation
            if ((Auth::user()->hasRole('organisation') || (get_class(auth()->user()->profilable) == Organisation::class))) {
                $organisation = Auth::user()->profilable;

                // Charger uniquement les soumissions factuelles de l'organisation
                                    $soumissionsFactuel = $evaluationDeGouvernance->soumissionsFactuel()
                                        ->where('organisationId', $organisation->id)
                                        ->get();

                                    if (!$organisationEvaluation = $evaluationDeGouvernance->organisations()->where('organisations.id', $organisation->id)->first()) {
                                        // declencher une exception
                                        throw new Exception("L'organisation n'est pas liée aucune évaluation de gouvernance.", 404);
                                    }
                $group_soumissions = [
                    "id" => $organisation->secure_id,
                    'nom' => optional($organisation->user)->nom ?? null,
                    'sigle' => $organisation->sigle,
                    'code' => $organisation->code,
                    'nom_point_focal' => $organisation->nom_point_focal,
                    'prenom_point_focal' => $organisation->prenom_point_focal,
                    'contact_point_focal' => $organisation->contact_point_focal,
                    "lien_factuel_token"          => $organisationEvaluation->pivot->token,
                    'pourcentage_evolution' => $organisation->getSubmissionRateAttribute($evaluationDeGouvernance->id),
                    'factuel' => $soumissionsFactuel->isNotEmpty() ? new SoumissionFactuelResource($soumissionsFactuel->first()) : null
                ];
            } else {
                // Cas: Autres utilisateurs (Admin, Programme, etc.)
                $group_soumissions = $evaluationDeGouvernance->organisations()
                    ->with(['sousmissions_enquete_factuel'])
                    ->get()
                    ->map(function ($organisation) use ($evaluationDeGouvernance, $url) {

                        $soumissionsFactuel = $organisation->sousmissions_enquete_factuel
                            ->where('evaluationId', $evaluationDeGouvernance->id);

                        return [
                            "id" => $organisation->secure_id,
                            'nom' => optional($organisation->user)->nom ?? null,
                            'sigle' => $organisation->sigle,
                            'code' => $organisation->code,
                            'nom_point_focal' => $organisation->nom_point_focal,
                            'prenom_point_focal' => $organisation->prenom_point_focal,
                            'contact_point_focal' => $organisation->contact_point_focal,
                            'pourcentage_evolution' => $organisation->getSubmissionRateAttribute($evaluationDeGouvernance->id),
                            "lien_factuel_token"          => $organisation->pivot->token,
                            "lien_factuel" => $url . "/tools-factuel/{$organisation->pivot->token}",
                            'factuel' => $soumissionsFactuel->isNotEmpty() ? new SoumissionFactuelResource($soumissionsFactuel->first()) : null
                        ];
                    });
            }

            return response()->json([
                'statut' => 'success',
                'message' => null,
                'data' => $group_soumissions,
                'statutCode' => Response::HTTP_OK
            ], Response::HTTP_OK);
        } catch (\Throwable $th) {
            return response()->json([
                'statut' => 'error',
                'message' => $th->getMessage(),
                'errors' => [],
                'statutCode' => Response::HTTP_INTERNAL_SERVER_ERROR
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * Liste des soumissions de perception d'une evaluation de gouvernance
     *
     * return JsonResponse
     */
    public function soumissions_enquete_de_perception($evaluationDeGouvernance): JsonResponse
    {
        try {
            if (!is_object($evaluationDeGouvernance) && !($evaluationDeGouvernance = $this->repository->findById($evaluationDeGouvernance))) {
                throw new Exception("Evaluation de gouvernance inconnue.", 500);
            }

            $url = config("app.url");
            if (strpos($url, 'localhost') == false) {
                $url = config("app.organisation_url");
            }

            $group_soumissions = [];

            // Cas: Utilisateur de type Organisation
            if ((Auth::user()->hasRole('organisation') || (get_class(auth()->user()->profilable) == Organisation::class))) {
                $organisation = Auth::user()->profilable;

                // Charger uniquement les soumissions de perception de l'organisation
                $soumissionsDePerception = $evaluationDeGouvernance->soumissionsDePerception()
                    ->where('organisationId', $organisation->id)
                    ->get();

                if (!$organisationEvaluation = $evaluationDeGouvernance->organisations()->where('organisations.id', $organisation->id)->first()) {
                    // declencher une exception
                    throw new Exception("L'organisation n'est pas liée aucune évaluation de gouvernance.", 404);
                }

                $group_soumissions = [
                    "id" => $organisation->secure_id,
                    'nom' => optional($organisation->user)->nom ?? null,
                    'sigle' => $organisation->sigle,
                    'code' => $organisation->code,
                    'nom_point_focal' => $organisation->nom_point_focal,
                    'prenom_point_focal' => $organisation->prenom_point_focal,
                    'contact_point_focal' => $organisation->contact_point_focal,
                    "lien_perception_token"       => $organisationEvaluation->pivot->token,
                    'nbreDeParticipants'          => $organisationEvaluation->pivot->nbreParticipants ?? 0,
                    'participants'                => $organisationEvaluation->pivot->participants ? json_decode($organisationEvaluation->pivot->participants) : 0,
                    'pourcentage_evolution_des_soumissions_de_perception' => $organisation->getPerceptionSubmissionsCompletionAttribute($evaluationDeGouvernance->id),
                    'perception' => SoumissionDePerceptionResource::collection($soumissionsDePerception)
                ];
            } else {
                // Cas: Autres utilisateurs (Admin, Programme, etc.)
                $group_soumissions = $evaluationDeGouvernance->organisations()
                    ->with(['sousmissions_enquete_de_perception'])
                    ->get()
                    ->map(function ($organisation) use ($evaluationDeGouvernance, $url) {

                        $soumissionsDePerception = $organisation->sousmissions_enquete_de_perception
                            ->where('evaluationId', $evaluationDeGouvernance->id);

                        return [
                            "id" => $organisation->secure_id,
                            'nom' => optional($organisation->user)->nom ?? null,
                            'sigle' => $organisation->sigle,
                            'code' => $organisation->code,
                            'nom_point_focal' => $organisation->nom_point_focal,
                            'prenom_point_focal' => $organisation->prenom_point_focal,
                            'contact_point_focal' => $organisation->contact_point_focal,
                            'pourcentage_evolution_des_soumissions_de_perception' => $organisation->getPerceptionSubmissionsCompletionAttribute($evaluationDeGouvernance->id),
                            "lien_perception_token"       => $organisation->pivot->token,
                            "lien_perception" => $url . "/tools-perception/{$organisation->pivot->token}",
                            'perception' => SoumissionDePerceptionResource::collection($soumissionsDePerception)
                        ];
                    });
            }

            return response()->json([
                'statut' => 'success',
                'message' => null,
                'data' => $group_soumissions,
                'statutCode' => Response::HTTP_OK
            ], Response::HTTP_OK);
        } catch (\Throwable $th) {
            return response()->json([
                'statut' => 'error',
                'message' => $th->getMessage(),
                'errors' => [],
                'statutCode' => Response::HTTP_INTERNAL_SERVER_ERROR
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * Liste des soumissions d'une evaluation de gouvernance
     *
     * return JsonResponse
     */
    public function soumissionsDePerception($evaluationDeGouvernance, array $columns = ['*'], array $relations = [], array $appends = []): JsonResponse
    {
        try {
            if (!is_object($evaluationDeGouvernance) && !($evaluationDeGouvernance = $this->repository->findById($evaluationDeGouvernance))) throw new Exception("Evaluation de gouvernance inconnue.", 500);

            $url = config("app.url");

            // If the URL is localhost, append the appropriate IP address and port
            if (strpos($url, 'localhost') == false) {
                $url = config("app.organisation_url");
            }

            if ((Auth::user()->hasRole('administrateur') || auth()->user()->profilable_type == "App\\Models\\Administrateur")) {
                $group_soumissions = [];
            } else if ((Auth::user()->hasRole('organisation') || (get_class(auth()->user()->profilable) == Organisation::class))) {

                $organisation = Auth::user()->profilable;

                $group_soumissions = $evaluationDeGouvernance->soumissionsDePerception()->where('organisationId', $organisation->id)
                    ->get()->map(function ($soumissions) {
                        return SoumissionDePerceptionResource::collection($soumissions);
                    });

                $group_soumissions = array_merge([
                    "id"                    => $organisation->secure_id,
                    'nom'                   => optional($organisation->user)->nom ?? null,
                    'sigle'                 => $organisation->sigle,
                    'code'                  => $organisation->code,
                    'nom_point_focal'       => $organisation->nom_point_focal,
                    'prenom_point_focal'    => $organisation->prenom_point_focal,
                    'contact_point_focal'   => $organisation->contact_point_focal,
                    'pourcentage_evolution_des_soumissions_de_perception'   => $organisation->getPerceptionSubmissionsCompletionAttribute($evaluationDeGouvernance->id)
                ], $group_soumissions->toArray());
            } else {

                $group_soumissions = $evaluationDeGouvernance->organisations()
                    ->with('soumissions') // Load the associated organisations
                    ->get()->map(function ($organisation) use ($evaluationDeGouvernance, $url) {
                        // Fetch submissions for this organization
                        $types_soumissions = $organisation->soumissions
                            ->where('evaluationId', $evaluationDeGouvernance->id)
                            ->groupBy('type')->map(function ($soumissions, $type) {
                                return SoumissionsResource::collection($soumissions);
                            }); // Group submissions by type

                        return array_merge([
                            "id"                    => $organisation->secure_id,
                            'nom'                   => optional($organisation->user)->nom ?? null,
                            'sigle'                 => $organisation->sigle,
                            'code'                  => $organisation->code,
                            'nom_point_focal'       => $organisation->nom_point_focal,
                            'prenom_point_focal'    => $organisation->prenom_point_focal,
                            'contact_point_focal'   => $organisation->contact_point_focal,
                            "lien_factuel"          => $url . "/tools-factuel/{$organisation->pivot->token}",
                            "lien_perception"       => $url . "/tools-perception/{$organisation->pivot->token}",
                        ], $types_soumissions->toArray());
                    });
            }
            return response()->json(['statut' => 'success', 'message' => null, 'data' => $group_soumissions, 'statutCode' => Response::HTTP_OK], Response::HTTP_OK);
        } catch (\Throwable $th) {
            return response()->json(['statut' => 'error', 'message' => $th->getMessage(), 'errors' => []], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * Liste des soumissions d'une evaluation de gouvernance
     *
     * return JsonResponse
     */
    public function soumissions($evaluationDeGouvernance, array $columns = ['*'], array $relations = [], array $appends = []): JsonResponse
    {
        try {
            if (!is_object($evaluationDeGouvernance) && !($evaluationDeGouvernance = $this->repository->findById($evaluationDeGouvernance))) throw new Exception("Evaluation de gouvernance inconnue.", 500);

            $url = config("app.url");

            // If the URL is localhost, append the appropriate IP address and port
            if (strpos($url, 'localhost') == false) {
                $url = config("app.organisation_url");
            }

            if ((Auth::user()->hasRole('administrateur') || auth()->user()->profilable_type == "App\\Models\\Administrateur")) {
                $group_soumissions = [];
            } else if ((Auth::user()->hasRole('organisation') || (get_class(auth()->user()->profilable) == Organisation::class))) {

                $organisation = Auth::user()->profilable;

                $group_soumissions = $evaluationDeGouvernance->soumissions()->where('organisationId', $organisation->id)
                    ->get()->groupBy('type')->map(function ($soumissions, $type) {
                        if ($type === 'perception') {
                            return SoumissionsResource::collection($soumissions);
                        }
                        return new SoumissionsResource($soumissions->first());
                    });

                $group_soumissions = array_merge([
                    "id"                    => $organisation->secure_id,
                    'nom'                   => optional($organisation->user)->nom ?? null,
                    'sigle'                 => $organisation->sigle,
                    'code'                  => $organisation->code,
                    'nom_point_focal'       => $organisation->nom_point_focal,
                    'prenom_point_focal'    => $organisation->prenom_point_focal,
                    'contact_point_focal'   => $organisation->contact_point_focal,
                    'pourcentage_evolution_des_soumissions_de_perception'   => $organisation->getPerceptionSubmissionsCompletionAttribute($evaluationDeGouvernance->id),
                    // Ajout du token de l'organisation pour le cas de l'utilisateur de type organisation
                    'organisation_token' => $organisation->pivot->token, // <-- Ajouté avec le nom demandé
                    'nbreParticipants'   => $organisation->pivot->nbreParticipants,
                    'participants'       => $organisation->pivot->participants ? json_decode($organisation->pivot->participants, true) : []
                ], $group_soumissions->toArray());
            } else {

                /*
                    $organisation_soumissions = $evaluationDeGouvernance->soumissions()
                        ->with('organisation') // Load the associated organisations
                        ->get()->groupBy('organisationId')->map(function ($group) {
                            return $group->groupBy('type'); // Then group by type within each organisation
                        });

                    $group_soumissions = $organisation_soumissions->map(function ($type_soumissions, $organisationId) {

                        $organisation = app(OrganisationRepository::class)->findById($organisationId);

                        $types_de_soumission = $type_soumissions->map(function ($soumissions, $type) {

                            return SoumissionsResource::collection($soumissions);
                            if ($type === 'perception') {
                                return SoumissionsResource::collection($soumissions);
                            } else {
                                return new SoumissionsResource($soumissions->first());
                            }
                        });

                        return array_merge([
                            "id"                    => $organisation->secure_id,
                            'nom'                   => optional($organisation->user)->nom ?? null,
                            'sigle'                 => $organisation->sigle,
                            'code'                  => $organisation->code,
                            'nom_point_focal'       => $organisation->nom_point_focal,
                            'prenom_point_focal'    => $organisation->prenom_point_focal,
                            'contact_point_focal'   => $organisation->contact_point_focal
                        ], $types_de_soumission->toArray());
                    })->values();
                */

                $group_soumissions = $evaluationDeGouvernance->organisations()
                    ->with('soumissions') // Load the associated organisations
                    ->get()->map(function ($organisation) use ($evaluationDeGouvernance, $url) {
                        // Fetch submissions for this organization
                        $types_soumissions = $organisation->soumissions
                            ->where('evaluationId', $evaluationDeGouvernance->id)
                            ->groupBy('type')->map(function ($soumissions, $type) {
                                return SoumissionsResource::collection($soumissions);
                            }); // Group submissions by type

                        return array_merge([
                            "id"                    => $organisation->secure_id,
                            'nom'                   => optional($organisation->user)->nom ?? null,
                            'sigle'                 => $organisation->sigle,
                            'code'                  => $organisation->code,
                            'nom_point_focal'       => $organisation->nom_point_focal,
                            'prenom_point_focal'    => $organisation->prenom_point_focal,
                            'contact_point_focal'   => $organisation->contact_point_focal,
                            "lien_factuel"          => $url . "/tools-factuel/{$organisation->pivot->token}",
                            "lien_perception"       => $url . "/tools-perception/{$organisation->pivot->token}",
                            'organisation_token'    => $organisation->pivot->token, // <-- Ajouté
                            'nbreParticipants'      => $organisation->pivot->nbreParticipants,
                            'participants'          => $organisation->pivot->participants ? json_decode($organisation->pivot->participants, true) : []
                        ], $types_soumissions->toArray());
                    });
            }
            return response()->json(['statut' => 'success', 'message' => null, 'data' => $group_soumissions, 'statutCode' => Response::HTTP_OK], Response::HTTP_OK);
        } catch (\Throwable $th) {
            return response()->json(['statut' => 'error', 'message' => $th->getMessage(), 'errors' => []], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * Liste des soumissions d'une evaluation de gouvernance
     *
     * return JsonResponse
     */
    public function fiches_de_synthese($evaluationDeGouvernance, array $columns = ['*'], array $relations = [], array $appends = []): JsonResponse
    {
        try {
            if (!is_object($evaluationDeGouvernance) && !($evaluationDeGouvernance = $this->repository->findById($evaluationDeGouvernance))) throw new Exception("Evaluation de gouvernance inconnue.", 500);

            if ((Auth::user()->hasRole('administrateur') || auth()->user()->profilable_type == "App\\Models\\Administrateur")) {
                $fiches_de_synthese = [];
            } else if ((Auth::user()->hasRole('organisation') || (get_class(auth()->user()->profilable) == Organisation::class))) {

                $organisation = Auth::user()->profilable;

                $fiches_de_synthese = $evaluationDeGouvernance->fiches_de_synthese()->where('organisationId', $organisation->id)
                    ->get()->groupBy(['type'])->map(function ($fiches_de_synthese, $type) {
                        return new FicheDeSyntheseResource($fiches_de_synthese->first());
                    });

                $fiches_de_synthese = array_merge([
                    "id"                    => $organisation->secure_id,
                    'nom'                   => optional($organisation->user)->nom ?? null,
                    'sigle'                 => $organisation->sigle,
                    'code'                  => $organisation->code,
                    'nom_point_focal'       => $organisation->nom_point_focal,
                    'prenom_point_focal'    => $organisation->prenom_point_focal,
                    'contact_point_focal'   => $organisation->contact_point_focal,
                    'profile_de_gouvernance'   => $organisation->profiles($evaluationDeGouvernance->id)->first()->resultat_synthetique ?? []

                ], $fiches_de_synthese->toArray());
            } else {
                $rapportsEvaluationParOrganisation = $evaluationDeGouvernance->fiches_de_synthese->groupBy(['organisationId', 'type']);

                $fiches_de_synthese = $rapportsEvaluationParOrganisation->map(function ($rapportEvaluationParOrganisation, $organisationId) use ($evaluationDeGouvernance) {

                    $organisation = app(OrganisationRepository::class)->findById($organisationId);

                    $fiches_de_synthese = $rapportEvaluationParOrganisation->map(function ($fiches_de_synthese, $type) {
                        if ($fiches_de_synthese) {
                            return new FicheDeSyntheseResource($fiches_de_synthese->first());
                        }
                    })->filter();

                    return array_merge([
                        "id"                    => $organisation->secure_id,
                        'nom'                   => optional($organisation->user)->nom ?? null,
                        'sigle'                 => $organisation->sigle,
                        'code'                  => $organisation->code,
                        'nom_point_focal'       => $organisation->nom_point_focal,
                        'prenom_point_focal'    => $organisation->prenom_point_focal,
                        'contact_point_focal'   => $organisation->contact_point_focal,
                        'profile_de_gouvernance'   => optional($organisation->profiles($evaluationDeGouvernance->id)->first())->resultat_synthetique ?? []
                    ], $fiches_de_synthese->toArray());
                })->values();
            }

            return response()->json(['statut' => 'success', 'message' => null, 'data' => $fiches_de_synthese, 'statutCode' => Response::HTTP_OK], Response::HTTP_OK);
        } catch (\Throwable $th) {
            return response()->json(['statut' => 'error', 'message' => $th->getMessage(), 'errors' => []], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }



    // Helper function to determine score range
    private function getScoreRange($score)
    {
        if ($score <= 1 && $score > 0.75) {
            return ']0,75 - 1]';
        } elseif ($score <= 0.75 && $score > 0.50) {
            return ']0,50 - 0,75]';
        } elseif ($score <= 0.50 && $score > 0.25) {
            return ']0,25 - 0,50]';
        } else {
            return '[0 - 0,25]';
        }
    }

    private function getCategories($categories, $fiche, $syntheseCategories)
    {
        return collect($categories)->map(function ($category) use ($fiche, $syntheseCategories) {

            if (!isset($category['score_ranges'])) {
                $categoryScoreRanges = [
                    '0-0.25' => ['organisations' => []],
                    '0.25-0.50' => ['organisations' => []],
                    '0.50-0.75' => ['organisations' => []],
                    '0.75-1' => ['organisations' => []],
                ];
            } else {
                $categoryScoreRanges = $category['score_ranges'];
            }

            foreach ($syntheseCategories as $key => $syntheseCategorie) {

                if ($syntheseCategorie['id'] == $category->secure_id) {

                    if (isset($syntheseCategorie['score_factuel'])) {

                        $scoreFactuel = $syntheseCategorie['score_factuel'];

                        // Logic for organizing into score ranges (adjust based on actual criteria)
                        if ($scoreFactuel >= 0 && $scoreFactuel <= 0.25) {
                            $categoryScoreRanges['0-0.25']['organisations'][] = ['id' => $fiche->organisation->secure_id, 'nom' => optional($fiche->organisation->user)->nom ?? null, 'sigle' => $fiche->organisation->sigle, 'score_factuel' => $scoreFactuel]; // Assuming you have this info in the fiche
                        } elseif ($scoreFactuel > 0.25 && $scoreFactuel <= 0.50) {
                            $categoryScoreRanges['0.25-0.50']['organisations'][] = ['id' => $fiche->organisation->secure_id, 'nom' => optional($fiche->organisation->user)->nom ?? null, 'sigle' => $fiche->organisation->sigle, 'score_factuel' => $scoreFactuel];
                        } elseif ($scoreFactuel > 0.50 && $scoreFactuel <= 0.75) {
                            $categoryScoreRanges['0.50-0.75']['organisations'][] = ['id' => $fiche->organisation->secure_id, 'nom' => optional($fiche->organisation->user)->nom ?? null, 'sigle' => $fiche->organisation->sigle, 'score_factuel' => $scoreFactuel];
                        } elseif ($scoreFactuel > 0.75 && $scoreFactuel <= 1) {
                            $categoryScoreRanges['0.75-1']['organisations'][] = ['id' => $fiche->organisation->secure_id, 'nom' => optional($fiche->organisation->user)->nom ?? null, 'sigle' => $fiche->organisation->sigle, 'score_factuel' => $scoreFactuel];
                        }

                        if ($category->categories_de_gouvernance->count() && isset($syntheseCategorie['categories_de_gouvernance'])) {
                            $category->categories_de_gouvernance = $this->getCategories($category->categories_de_gouvernance, $fiche, $syntheseCategorie['categories_de_gouvernance']);
                        }
                    }

                    if ($category->questions_de_gouvernance->count() && isset($syntheseCategorie['questions_de_gouvernance'])) {
                        $category->questions_de_gouvernance = $this->getQuestions($category->questions_de_gouvernance, $fiche, $syntheseCategorie['questions_de_gouvernance']);
                    }
                }
            }

            $category->score_ranges = $categoryScoreRanges;

            return new CategoriesDeGouvernanceResource($category);
        })->values();
    }

    private function getQuestions($questions, $fiche, $questionsOperationnelle)
    {
        return collect($questions)->map(function ($question) use ($fiche, $questionsOperationnelle) {

            if (!isset($question['score_ranges'])) {
                $questionScoreRanges = [
                    '0-0.25' => ['organisations' => []],
                    '0.25-0.50' => ['organisations' => []],
                    '0.50-0.75' => ['organisations' => []],
                    '0.75-1' => ['organisations' => []],
                ];
            } else {
                $questionScoreRanges = $question['score_ranges'];
            }

            foreach ($questionsOperationnelle as $questionOperationnelle) {

                if ($questionOperationnelle['id'] == $question->secure_id) {

                    //if(isset($question['reponse'])){

                    $point = $questionOperationnelle['reponse']['point'];

                    // Logic for organizing into score ranges (adjust based on actual criteria)
                    if ($point >= 0 && $point <= 0.25) {
                        $questionScoreRanges['0-0.25']['organisations'][] = ['id' => $fiche->organisation->secure_id, 'nom' => optional($fiche->organisation->user)->nom ?? null, 'sigle' => $fiche->organisation->sigle, 'point' => $point]; // Assuming you have this info in the fiche
                    } elseif ($point > 0.25 && $point <= 0.50) {
                        $questionScoreRanges['0.25-0.50']['organisations'][] = ['id' => $fiche->organisation->secure_id, 'nom' => optional($fiche->organisation->user)->nom ?? null, 'sigle' => $fiche->organisation->sigle, 'point' => $point];
                    } elseif ($point > 0.50 && $point <= 0.75) {
                        $questionScoreRanges['0.50-0.75']['organisations'][] = ['id' => $fiche->organisation->secure_id, 'nom' => optional($fiche->organisation->user)->nom ?? null, 'sigle' => $fiche->organisation->sigle, 'point' => $point];
                    } elseif ($point > 0.75 && $point <= 1) {
                        $questionScoreRanges['0.75-1']['organisations'][] = ['id' => $fiche->organisation->secure_id, 'nom' => optional($fiche->organisation->user)->nom ?? null, 'sigle' => $fiche->organisation->sigle, 'point' => $point];
                    }
                    //}
                }
            }

            $question->score_ranges = $questionScoreRanges;

            return $question;
        })->values();
    }

    private function getQuestionsOperationnelle($questions, $fiche, $syntheseItem)
    {
        return collect($questions)->map(function ($question) use ($fiche, $syntheseItem) {

            if (!isset($question['score_ranges'])) {
                $questionScoreRanges = [
                    '0-0.25' => ['organisations' => []],
                    '0.25-0.50' => ['organisations' => []],
                    '0.50-0.75' => ['organisations' => []],
                    '0.75-1' => ['organisations' => []],
                ];
            } else {
                $questionScoreRanges = $question->score_ranges;
            }

            foreach ($syntheseItem['questions_de_gouvernance'] as $questionOperationnelle) {

                if ($questionOperationnelle['id'] == $question->secure_id) {

                    $moyenne_ponderee = $questionOperationnelle['moyenne_ponderee'];

                    // Logic for organizing into score ranges (adjust based on actual criteria)
                    if ($moyenne_ponderee >= 0 && $moyenne_ponderee <= 0.25) {
                        $questionScoreRanges['0-0.25']['organisations'][] = ['id' => $fiche->organisation->secure_id, 'nom' => optional($fiche->organisation->user)->nom ?? null, 'sigle' => $fiche->organisation->sigle, 'moyenne_ponderee' => $moyenne_ponderee]; // Assuming you have this info in the fiche
                    } elseif ($moyenne_ponderee > 0.25 && $moyenne_ponderee <= 0.50) {
                        $questionScoreRanges['0.25-0.50']['organisations'][] = ['id' => $fiche->organisation->secure_id, 'nom' => optional($fiche->organisation->user)->nom ?? null, 'sigle' => $fiche->organisation->sigle, 'moyenne_ponderee' => $moyenne_ponderee];
                    } elseif ($moyenne_ponderee > 0.50 && $moyenne_ponderee <= 0.75) {
                        $questionScoreRanges['0.50-0.75']['organisations'][] = ['id' => $fiche->organisation->secure_id, 'nom' => optional($fiche->organisation->user)->nom ?? null, 'sigle' => $fiche->organisation->sigle, 'moyenne_ponderee' => $moyenne_ponderee];
                    } elseif ($moyenne_ponderee > 0.75 && $moyenne_ponderee <= 1) {
                        $questionScoreRanges['0.75-1']['organisations'][] = ['id' => $fiche->organisation->secure_id, 'nom' => optional($fiche->organisation->user)->nom ?? null, 'sigle' => $fiche->organisation->sigle, 'moyenne_ponderee' => $moyenne_ponderee];
                    }
                }
            }

            $question->score_ranges = $questionScoreRanges;

            return $question;
        })->values();
    }

    /**
     * Liste des soumissions d'une evaluation de gouvernance
     *
     * return JsonResponse
     */
    public function fiches_de_synthese_with_organisations_classement($evaluationDeGouvernance, array $columns = ['*'], array $relations = [], array $appends = []): JsonResponse
    {
        try {
            if (!is_object($evaluationDeGouvernance) && !($evaluationDeGouvernance = $this->repository->findById($evaluationDeGouvernance))) throw new Exception("Evaluation de gouvernance inconnue.", 500);

            $formulaire_factuel_de_gouvernance = $evaluationDeGouvernance->formulaire_factuel_de_gouvernance();


            $formulaire_factuel_de_gouvernance = $formulaire_factuel_de_gouvernance->categories_de_gouvernance->map(function ($category_de_gouvernance) use ($evaluationDeGouvernance) {

                $fiches = $evaluationDeGouvernance->fiches_de_synthese_factuel->load("organisation.user");

                // Initialize score ranges
                $scoreRanges = [
                    '0-0.25' => ['organisations' => []],
                    '0.25-0.50' => ['organisations' => []],
                    '0.50-0.75' => ['organisations' => []],
                    '0.75-1' => ['organisations' => []],
                ];

                // Loop through each record
                foreach ($fiches as $fiche) {
                    // Skip if organisation is null
                    if (!$fiche->organisation) {
                        continue;
                    }

                    $synthese = $fiche->synthese;

                    foreach ($synthese as $syntheseItem) {

                        if ($syntheseItem['id'] == $category_de_gouvernance->secure_id) {
                            $indiceFactuel = $syntheseItem['indice_factuel'];

                            // Logic for organizing into score ranges (adjust based on actual criteria)
                            if ($indiceFactuel >= 0 && $indiceFactuel <= 0.25) {
                                $scoreRanges['0-0.25']['organisations'][] = ['id' => $fiche->organisation->secure_id, 'nom' => optional($fiche->organisation->user)->nom, 'sigle' => $fiche->organisation->sigle, 'indice_factuel' => $indiceFactuel]; // Assuming you have this info in the fiche
                            } elseif ($indiceFactuel > 0.25 && $indiceFactuel <= 0.50) {
                                $scoreRanges['0.25-0.50']['organisations'][] = ['id' => $fiche->organisation->secure_id, 'nom' => optional($fiche->organisation->user)->nom, 'sigle' => $fiche->organisation->sigle, 'indice_factuel' => $indiceFactuel];
                            } elseif ($indiceFactuel > 0.50 && $indiceFactuel <= 0.75) {
                                $scoreRanges['0.50-0.75']['organisations'][] = ['id' => $fiche->organisation->secure_id, 'nom' => optional($fiche->organisation->user)->nom, 'sigle' => $fiche->organisation->sigle, 'indice_factuel' => $indiceFactuel];
                            } elseif ($indiceFactuel > 0.75 && $indiceFactuel <= 1) {
                                $scoreRanges['0.75-1']['organisations'][] = ['id' => $fiche->organisation->secure_id, 'nom' => optional($fiche->organisation->user)->nom, 'sigle' => $fiche->organisation->sigle, 'indice_factuel' => $indiceFactuel];
                            }

                            $category_de_gouvernance->categories_de_gouvernance = $this->getCategories($category_de_gouvernance->categories_de_gouvernance, $fiche, $syntheseItem['categories_de_gouvernance']);
                        }
                    }
                }

                $category_de_gouvernance['score_ranges'] = $scoreRanges;

                return new CategoriesDeGouvernanceResource($category_de_gouvernance);
            });

            $formulaire_de_perception_de_gouvernance = $evaluationDeGouvernance->formulaire_de_perception_de_gouvernance();

            $formulaire_de_perception_de_gouvernance = $formulaire_de_perception_de_gouvernance->categories_de_gouvernance->map(function ($category_de_gouvernance) use ($evaluationDeGouvernance) {

                $fiches = $evaluationDeGouvernance->fiches_de_synthese_de_perception->load("organisation.user");

                // Initialize score ranges
                $scoreRanges = [
                    '0-0.25' => ['organisations' => []],
                    '0.25-0.50' => ['organisations' => []],
                    '0.50-0.75' => ['organisations' => []],
                    '0.75-1' => ['organisations' => []],
                ];

                // Loop through each record
                foreach ($fiches as $fiche) {
                    // Skip if organisation is null
                    if (!$fiche->organisation) {
                        continue;
                    }

                    $synthese = $fiche->synthese;

                    foreach ($synthese as $syntheseItem) {

                        if ($syntheseItem['id'] == $category_de_gouvernance->secure_id) {
                            $indiceFactuel = $syntheseItem['indice_de_perception'];

                            // Logic for organizing into score ranges (adjust based on actual criteria)
                            if ($indiceFactuel >= 0 && $indiceFactuel <= 0.25) {
                                $scoreRanges['0-0.25']['organisations'][] = ['id' => $fiche->organisation->secure_id, 'nom' => optional($fiche->organisation->user)->nom, 'sigle' => $fiche->organisation->sigle, 'indice_de_perception' => $indiceFactuel]; // Assuming you have this info in the fiche
                            } elseif ($indiceFactuel > 0.25 && $indiceFactuel <= 0.50) {
                                $scoreRanges['0.25-0.50']['organisations'][] = ['id' => $fiche->organisation->secure_id, 'nom' => optional($fiche->organisation->user)->nom, 'sigle' => $fiche->organisation->sigle, 'indice_de_perception' => $indiceFactuel];
                            } elseif ($indiceFactuel > 0.50 && $indiceFactuel <= 0.75) {
                                $scoreRanges['0.50-0.75']['organisations'][] = ['id' => $fiche->organisation->secure_id, 'nom' => optional($fiche->organisation->user)->nom, 'sigle' => $fiche->organisation->sigle, 'indice_de_perception' => $indiceFactuel];
                            } elseif ($indiceFactuel > 0.75 && $indiceFactuel <= 1) {
                                $scoreRanges['0.75-1']['organisations'][] = ['id' => $fiche->organisation->secure_id, 'nom' => optional($fiche->organisation->user)->nom, 'sigle' => $fiche->organisation->sigle, 'indice_de_perception' => $indiceFactuel];
                            }

                            if (isset($syntheseItem['questions_de_gouvernance'])) {
                                $category_de_gouvernance->questions_de_gouvernance = $this->getQuestionsOperationnelle($category_de_gouvernance->questions_de_gouvernance, $fiche, $syntheseItem);
                            }
                        }
                    }
                }

                $category_de_gouvernance->score_ranges = $scoreRanges;

                return new CategoriesDeGouvernanceResource($category_de_gouvernance);
            });


            return response()->json(['statut' => 'success', 'message' => null, 'data' => ["factuel" => $formulaire_factuel_de_gouvernance, "perception" => $formulaire_de_perception_de_gouvernance], 'statutCode' => Response::HTTP_OK], Response::HTTP_OK);
        } catch (\Throwable $th) {
            return response()->json(['statut' => 'error', 'message' => $th->getMessage(), 'errors' => []], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    public function feuille_de_route($evaluationDeGouvernance, array $columns = ['*'], array $relations = [], array $appends = []): JsonResponse
    {
        try {
            if (!is_object($evaluationDeGouvernance) && !($evaluationDeGouvernance = $this->repository->findById($evaluationDeGouvernance))) throw new Exception("Evaluation de gouvernance inconnue.", 500);

            $feuille_de_route = [];
            auth()->user()->profilable;

            if ((Auth::user()->hasRole('organisation') || (get_class(auth()->user()->profilable) == Organisation::class))) {
                $feuille_de_route = $evaluationDeGouvernance->load(["recommandations" => function ($query) {
                    $query/* ->where("organisationId", auth()->user()->profilable->id) */->with(["actions_a_mener" => function ($query) {
                        $query/* ->where("organisationId", auth()->user()->profilable->id) */;
                    }]);
                }, "actions_a_mener" => function ($query) {
                    $query->where("organisationId", auth()->user()->profilable->id)->whereDoesntHave("actionable");
                }]);
            } else {
                $feuille_de_route = $evaluationDeGouvernance->actions_a_mener;
            }

            return response()->json(['statut' => 'success', 'message' => null, 'data' => ['recommandations' => RecommandationsResource::collection($feuille_de_route->recommandations), 'actions_a_mener' => ActionsAMenerResource::collection($feuille_de_route->actions_a_mener)], 'statutCode' => Response::HTTP_OK], Response::HTTP_OK);
        } catch (\Throwable $th) {
            return response()->json(['statut' => 'error', 'message' => $th->getMessage(), 'errors' => []], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    public function principes_de_gouvernance($evaluationDeGouvernance, array $columns = ['*'], array $relations = [], array $appends = []): JsonResponse
    {
        try {
            if (!is_object($evaluationDeGouvernance) && !($evaluationDeGouvernance = $this->repository->findById($evaluationDeGouvernance))) throw new Exception("Evaluation de gouvernance inconnue.", 500);

            $principes_de_gouvernance = $evaluationDeGouvernance->principes_de_gouvernance();

            return response()->json(['statut' => 'success', 'message' => null, 'data' => PrincipeDeGouvernanceResource::collection($principes_de_gouvernance), 'statutCode' => Response::HTTP_OK], Response::HTTP_OK);
        } catch (\Throwable $th) {
            return response()->json(['statut' => 'error', 'message' => $th->getMessage(), 'errors' => []], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    public function actions_a_mener($evaluationDeGouvernance, array $columns = ['*'], array $relations = [], array $appends = []): JsonResponse
    {
        try {
            if (!is_object($evaluationDeGouvernance) && !($evaluationDeGouvernance = $this->repository->findById($evaluationDeGouvernance))) throw new Exception("Evaluation de gouvernance inconnue.", 500);

            $actions_a_mener = [];

            if ((Auth::user()->hasRole('organisation') || (get_class(auth()->user()->profilable) == Organisation::class))) {
                $actions_a_mener = $evaluationDeGouvernance->actions_a_mener()->where("organisationId", auth()->user()->profilable->id)/* ->where('statut','>',-1) */->get();
            } else {
                $actions_a_mener = $evaluationDeGouvernance->actions_a_mener;
            }

            return response()->json(['statut' => 'success', 'message' => null, 'data' => ActionsAMenerResource::collection($actions_a_mener), 'statutCode' => Response::HTTP_OK], Response::HTTP_OK);
        } catch (\Throwable $th) {
            return response()->json(['statut' => 'error', 'message' => $th->getMessage(), 'errors' => []], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }


    public function recommandations($evaluationDeGouvernance, array $columns = ['*'], array $relations = [], array $appends = []): JsonResponse
    {
        try {
            if (!is_object($evaluationDeGouvernance) && !($evaluationDeGouvernance = $this->repository->findById($evaluationDeGouvernance))) throw new Exception("Evaluation de gouvernance inconnue.", 500);

            $recommandations = [];

            if ((Auth::user()->hasRole('organisation') || (get_class(auth()->user()->profilable) == Organisation::class))) {
                $recommandations = $evaluationDeGouvernance->recommandations()->where("organisationId", auth()->user()->profilable->id)->get();
            } else {
                $recommandations = $evaluationDeGouvernance->recommandations;
            }

            return response()->json(['statut' => 'success', 'message' => null, 'data' => RecommandationsResource::collection($recommandations), 'statutCode' => Response::HTTP_OK], Response::HTTP_OK);
        } catch (\Throwable $th) {
            return response()->json(['statut' => 'error', 'message' => $th->getMessage(), 'errors' => []], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * Liste des formulaires d'une evaluation de gouvernance
     *
     * return JsonResponse
     */
    public function formulaires_de_gouvernance($evaluationDeGouvernance, array $columns = ['*'], array $relations = [], array $appends = []): JsonResponse
    {
        try {
            if (!is_object($evaluationDeGouvernance) && !($evaluationDeGouvernance = $this->repository->findById($evaluationDeGouvernance))) throw new Exception("Evaluation de gouvernance inconnue.", 500);

            return response()->json(['statut' => 'success', 'message' => null, 'data' => [new ListFormulaireDeGouvernanceFactuelResource($evaluationDeGouvernance->formulaire_factuel_de_gouvernance()), new ListFormulaireDeGouvernanceDePerceptionResource($evaluationDeGouvernance->formulaire_de_perception_de_gouvernance())], 'statutCode' => Response::HTTP_OK], Response::HTTP_OK);
        } catch (\Throwable $th) {
            return response()->json(['statut' => 'error', 'message' => $th->getMessage(), 'errors' => []], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * Liste des formulaires d'une evaluation de gouvernance
     *
     * return JsonResponse
     */
    public function formulaire_factuel($evaluationDeGouvernance): JsonResponse
    {
        try {

            if ((!Auth::user()->hasRole('organisation') && (get_class(auth()->user()->profilable) != Organisation::class))) {

                return response()->json(['statut' => 'error', 'message' => "Pas la permission pour", 'data' => null, 'statutCode' => Response::HTTP_FORBIDDEN], Response::HTTP_FORBIDDEN);
            } else if (Auth::user()->profilable === null) {
                return response()->json(['statut' => 'error', 'message' => "Unknown", 'data' => null, 'statutCode' => Response::HTTP_OK], Response::HTTP_OK);
            }

            if (!is_object($evaluationDeGouvernance) && !($evaluationDeGouvernance = $this->repository->findById($evaluationDeGouvernance))) throw new Exception("Evaluation de gouvernance inconnue.", 500);

            if ($evaluationDeGouvernance->statut == 1) {

                return response()->json(['statut' => 'success', 'message' => "Lien expire", 'data' => null, 'statutCode' => Response::HTTP_NO_CONTENT], Response::HTTP_NO_CONTENT);
            }

            $organisation = $evaluationDeGouvernance->organisations(Auth::user()->profilable->id)->first();

            $terminer = false;

            if ($organisation != null) {
                if ($soumission = $evaluationDeGouvernance->soumissionFactuel($organisation->id)->first()) {
                    if ($soumission->statut === true) {
                        $terminer = true;
                    }
                }
            }

            return response()->json(['statut' => 'success', 'message' => null, 'data' => [
                'token' => $organisation->pivot->token,
                'terminer' => $terminer,
                'exist' => $evaluationDeGouvernance->formulaire_factuel_de_gouvernance() ? true : false
            ], 'statutCode' => Response::HTTP_OK], Response::HTTP_OK);
        } catch (\Throwable $th) {
            return response()->json(['statut' => 'error', 'message' => $th->getMessage(), 'errors' => []], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * Liste des formulaires d'une evaluation de gouvernance
     *
     * return JsonResponse
     */
    public function formulaire_factuel_de_gouvernance($token): JsonResponse
    {
        try {

            $evaluationDeGouvernance = EnqueteEvaluationDeGouvernance::whereHas("organisations", function ($query) use ($token) {
                $query->where('evaluation_organisations.token', $token);
            })->with(["organisations" => function ($query) use ($token) {
                $query->wherePivot('token', $token);
            }])->first();

            if (!($evaluationDeGouvernance)) throw new Exception("Evaluation de gouvernance inconnue.", 500);

            if ($evaluationDeGouvernance->statut == 1) {
                return response()->json(['statut' => 'success', 'message' => "Lien expire", 'data' => null, 'statutCode' => Response::HTTP_NO_CONTENT], Response::HTTP_NO_CONTENT);
            }

            $organisation = $evaluationDeGouvernance->organisations->first();

            $terminer = false;

            if ($organisation != null) {

                if ($soumission = $evaluationDeGouvernance->soumissionFactuel($organisation->id)->first()) {

                    if ($soumission->statut === true) {
                        $terminer = true;
                        $formulaire_factuel_de_gouvernance = false;

                        return response()->json(['statut' => 'success', 'message' => "Soumission deja valider", 'data' => ['terminer' => $terminer, 'idEvaluation' => $evaluationDeGouvernance->secure_id, 'idSoumission' => $soumission->secure_id], 'statutCode' => Response::HTTP_PARTIAL_CONTENT], Response::HTTP_PARTIAL_CONTENT);
                    } else {
                        $formulaire_factuel_de_gouvernance = new SoumissionFactuelResource($soumission);
                    }
                }
                /*$formulaire_factuel_de_gouvernance = $evaluationDeGouvernance->formulaire_factuel_de_gouvernance()->load("questions_de_gouvernance.reponses", function ($query) use ($evaluationDeGouvernance, $token) {
                    $query->where('type', 'indicateur')->whereHas("soumission", function ($query) use ($evaluationDeGouvernance, $token) {
                        $query->where('evaluationId', $evaluationDeGouvernance->id)->where('organisationId', $evaluationDeGouvernance->organisations()->wherePivot('token', $token)->first()->id);
                    });
                });*/ else {

                    if (!$evaluationDeGouvernance->formulaire_factuel_de_gouvernance()) {
                        return response()->json(['statut' => 'success', 'message' => "Evaluation inexistant", 'data' => -1, 'statutCode' => Response::HTTP_NOT_FOUND], Response::HTTP_NOT_FOUND);
                    }

                    if ($organisation->sousmissions_enquete_factuel()->where('evaluationId', $evaluationDeGouvernance->id)->count() >= 1) {
                        return response()->json(['statut' => 'success', 'message' => "Soumission factuel passer", 'data' => ['terminer' => true, 'formulaire_de_gouvernance' => null], 'statutCode' => Response::HTTP_OK], Response::HTTP_OK);
                    }

                    $attributs = [
                        'evaluationId' => $evaluationDeGouvernance->id,
                        'formulaireFactuelId' => $evaluationDeGouvernance->formulaire_factuel_de_gouvernance()->id,
                        'organisationId' => $organisation->id,
                        'programmeId' => $evaluationDeGouvernance->programmeId,
                        'submitted_at' => now()
                    ];

                    $soumission = $evaluationDeGouvernance->soumissionsFactuel()->create($attributs);

                    $formulaire_factuel_de_gouvernance = new SoumissionFactuelResource($soumission);
                }
            } else {

                return response()->json(['statut' => 'success', 'message' => "Organisation inconnu du programme", 'data' => null, 'statutCode' => Response::HTTP_NOT_FOUND], Response::HTTP_NOT_FOUND);

                $formulaire_factuel_de_gouvernance = new ListFormulaireDeGouvernanceFactuelResource($evaluationDeGouvernance->formulaire_factuel_de_gouvernance());
            }

            return response()->json(['statut' => 'success', 'message' => null, 'data' => [
                'id'                => $evaluationDeGouvernance->secure_id,
                'intitule' => $evaluationDeGouvernance->intitule,
                'description' => $evaluationDeGouvernance->description,
                'debut' => Carbon::parse($evaluationDeGouvernance->debut)->format("Y-m-d"),
                'fin' => Carbon::parse($evaluationDeGouvernance->fin)->format("Y-m-d"),
                'annee_exercice' => $evaluationDeGouvernance->annee_exercice,
                'statut' => $evaluationDeGouvernance->statut,
                'terminer' => $terminer,
                'programmeId' => $evaluationDeGouvernance->programme->secure_id,
                'formulaire_de_gouvernance' => $formulaire_factuel_de_gouvernance
            ], 'statutCode' => Response::HTTP_OK], Response::HTTP_OK);
        } catch (\Throwable $th) {
            return response()->json(['statut' => 'error', 'message' => $th->getMessage(), 'errors' => []], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * Liste des formulaires d'une evaluation de gouvernance
     *
     * return JsonResponse
     */
    public function formulaire_de_perception_de_gouvernance(string $paricipant_id, string $token): JsonResponse
    {
        try {
            // 1. Récupérer l'évaluation via le token d'organisation
            $evaluationDeGouvernance = EnqueteEvaluationDeGouvernance::whereHas("organisations", function ($query) use ($token) {
                $query->where('evaluation_organisations.token', $token);
            })->first();

            if (!$evaluationDeGouvernance) {
                throw new Exception("Evaluation de gouvernance inconnue ou lien invalide.", 404);
            }

            // 2. Vérifier le statut de l'évaluation
            if ($evaluationDeGouvernance->statut == 1) {
                return response()->json(['statut' => 'success', 'message' => "L'évaluation est clôturée.", 'data' => null, 'statutCode' => Response::HTTP_NO_CONTENT], Response::HTTP_NO_CONTENT);
            }

            $organisation = $evaluationDeGouvernance->organisations()->wherePivot('token', $token)->first();
            if (!$organisation) {
                throw new Exception("Organisation introuvable.", 404);
            }

            // 3. Tenter d'identifier le participant_id (sans bloquer la soumission s'il n'est pas trouvé)
            $participantsList = $organisation->pivot->participants ? json_decode($organisation->pivot->participants, true) : [];
            $participantInfo = null;

            foreach ($participantsList as $p) {
                if (isset($p['id']) && (string)$p['id'] === (string)$paricipant_id) {
                    $participantInfo = $p; // Stocke toutes les infos du participant connues (nom, mail, tel, etc.)
                    break;
                }
            }
            // Si participantInfo est null ici, cela signifie que c'est un "anonyme" ou un participant non listé initialement.
            // On continue le flow, mais on n'a pas d'infos supplémentaires pour le personnaliser.

            $terminer = false;
            $formulaire_de_perception_de_gouvernance = null;

            // 4. Vérifier si une soumission existe déjà pour ce participant (quel qu'il soit)
            $soumission = $evaluationDeGouvernance->soumissionDePerception($paricipant_id, $organisation->id)->first();

            if ($soumission) {
                // Cas : Soumission existante
                if ($soumission->statut === true) {
                    $terminer = true;
                    // La soumission est terminée, on ne renvoie pas le formulaire pour éviter toute modification.
                    return response()->json(['statut' => 'success', 'message' => "Votre soumission a déjà été validée.", 'data' => [
                        'id' => $evaluationDeGouvernance->secure_id,
                        'intitule' => $evaluationDeGouvernance->intitule,
                        'description' => $evaluationDeGouvernance->description,
                        'debut' => Carbon::parse($evaluationDeGouvernance->debut)->format("Y-m-d"),
                        'fin' => Carbon::parse($evaluationDeGouvernance->fin)->format("Y-m-d"),
                        'annee_exercice' => $evaluationDeGouvernance->annee_exercice,
                        'statut' => $evaluationDeGouvernance->statut,
                        'terminer' => $terminer,
                        'programmeId' => $evaluationDeGouvernance->programme->secure_id,
                        'participant_info' => $participantInfo,
                        'formulaire_de_gouvernance' => null // Pas de formulaire pour une soumission terminée
                    ], 'statutCode' => Response::HTTP_OK], Response::HTTP_OK);
                } else {
                    // La soumission existe mais n'est pas terminée, on renvoie le formulaire pour qu'il puisse reprendre.
                    $formulaire_de_perception_de_gouvernance = new SoumissionDePerceptionResource($soumission);
                }
            } else {
                // Cas : Nouvelle soumission

                // Vérification du quota global avant de créer une nouvelle soumission
                // On compte le nombre de soumissions déjà créées ET TERMINÉES (statut = true) pour cette organisation
                $currentSubmissionsCount = $organisation->sousmissions_enquete_de_perception()
                    ->where('evaluationId', $evaluationDeGouvernance->id)
                    ->where('statut', true) // <-- Modifié : Ne compte que les soumissions terminées
                    ->count();

                $quota = $organisation->pivot->nbreParticipants; // Le nombre total de soumissions autorisées

                // Si le nombre de soumissions terminées atteint ou dépasse le quota, on bloque la création de nouvelles.
                if ($currentSubmissionsCount >= $quota) {
                    return response()->json(['statut' => 'error', 'message' => "Le nombre maximal de soumissions COMPLÉTÉES pour cette évaluation a été atteint. Merci de votre intérêt.", 'data' => ['terminer' => true, 'formulaire_de_gouvernance' => null], 'statutCode' => Response::HTTP_FORBIDDEN], Response::HTTP_FORBIDDEN);
                }

                // Création de la soumission
                $attributs = [
                    'evaluationId' => $evaluationDeGouvernance->id,
                    'formulaireDePerceptionId' => $evaluationDeGouvernance->formulaire_de_perception_de_gouvernance()->id,
                    'organisationId' => $organisation->id,
                    'programmeId' => $evaluationDeGouvernance->programmeId,
                    'identifier_of_participant' => $paricipant_id,
                    'statut' => false // Nouvelle soumission, non terminée par défaut
                ];

                $soumission = $evaluationDeGouvernance->soumissionsDePerception()->create($attributs);
                $formulaire_de_perception_de_gouvernance = new SoumissionDePerceptionResource($soumission);
            }

            // Construction de la réponse finale
            $responseData = [
                'id' => $evaluationDeGouvernance->secure_id,
                'intitule' => $evaluationDeGouvernance->intitule,
                'description' => $evaluationDeGouvernance->description,
                'debut' => Carbon::parse($evaluationDeGouvernance->debut)->format("Y-m-d"),
                'fin' => Carbon::parse($evaluationDeGouvernance->fin)->format("Y-m-d"),
                'annee_exercice' => $evaluationDeGouvernance->annee_exercice,
                'statut' => $evaluationDeGouvernance->statut,
                'terminer' => $terminer,
                'programmeId' => $evaluationDeGouvernance->programme->secure_id,
                'participant_info' => $participantInfo, // Infos du participant si trouvé, sinon null
                'formulaire_de_gouvernance' => $formulaire_de_perception_de_gouvernance
            ];

            return response()->json(['statut' => 'success', 'message' => null, 'data' => $responseData, 'statutCode' => Response::HTTP_OK], Response::HTTP_OK);
        } catch (\Throwable $th) {
            return response()->json(['statut' => 'error', 'message' => $th->getMessage(), 'errors' => []], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * Envoi
     *
     * return JsonResponse
     */
    public function envoi_mail_au_participants($evaluationDeGouvernance, array $attributs): JsonResponse
    {
        try {
            if (!is_object($evaluationDeGouvernance) && !($evaluationDeGouvernance = $this->repository->findById($evaluationDeGouvernance))) {
                throw new Exception("Evaluation de gouvernance inconnue.", 500);
            }

            $organisationId = null;
            if ((Auth::user()->hasRole('organisation') || (get_class(auth()->user()->profilable) == Organisation::class))) {
                $organisationId = Auth::user()->profilable->id;
            } else {
                return response()->json(['statut' => 'error', 'message' => "Pas le droit", 'data' => null, 'statutCode' => Response::HTTP_FORBIDDEN], Response::HTTP_FORBIDDEN);
            }

            // 1. Récupérer l'organisation et ses données pivot
            $evaluationOrganisation = $evaluationDeGouvernance->organisations($organisationId)->first();
            if (!$evaluationOrganisation) {
                throw new Exception("Organisation non liée à cette évaluation.", 404);
            }

            // 2. Fusionner et dédoublonner les participants (Logique déplacée du Job vers le Service)
            $existingParticipants = $evaluationOrganisation->pivot->participants ? json_decode($evaluationOrganisation->pivot->participants, true) : [];
            $newParticipants = $attributs['participants'] ?? [];

            // Assigner un ID unique aux nouveaux participants s'ils n'en ont pas
            foreach ($newParticipants as &$np) {
                if (!isset($np['id'])) {
                    $np['id'] = uniqid('part_', true);
                }
            }
            unset($np); // Rompre la référence

            $allParticipants = array_merge($existingParticipants, $newParticipants);
            $uniqueParticipants = [];

            // Dédoublonnage robuste (Email prioritaire, puis Téléphone)
            foreach ($allParticipants as $p) {
                if (isset($p['type_de_contact'])) {
                    $key = null;
                    // On utilise l'email ou le téléphone comme clé d'unicité
                    if ($p['type_de_contact'] === 'email' && !empty($p['email'])) {
                        $key = 'email_' . $p['email'];
                    } elseif ($p['type_de_contact'] === 'contact' && !empty($p['phone'])) {
                        $key = 'phone_' . $p['phone'];
                    }

                    if ($key) {
                        // Si le participant n'a pas d'ID (cas d'un ancien participant en base), on lui en génère un
                        if (!isset($p['id'])) {
                            $p['id'] = uniqid('part_', true);
                        }

                        // On stocke le participant.
                        // Si la clé existe déjà (doublon), la version actuelle ($p) écrasera la précédente.
                        // Comme $allParticipants contient [Anciens..., Nouveaux...], les nouveaux prévaudront en cas de conflit
                        // (mise à jour des infos), mais s'ils sont identiques, l'ID du "nouveau" sera utilisé.
                        // Pour garder la stabilité de l'ID d'un existant, on vérifie d'abord.
                        if (!isset($uniqueParticipants[$key])) {
                            $uniqueParticipants[$key] = $p;
                        } else {
                            // Si on veut mettre à jour les infos mais garder l'ID original :
                            $originalId = $uniqueParticipants[$key]['id'];
                            $uniqueParticipants[$key] = $p;
                            $uniqueParticipants[$key]['id'] = $originalId; // On restaure l'ID original
                        }
                    }
                }
            }
            $finalParticipantsList = array_values($uniqueParticipants);

            // 3. Mise à jour immédiate de la liste des participants
            $evaluationOrganisation->pivot->participants = json_encode($finalParticipantsList);

            // 4. Mise à jour immédiate du Quota (nbreParticipants) avec contrôles de sécurité
            $currentQuota = $evaluationOrganisation->pivot->nbreParticipants ?? 0;
            $requestedQuota = $attributs['nbreParticipants'] ?? 0;
            $totalInvited = count($finalParticipantsList);

            // Compter les réponses déjà reçues pour ne pas casser l'existant
            $responsesReceived = $evaluationDeGouvernance->soumissionsDePerception()
                ->where('organisationId', $organisationId)
                ->count();

            // Le quota doit être au moins égal au nombre de gens qu'on a explicitement invités
            $effectiveQuota = max($requestedQuota, $totalInvited);

            if ($effectiveQuota > 0) {
                // On met à jour si :
                // - C'est une augmentation (toujours safe)
                // - C'est une diminution MAIS qui reste supérieure au nombre de réponses déjà reçues
                if ($effectiveQuota > $currentQuota || ($effectiveQuota < $currentQuota && $effectiveQuota >= $responsesReceived)) {
                    $evaluationOrganisation->pivot->nbreParticipants = $effectiveQuota;
                }
            }

            $evaluationOrganisation->pivot->save();

            // 5. Identifier les VRAIS nouveaux participants pour l'envoi de mail
            // On compare les clés (email/phone) des $newParticipants avec ceux qui étaient DÉJÀ dans $existingParticipants
            $existingKeys = [];
            foreach ($existingParticipants as $ep) {
                if (isset($ep['type_de_contact'])) {
                    if ($ep['type_de_contact'] === 'email' && !empty($ep['email'])) $existingKeys['email_' . $ep['email']] = true;
                    elseif ($ep['type_de_contact'] === 'contact' && !empty($ep['phone'])) $existingKeys['phone_' . $ep['phone']] = true;
                }
            }

            $participantsToNotify = [];
            foreach ($newParticipants as $np) {
                $key = null;
                if (isset($np['type_de_contact'])) {
                    if ($np['type_de_contact'] === 'email' && !empty($np['email'])) $key = 'email_' . $np['email'];
                    elseif ($np['type_de_contact'] === 'contact' && !empty($np['phone'])) $key = 'phone_' . $np['phone'];
                }

                // Si la clé n'existait pas avant, c'est un nouveau à notifier
                if ($key && !isset($existingKeys[$key])) {
                    $participantsToNotify[] = $np;
                }
            }

            // 6. Dispatcher le job UNIQUEMENT s'il y a des gens à notifier
            if (!empty($participantsToNotify)) {
                $jobData = $attributs;
                $jobData['organisationId'] = $organisationId;
                $jobData['participants'] = $participantsToNotify;
                $jobData['token'] = $evaluationOrganisation->pivot->token; // Pass token explicitly

                SendInvitationJob::dispatch($evaluationDeGouvernance, $evaluationOrganisation, $jobData, 'invitation-enquete-de-collecte');

                return response()->json(['statut' => 'success', 'message' => "Invitations envoyées aux nouveaux participants.", 'data' => null, 'statutCode' => Response::HTTP_OK], Response::HTTP_OK);
            }

            return response()->json(['statut' => 'success', 'message' => "Participants mis à jour. Aucune nouvelle invitation nécessaire.", 'data' => null, 'statutCode' => Response::HTTP_OK], Response::HTTP_OK);
        } catch (\Throwable $th) {
            return response()->json(['statut' => 'error', 'message' => $th->getMessage(), 'errors' => []], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * Envoi
     *
     * return JsonResponse
     */
    public function rappel_soumission($evaluationDeGouvernance): JsonResponse
    {
        try {
            if (!is_object($evaluationDeGouvernance) && !($evaluationDeGouvernance = $this->repository->findById($evaluationDeGouvernance))) throw new Exception("Evaluation de gouvernance inconnue.", 500);

            $organisationId = Auth::user()->profilable->id; // Current user's organisation ID

            // 1. Retrieve the evaluationOrganisation pivot data
            if (!($evaluationOrganisation = $evaluationDeGouvernance->organisations($organisationId)->first())) {
                throw new Exception("L'organisation n'est pas liée à cette évaluation.", 404);
            }

            // 2. Get all invited participants from the pivot table
            $allInvitedParticipants = $evaluationOrganisation->pivot->participants ? json_decode($evaluationOrganisation->pivot->participants, true) : [];

            // 3. Get IDs of participants who have already submitted (only completed ones)
            $submittedParticipantIds = SoumissionDePerception::where('evaluationId', $evaluationDeGouvernance->id)
                ->where('organisationId', $organisationId)
                ->where('statut', true) // <-- Ajouté : Ne récupère que les IDs des soumissions terminées
                ->pluck('identifier_of_participant')
                ->toArray();

            // 4. Identify participants who have not yet submitted
            $participantsToRemind = [];
            foreach ($allInvitedParticipants as $participant) {
                // Ensure participant has an ID and it's not in the submitted list
                if (isset($participant['id']) && !in_array($participant['id'], $submittedParticipantIds)) {
                    $participantsToRemind[] = $participant;
                }
            }

            // 5. Dispatch SendInvitationJob if there are participants to remind
            if (!empty($participantsToRemind)) {
                $jobData = [];
                $jobData['organisationId'] = $organisationId;
                $jobData['participants'] = $participantsToRemind; // Only send to those who need a reminder
                $jobData['token'] = $evaluationOrganisation->pivot->token; // Pass token explicitly

                $mailSubject = "Rappel : Soumission à l'auto-évaluation de gouvernance";
                $mailView = "emails.auto-evaluation.rappel_soumission_participant"; // Assuming you have this reminder template

                SendInvitationJob::dispatch(
                    $evaluationDeGouvernance,
                    $evaluationOrganisation,
                    $jobData,
                    'rappel-soumission', // Type to indicate it's a reminder
                    $mailSubject,
                    $mailView
                );

                return response()->json(['statut' => 'success', 'message' => "Rappel(s) envoyé(s) aux participants n'ayant pas encore soumis.", 'data' => null, 'statutCode' => Response::HTTP_OK], Response::HTTP_OK);
            }

            return response()->json(['statut' => 'success', 'message' => "Tous les participants ont déjà soumis leur enquête.", 'data' => null, 'statutCode' => Response::HTTP_OK], Response::HTTP_OK);
        } catch (\Throwable $th) {
            return response()->json(['statut' => 'error', 'message' => $th->getMessage(), 'errors' => []], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    public function resultats_syntheses($evaluationDeGouvernance): JsonResponse
    {
        try {
            if (!is_object($evaluationDeGouvernance) && !($evaluationDeGouvernance = $this->repository->findById($evaluationDeGouvernance))) throw new Exception("Evaluation de gouvernance inconnue.", 500);

            $resultats_syntheses = [];

            $organisationId = null;

            if ((auth()->user()->type == "organisation") || get_class(auth()->user()->profilable) == Organisation::class) {
                $organisationId = optional(auth()->user()->profilable)->id;
            }

            $programme = auth()->user()->programme;

            $resultats_syntheses = $evaluationDeGouvernance->organisations($organisationId)->get()
                ->map(function ($organisation) use ($programme) {
                    $evaluations_scores = $programme->evaluations_de_gouvernance->mapWithKeys(function ($evaluationDeGouvernance) use ($organisation) {
                        // Key-value pairing for each year with scores
                        $results = $organisation->profiles($evaluationDeGouvernance->id)->first()->resultat_synthetique ?? [];

                        return [$evaluationDeGouvernance->annee_exercice => $results];
                    });

                    // Merge evaluation scores with organizational metadata
                    return [
                        'id' => $organisation->secure_id,
                        'intitule' => $organisation->sigle . " - " . $organisation->user->nom,
                        'scores' => $evaluations_scores,
                    ];
                })
                ->values(); // Reset keys for a clean JSON output

            return response()->json(['statut' => 'success', 'message' => null, 'data' => $resultats_syntheses, 'statutCode' => Response::HTTP_OK], Response::HTTP_OK);
        } catch (\Throwable $th) {
            return response()->json(['statut' => 'error', 'message' => $th->getMessage(), 'errors' => []], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    public function classement_resultats_syntheses_des_organisation($evaluationDeGouvernance): JsonResponse
    {
        try {
            if (!is_object($evaluationDeGouvernance) && !($evaluationDeGouvernance = $this->repository->findById($evaluationDeGouvernance))) throw new Exception("Evaluation de gouvernance inconnue.", 500);

            $resultats_syntheses = [];

            $organisationId = null;

            if ((auth()->user()->type == "organisation") || get_class(auth()->user()->profilable) == Organisation::class) {
                $organisationId = optional(auth()->user()->profilable)->id;

                if (!$evaluationDeGouvernance->organisations($organisationId)->first()) {
                    return response()->json(['statut' => 'success', 'message' => null, 'data' => $resultats_syntheses, 'statutCode' => Response::HTTP_OK], Response::HTTP_OK);
                }
            }

            $resultats_syntheses = $evaluationDeGouvernance->organisationsClassement(); // Reset keys for a clean JSON output

            return response()->json(['statut' => 'success', 'message' => null, 'data' => $resultats_syntheses, 'statutCode' => Response::HTTP_OK], Response::HTTP_OK);
        } catch (\Throwable $th) {
            return response()->json(['statut' => 'error', 'message' => $th->getMessage(), 'errors' => []], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * Remove duplicate participants based on the 'email' field (or any unique field).
     */
    private function removeDuplicateParticipants($participants, string $type = 'email')
    {
        $uniqueParticipants = [];

        foreach ($participants as $participant) {
            if ($type == 'email') {
                // If participant doesn't exist in uniqueParticipants array, add them
                $uniqueParticipants[$participant['email']] = $participant;
            } elseif ($type == 'phone') {
                $uniqueParticipants[$participant['phone']] = $participant;
            }
        }

        // Return the unique participants as a re-indexed array
        return array_values($uniqueParticipants);
    }
}
