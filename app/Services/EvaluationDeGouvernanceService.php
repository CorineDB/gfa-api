<?php

namespace App\Services;

use App\Http\Resources\gouvernance\EvaluationsDeGouvernanceResource;
use App\Http\Resources\gouvernance\FicheDeSyntheseResource;
use App\Http\Resources\gouvernance\FormulairesDeGouvernanceResource;
use App\Http\Resources\gouvernance\SoumissionsResource;
use App\Http\Resources\OrganisationResource;
use App\Jobs\RappelJob;
use App\Jobs\SendInvitationJob;
use App\Models\EvaluationDeGouvernance;
use App\Models\Organisation;
use App\Repositories\EvaluationDeGouvernanceRepository;
use App\Repositories\OrganisationRepository;
use Core\Services\Contracts\BaseService;
use Core\Services\Interfaces\EvaluationDeGouvernanceServiceInterface;
use Exception;
use App\Traits\Helpers\LogActivity;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Hash;

/**
 * Interface EvaluationDeGouvernanceServiceInterface
 * @package Core\Services\Interfaces
 */
class EvaluationDeGouvernanceService extends BaseService implements EvaluationDeGouvernanceServiceInterface
{

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
            if (Auth::user()->hasRole('administrateur')) {
                $evaluationsDeGouvernance = $this->repository->all();
            } else if (Auth::user()->hasRole('organisation')) {
                $evaluationsDeGouvernance = Auth::user()->programme->evaluations_de_gouvernance()->whereHas('organisations', function ($query) {
                    $query->where('organisationId', Auth::user()->profilable->id);
                })->get();
            } else {
                //$projets = $this->repository->allFiltredBy([['attribut' => 'programmeId', 'operateur' => '=', 'valeur' => auth()->user()->programme->id]]);
                $evaluationsDeGouvernance = Auth::user()->programme->evaluations_de_gouvernance;
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

            $programme = Auth::user()->programme;

            $attributs = array_merge($attributs, ['programmeId' => $programme->id]);

            $evaluationDeGouvernance = $this->repository->create($attributs);
            $evaluationDeGouvernance->formulaires_de_gouvernance()->attach($attributs['formulaires_de_gouvernance']);

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

            LogActivity::addToLog("Enrégistrement", $message, get_class($evaluationDeGouvernance), $evaluationDeGouvernance->id);

            DB::commit();

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

            $evaluationDeGouvernance->refresh();
            $evaluationDeGouvernance->organisations()->syncWithoutDetaching($attributs['organisations']);
            $evaluationDeGouvernance->formulaires_de_gouvernance()->syncWithoutDetaching($attributs['formulaires_de_gouvernance']);

            $acteur = Auth::check() ? Auth::user()->nom . " " . Auth::user()->prenom : "Inconnu";

            $message = $message ?? Str::ucfirst($acteur) . " a modifié un " . strtolower(class_basename($evaluationDeGouvernance));

            LogActivity::addToLog("Modification", $message, get_class($evaluationDeGouvernance), $evaluationDeGouvernance->id);

            DB::commit();

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

            return response()->json(['statut' => 'success', 'message' => null, 'data' => new OrganisationResource($evaluationDeGouvernance->organisations), 'statutCode' => Response::HTTP_OK], Response::HTTP_OK);
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

            if (Auth::user()->hasRole('administrateur')) {
                $group_soumissions = [];
            } else if (Auth::user()->hasRole('organisation')) {

                $organisation = Auth::user()->profilable;

                $group_soumissions = $evaluationDeGouvernance->soumissions()->where('organisationId', $organisation->id)
                    ->get()->groupBy('type')->map(function ($soumissions, $type) {
                        if ($type === 'perception') {
                            return SoumissionsResource::collection($soumissions);
                        } else {
                            return new SoumissionsResource($soumissions->first());
                        }
                    });

                $group_soumissions = array_merge([
                    "id"                    => $organisation->secure_id,
                    'nom'                   => optional($organisation->user)->nom ?? null,
                    'sigle'                 => $organisation->sigle,
                    'code'                  => $organisation->code,
                    'nom_point_focal'       => $organisation->nom_point_focal,
                    'prenom_point_focal'    => $organisation->prenom_point_focal,
                    'contact_point_focal'   => $organisation->contact_point_focal
                ], $group_soumissions->toArray());
            } else {
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


            if (Auth::user()->hasRole('administrateur')) {
                $fiches_de_synthese = [];
            } else if (Auth::user()->hasRole('organisation')) {

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
                    'profile_de_gouvernance'   => $organisation->profiles($evaluationDeGouvernance)
                ], $fiches_de_synthese->toArray());
            } else {
                $rapportsEvaluationParOrganisation = $evaluationDeGouvernance->fiches_de_synthese->groupBy(['organisationId', 'type']);

                $fiches_de_synthese = $rapportsEvaluationParOrganisation->map(function ($rapportEvaluationParOrganisation, $organisationId) use ($evaluationDeGouvernance) {

                    $organisation = app(OrganisationRepository::class)->findById($organisationId);

                    $fiches_de_synthese = $rapportEvaluationParOrganisation->map(function ($fiches_de_synthese, $type) {
                        return new FicheDeSyntheseResource($fiches_de_synthese->first());
                    });

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

    /**
     * Liste des formulaires d'une evaluation de gouvernance
     * 
     * return JsonResponse
     */
    public function formulaires_de_gouvernance($evaluationDeGouvernance, array $columns = ['*'], array $relations = [], array $appends = []): JsonResponse
    {
        try {
            if (!is_object($evaluationDeGouvernance) && !($evaluationDeGouvernance = $this->repository->findById($evaluationDeGouvernance))) throw new Exception("Evaluation de gouvernance inconnue.", 500);

            return response()->json(['statut' => 'success', 'message' => null, 'data' => FormulairesDeGouvernanceResource::collection($evaluationDeGouvernance->formulaires_de_gouvernance), 'statutCode' => Response::HTTP_OK], Response::HTTP_OK);
        } catch (\Throwable $th) {
            return response()->json(['statut' => 'error', 'message' => $th->getMessage(), 'errors' => []], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * Liste des formulaires d'une evaluation de gouvernance
     * 
     * return JsonResponse
     */
    public function formulaire_factuel_de_gouvernance($token, array $columns = ['*'], array $relations = [], array $appends = []): JsonResponse
    {
        try {
            ///if (!is_object($evaluationDeGouvernance) && !($evaluationDeGouvernance = $this->repository->findById($evaluationDeGouvernance))) throw new Exception("Evaluation de gouvernance inconnue.", 500);

            $evaluationDeGouvernance = EvaluationDeGouvernance::with(["organisations" => function ($query) use ($token) {
                $query->wherePivot('token', $token);
            }])->first();

            $organisation = $evaluationDeGouvernance->organisations->first();

            $formulaire_factuel_de_gouvernance = [];

            if ($organisation != null) {
                $formulaire_factuel_de_gouvernance = $evaluationDeGouvernance->formulaire_factuel_de_gouvernance()->load("questions_de_gouvernance.reponses", function ($query) use ($evaluationDeGouvernance, $token) {
                    $query->where('type', 'indicateur')->whereHas("soumission", function ($query) use ($evaluationDeGouvernance, $token) {
                        $query->where('evaluationId', $evaluationDeGouvernance->id)->where('organisationId', $evaluationDeGouvernance->organisations()->wherePivot('token', $token)->first()->id);
                    });
                });
            }
            return response()->json(['statut' => 'success', 'message' => null, 'data' => FormulairesDeGouvernanceResource::collection($formulaire_factuel_de_gouvernance), 'statutCode' => Response::HTTP_OK], Response::HTTP_OK);
        } catch (\Throwable $th) {
            return response()->json(['statut' => 'error', 'message' => $th->getMessage(), 'errors' => []], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * Liste des formulaires d'une evaluation de gouvernance
     * 
     * return JsonResponse
     */
    public function formulaire_de_perception_de_gouvernance($paricipant_id, $token, array $columns = ['*'], array $relations = [], array $appends = []): JsonResponse
    {
        try {
            // if (!is_object($evaluationDeGouvernance) && !($evaluationDeGouvernance = $this->repository->findById($evaluationDeGouvernance))) throw new Exception("Evaluation de gouvernance inconnue.", 500);

            $evaluationDeGouvernance = EvaluationDeGouvernance::with(["organisations" => function ($query) use ($token) {
                $query->wherePivot('token', $token);
            }])->first();

            $organisation = $evaluationDeGouvernance->organisations->first();

            $formulaire_de_perception_de_gouvernance = [];

            if ($organisation != null) {

                $formulaire_de_perception_de_gouvernance = $evaluationDeGouvernance->formulaires_de_gouvernance()->where("type", 'perception')->with([
                    'categories_de_gouvernance' => function($query) use ($evaluationDeGouvernance, $token, $paricipant_id){
                        $query->with(['questions_de_gouvernance.reponses' => function ($query) use ($evaluationDeGouvernance, $token, $paricipant_id) {
                            $query->where('type', 'question_operationnelle')->whereHas('soumission', function ($query) use ($evaluationDeGouvernance, $token, $paricipant_id) {
                                $query->where('evaluationId', $evaluationDeGouvernance->id)->where('organisationId', $evaluationDeGouvernance->organisations()->wherePivot('token', $token)->first()->id)->where('identifier_of_participant', $paricipant_id);
                            });
                        }]);
                    }
                ])->first(); 
            }

            return response()->json(['statut' => 'success', 'message' => null, 'data' => $formulaire_de_perception_de_gouvernance/* FormulairesDeGouvernanceResource::collection($formulaire_de_perception_de_gouvernance) */, 'statutCode' => Response::HTTP_OK], Response::HTTP_OK);
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
            if (!is_object($evaluationDeGouvernance) && !($evaluationDeGouvernance = $this->repository->findById($evaluationDeGouvernance))) throw new Exception("Evaluation de gouvernance inconnue.", 500);

            dump($evaluationDeGouvernance);
            if (Auth::user()->hasRole('organisation')) {
                $attributs['organisationId'] = Auth::user()->profilable->id;
            }
            dd($attributs);

            SendInvitationJob::dispatch($evaluationDeGouvernance, $attributs, 'invitation-enquete-de-collecte');

            return response()->json(['statut' => 'success', 'message' => "Invitation envoye", 'data' => null, 'statutCode' => Response::HTTP_OK], Response::HTTP_OK);
        } catch (\Throwable $th) {
            return response()->json(['statut' => 'error', 'message' => $th->getMessage(), 'errors' => []], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
}
