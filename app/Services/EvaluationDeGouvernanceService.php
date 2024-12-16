<?php

namespace App\Services;

use App\Http\Resources\gouvernance\ActionsAMenerResource;
use App\Http\Resources\gouvernance\EvaluationsDeGouvernanceResource;
use App\Http\Resources\gouvernance\FicheDeSyntheseResource;
use App\Http\Resources\gouvernance\FormulairesDeGouvernanceResource;
use App\Http\Resources\gouvernance\RecommandationsResource;
use App\Http\Resources\gouvernance\SoumissionsResource;
use App\Http\Resources\OrganisationResource;
use App\Jobs\RappelJob;
use App\Jobs\SendInvitationJob;
use App\Mail\InvitationEnqueteDeCollecteEmail;
use App\Models\ActionAMener;
use App\Models\EvaluationDeGouvernance;
use App\Models\Organisation;
use App\Repositories\EvaluationDeGouvernanceRepository;
use App\Repositories\OrganisationRepository;
use Core\Services\Contracts\BaseService;
use Core\Services\Interfaces\EvaluationDeGouvernanceServiceInterface;
use Exception;
use App\Traits\Helpers\LogActivity;
use Carbon\Carbon;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;

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
            } else if ((Auth::user()->hasRole('organisation') || ( get_class(auth()->user()->profilable) == Organisation::class))) {
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


            $url = config("app.url");

            // If the URL is localhost, append the appropriate IP address and port
            if (strpos($url, 'localhost') !== false) {
                $url = 'http://192.168.1.16:3000';
            }

            if (Auth::user()->hasRole('administrateur')) {
                $group_soumissions = [];
            } else if ((Auth::user()->hasRole('organisation') || ( get_class(auth()->user()->profilable) == Organisation::class))) {

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
                    'contact_point_focal'   => $organisation->contact_point_focal,
                    'pourcentage_evolution_des_soumissions_de_perception'   => $organisation->getPerceptionSubmissionsCompletionAttribute($evaluationDeGouvernance->id)
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
                            ->groupBy('type')->map(function ($soumissions, $type) {return SoumissionsResource::collection($soumissions);}); // Group submissions by type

                        return array_merge([
                            "id"                    => $organisation->secure_id,
                            'nom'                   => optional($organisation->user)->nom ?? null,
                            'sigle'                 => $organisation->sigle,
                            'code'                  => $organisation->code,
                            'nom_point_focal'       => $organisation->nom_point_focal,
                            'prenom_point_focal'    => $organisation->prenom_point_focal,
                            'contact_point_focal'   => $organisation->contact_point_focal,
                            "lien_factuel"          => $url . "/dashboard/tools-factuel/{$organisation->pivot->token}",
                            "lien_perception"       => $url . "/dashboard/tools-perception/{$organisation->pivot->token}",
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

            if (Auth::user()->hasRole('administrateur')) {
                $fiches_de_synthese = [];
            } else if ((Auth::user()->hasRole('organisation') || ( get_class(auth()->user()->profilable) == Organisation::class))) {

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

    private function getCategories($categories, $fiche){
        return collect($categories)->map(function($category) use($fiche) {

            if(isset($category['score_factuel'])){
                $categoryScoreRanges = [
                    '0-0.25' => ['organisations' => []],
                    '0.25-0.50' => ['organisations' => []],
                    '0.50-0.75' => ['organisations' => []],
                    '0.75-1' => ['organisations' => []],
                ];

                $scoreFactuel = $category['score_factuel'];
                $organisationId =  $fiche->organisationId;

                // Logic for organizing into score ranges (adjust based on actual criteria)
                if ($scoreFactuel >= 0 && $scoreFactuel <= 0.25) {
                    $categoryScoreRanges['0-0.25']['organisations'][] = ['id' => $organisationId, 'score_factuel' => $scoreFactuel]; // Assuming you have this info in the fiche
                } elseif ($scoreFactuel > 0.25 && $scoreFactuel <= 0.50) {
                    $categoryScoreRanges['0.25-0.50']['organisations'][] = ['id' => $organisationId, 'score_factuel' => $scoreFactuel];
                } elseif ($scoreFactuel > 0.50 && $scoreFactuel <= 0.75) {
                    $categoryScoreRanges['0.50-0.75']['organisations'][] = ['id' => $organisationId, 'score_factuel' => $scoreFactuel];
                } elseif ($scoreFactuel > 0.75 && $scoreFactuel <= 1) {
                    $categoryScoreRanges['0.75-1']['organisations'][] = ['id' => $organisationId, 'score_factuel' => $scoreFactuel];
                }

                $category['score_ranges'] = $categoryScoreRanges;

                if(isset($category['categories_de_gouvernance'])){
                    $category['categories_de_gouvernance'] = $this->getCategories($category['categories_de_gouvernance'], $fiche);
                }
            }

            if(isset($category['questions_de_gouvernance'])){
                $category['questions_de_gouvernance'] = $this->getQuestions($category['questions_de_gouvernance'], $fiche);
            }

            return $category;
        })->values();
    }

    private function getQuestions($questions, $fiche){
        return collect($questions)->map(function($question) use($fiche) {

            //if(isset($question['reponse'])){
                $questionScoreRanges = [
                    '0-0.25' => ['organisations' => []],
                    '0.25-0.50' => ['organisations' => []],
                    '0.50-0.75' => ['organisations' => []],
                    '0.75-1' => ['organisations' => []],
                ];

                $point = $question['reponse']['point'];
                $organisationId =  $fiche->organisationId;

                // Logic for organizing into score ranges (adjust based on actual criteria)
                if ($point >= 0 && $point <= 0.25) {
                    $questionScoreRanges['0-0.25']['organisations'][] = ['id' => $organisationId, 'score_factuel' => $point]; // Assuming you have this info in the fiche
                } elseif ($point > 0.25 && $point <= 0.50) {
                    $questionScoreRanges['0.25-0.50']['organisations'][] = ['id' => $organisationId, 'score_factuel' => $point];
                } elseif ($point > 0.50 && $point <= 0.75) {
                    $questionScoreRanges['0.50-0.75']['organisations'][] = ['id' => $organisationId, 'score_factuel' => $point];
                } elseif ($point > 0.75 && $point <= 1) {
                    $questionScoreRanges['0.75-1']['organisations'][] = ['id' => $organisationId, 'score_factuel' => $point];
                }

                $question['score_ranges'] = $questionScoreRanges;
            //}

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

           /*  $fiches = $evaluationDeGouvernance->fiches_de_synthese_factuel;
            // Initialize the array for transformed result
            $finalResults = [];

            // Loop through each record
            foreach ($fiches as $fiche) {
                $synthese = $fiche->synthese;//json_decode($fiche->synthese, true); // Decode the JSON column

                foreach ($synthese as $syntheseItem) {
                    $syntheseId = $syntheseItem['id'];
                    $syntheseName = $syntheseItem['nom'];
                    $indiceFactuel = $syntheseItem['indice_factuel'];
                    $categories = $syntheseItem['categories_de_gouvernance'];

                    // Initialize score ranges
                    $scoreRanges = [
                        '0-0.25' => ['organisations' => []],
                        '0.25-0.50' => ['organisations' => []],
                        '0.50-0.75' => ['organisations' => []],
                        '0.75-1' => ['organisations' => []],
                    ];

                    // Logic for organizing into score ranges (adjust based on actual criteria)
                    if ($indiceFactuel >= 0 && $indiceFactuel <= 0.25) {
                        $scoreRanges['0-0.25']['organisations'][] = ['id' => $fiche->organisationId, 'indice_factuel' => $indiceFactuel]; // Assuming you have this info in the fiche
                    } elseif ($indiceFactuel > 0.25 && $indiceFactuel <= 0.50) {
                        $scoreRanges['0.25-0.50']['organisations'][] = ['id' => $fiche->organisationId, 'indice_factuel' => $indiceFactuel];
                    } elseif ($indiceFactuel > 0.50 && $indiceFactuel <= 0.75) {
                        $scoreRanges['0.50-0.75']['organisations'][] = ['id' => $fiche->organisationId, 'indice_factuel' => $indiceFactuel];
                    } elseif ($indiceFactuel > 0.75 && $indiceFactuel <= 1) {
                        $scoreRanges['0.75-1']['organisations'][] = ['id' => $fiche->organisationId, 'indice_factuel' => $indiceFactuel];
                    }

                    $categories = $this->getCategories($categories, $fiche);

                    // Construct the final result for this synthese item
                    $finalResults['factuel'][] = [
                        'id' => $syntheseId,
                        'nom' => $syntheseName,
                        'indice_factuel' => $indiceFactuel,
                        'score_ranges' => $scoreRanges,
                        'categories_de_gouvernance' => $categories
                    ];
                }
            } */

            $formulaire_de_perception_de_gouvernance = $evaluationDeGouvernance->formulaire_de_perception_de_gouvernance();

            $formulaire_de_perception_de_gouvernance = $formulaire_de_perception_de_gouvernance->categories_de_gouvernance->map(function($category_de_gouvernance) use($evaluationDeGouvernance) {

                $fiches = $evaluationDeGouvernance->fiches_de_synthese_de_perception;

                // Initialize score ranges
                $scoreRanges = [
                    '0-0.25' => ['organisations' => []],
                    '0.25-0.50' => ['organisations' => []],
                    '0.50-0.75' => ['organisations' => []],
                    '0.75-1' => ['organisations' => []],
                ];

                // Loop through each record
                foreach ($fiches as $fiche) {
                    $synthese = $fiche->synthese;

                    foreach ($synthese as $syntheseItem) {
                        if($syntheseItem['id'] == $category_de_gouvernance->id){
                            $indiceFactuel = $syntheseItem['indice_de_perception'];
                            //$categories = $syntheseItem['categories_de_gouvernance'];

                            // Logic for organizing into score ranges (adjust based on actual criteria)
                            if ($indiceFactuel >= 0 && $indiceFactuel <= 0.25) {
                                $scoreRanges['0-0.25']['organisations'][] = ['id' => $fiche->organisationId, 'indice_de_perception' => $indiceFactuel]; // Assuming you have this info in the fiche
                            } elseif ($indiceFactuel > 0.25 && $indiceFactuel <= 0.50) {
                                $scoreRanges['0.25-0.50']['organisations'][] = ['id' => $fiche->organisationId, 'indice_de_perception' => $indiceFactuel];
                            } elseif ($indiceFactuel > 0.50 && $indiceFactuel <= 0.75) {
                                $scoreRanges['0.50-0.75']['organisations'][] = ['id' => $fiche->organisationId, 'indice_de_perception' => $indiceFactuel];
                            } elseif ($indiceFactuel > 0.75 && $indiceFactuel <= 1) {
                                $scoreRanges['0.75-1']['organisations'][] = ['id' => $fiche->organisationId, 'indice_de_perception' => $indiceFactuel];
                            }

                            /* $categories = $this->getCategories($categories, $fiche);

                            // Construct the final result for this synthese item
                            $finalResults['perception'][] = [
                                'indice_de_perception' => $indiceFactuel,
                                'score_ranges' => $scoreRanges,
                                'categories_de_gouvernance' => $categories
                            ]; */
                        }
                    }
                }

                dd($scoreRanges);
                $category_de_gouvernance->score_ranges = $scoreRanges;
            });
            

            return response()->json(['statut' => 'success', 'message' => null, 'data' => ["perception" => $formulaire_de_perception_de_gouvernance], 'statutCode' => Response::HTTP_OK], Response::HTTP_OK);

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
                    ], $fiches_de_synthese->toArray());
                })->values();
            
            return response()->json(['statut' => 'success', 'message' => null, 'data' => $fiches_de_synthese, 'statutCode' => Response::HTTP_OK], Response::HTTP_OK);
        } catch (\Throwable $th) {
            return response()->json(['statut' => 'error', 'message' => $th->getMessage(), 'errors' => []], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    public function feuille_de_route($evaluationDeGouvernance, array $columns = ['*'], array $relations = [], array $appends = []): JsonResponse
    {
        try {
            if (!is_object($evaluationDeGouvernance) && !($evaluationDeGouvernance = $this->repository->findById($evaluationDeGouvernance))) throw new Exception("Evaluation de gouvernance inconnue.", 500);

            $feuille_de_route = [];

            if ((Auth::user()->hasRole('organisation') || ( get_class(auth()->user()->profilable) == Organisation::class))) {
                $feuille_de_route = $evaluationDeGouvernance->load(["recommandations" => function($query){
                    $query->where("organisationId", auth()->user()->profilable->id)->with(["actions_a_mener" => function($query){
                        $query->where("organisationId", auth()->user()->profilable->id);
                    }]);
                },"actions_a_mener" => function($query){
                        $query->where("organisationId", auth()->user()->profilable->id)->whereDoesntHave("actionable");
                    }]);
            }
            else{
                $feuille_de_route = $evaluationDeGouvernance->actions_a_mener;
            }

            return response()->json(['statut' => 'success', 'message' => null, 'data' => ['recommandations' => RecommandationsResource::collection($feuille_de_route->recommandations),'actions_a_mener' => ActionsAMenerResource::collection($feuille_de_route->actions_a_mener)], 'statutCode' => Response::HTTP_OK], Response::HTTP_OK);
        } catch (\Throwable $th) {
            return response()->json(['statut' => 'error', 'message' => $th->getMessage(), 'errors' => []], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    public function actions_a_mener($evaluationDeGouvernance, array $columns = ['*'], array $relations = [], array $appends = []): JsonResponse
    {
        try {
            if (!is_object($evaluationDeGouvernance) && !($evaluationDeGouvernance = $this->repository->findById($evaluationDeGouvernance))) throw new Exception("Evaluation de gouvernance inconnue.", 500);

            $actions_a_mener = [];

            if ((Auth::user()->hasRole('organisation') || ( get_class(auth()->user()->profilable) == Organisation::class))) {
                $actions_a_mener = $evaluationDeGouvernance->actions_a_mener()->where("organisationId", auth()->user()->profilable->id)->get();
            }
            else{
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

            if ((Auth::user()->hasRole('organisation') || ( get_class(auth()->user()->profilable) == Organisation::class))) {
                $recommandations = $evaluationDeGouvernance->recommandations()->where("organisationId", auth()->user()->profilable->id)->get();
            }
            else{
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
    public function formulaire_factuel($evaluationDeGouvernance, array $columns = ['*'], array $relations = [], array $appends = []): JsonResponse
    {
        try {

            if((!Auth::user()->hasRole('organisation') && (get_class(auth()->user()->profilable) != Organisation::class))){

                return response()->json(['statut' => 'error', 'message' => "Pas la permission pour", 'data' => null, 'statutCode' => Response::HTTP_FORBIDDEN], Response::HTTP_FORBIDDEN);

            }

            else if(Auth::user()->profilable === null){
                return response()->json(['statut' => 'error', 'message' => "Unknown", 'data' => null, 'statutCode' => Response::HTTP_OK], Response::HTTP_OK);
            }


            if (!is_object($evaluationDeGouvernance) && !($evaluationDeGouvernance = $this->repository->findById($evaluationDeGouvernance))) throw new Exception("Evaluation de gouvernance inconnue.", 500);


            if($evaluationDeGouvernance->statut==1){

                return response()->json(['statut' => 'success', 'message' => "Lien expire", 'data' => null, 'statutCode' => Response::HTTP_NO_CONTENT], Response::HTTP_NO_CONTENT);

            }

            $organisation = $evaluationDeGouvernance->organisations(Auth::user()->profilable->id)->first();

            $terminer = false;

            if ($organisation != null) {
                if($soumission = $evaluationDeGouvernance->soumissionFactuel($organisation->id)->first()){
                    if($soumission->statut === true){
                        $terminer = true;
                    }
                }
            }

            return response()->json(['statut' => 'success', 'message' => null, 'data' => [
                'token' => $organisation->pivot->token,
                'terminer' => $terminer], 'statutCode' => Response::HTTP_OK], Response::HTTP_OK);
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

            if(!($evaluationDeGouvernance = EvaluationDeGouvernance::whereHas("organisations", function ($query) use ($token) {
                $query->where('evaluation_organisations.token', $token);
            })->with(["organisations" => function ($query) use ($token) {
                $query->wherePivot('token', $token);
            }])->first())) throw new Exception("Evaluation de gouvernance inconnue.", 500);

            if($evaluationDeGouvernance->statut==1){
                return response()->json(['statut' => 'success', 'message' => "Lien expire", 'data' => null, 'statutCode' => Response::HTTP_NO_CONTENT], Response::HTTP_NO_CONTENT);
            }

            $organisation = $evaluationDeGouvernance->organisations->first();
            $terminer = false;

            if ($organisation != null) {

                if($soumission = $evaluationDeGouvernance->soumissionFactuel($organisation->id)->first()){
                    
                    if($soumission->statut === true){
                        $terminer = true;
                        $formulaire_factuel_de_gouvernance = false;

                        return response()->json(['statut' => 'success', 'message' => "Soumission deja valider", 'data' => ['terminer' => $terminer, 'idEvaluation' => $evaluationDeGouvernance->secure_id, 'idSoumission' => $soumission->secure_id], 'statutCode' => Response::HTTP_PARTIAL_CONTENT], Response::HTTP_PARTIAL_CONTENT);
                    }
                    else{
                        $formulaire_factuel_de_gouvernance = new FormulairesDeGouvernanceResource($soumission->formulaireDeGouvernance, true, $soumission->id);
                    }
                }
                /*$formulaire_factuel_de_gouvernance = $evaluationDeGouvernance->formulaire_factuel_de_gouvernance()->load("questions_de_gouvernance.reponses", function ($query) use ($evaluationDeGouvernance, $token) {
                    $query->where('type', 'indicateur')->whereHas("soumission", function ($query) use ($evaluationDeGouvernance, $token) {
                        $query->where('evaluationId', $evaluationDeGouvernance->id)->where('organisationId', $evaluationDeGouvernance->organisations()->wherePivot('token', $token)->first()->id);
                    });
                });*/

                else{
                    $formulaire_factuel_de_gouvernance = new FormulairesDeGouvernanceResource($evaluationDeGouvernance->formulaire_factuel_de_gouvernance());
                }
            }
            else{
                $formulaire_factuel_de_gouvernance = new FormulairesDeGouvernanceResource($evaluationDeGouvernance->formulaire_factuel_de_gouvernance());
            }

            return response()->json(['statut' => 'success', 'message' => null, 'data' => [
                'id'                => $evaluationDeGouvernance->secure_id,
                'intitule' => $evaluationDeGouvernance->intitule,
                'description' => $evaluationDeGouvernance->description,
                'objectif_attendu' => $evaluationDeGouvernance->objectif_attendu,
                'debut' => Carbon::parse($evaluationDeGouvernance->debut)->format("Y-m-d"),
                'fin' => Carbon::parse($evaluationDeGouvernance->fin)->format("Y-m-d"),
                'annee_exercice' => $evaluationDeGouvernance->annee_exercice,
                'statut' => $evaluationDeGouvernance->statut,
                'terminer' => $terminer,
                'programmeId' => $evaluationDeGouvernance->programme->secure_id, 'formulaire_de_gouvernance' => $formulaire_factuel_de_gouvernance], 'statutCode' => Response::HTTP_OK], Response::HTTP_OK);
        } catch (\Throwable $th) {
            return response()->json(['statut' => 'error', 'message' => $th->getMessage(), 'errors' => []], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * Liste des formulaires d'une evaluation de gouvernance
     * 
     * return JsonResponse
     */
    public function formulaire_de_perception_de_gouvernance(string $paricipant_id, string $token, array $columns = ['*'], array $relations = [], array $appends = []): JsonResponse
    {
        try {
            if(!($evaluationDeGouvernance = EvaluationDeGouvernance::whereHas("organisations", function ($query) use ($token) {
                $query->where('evaluation_organisations.token', $token);
            })->with(["organisations" => function ($query) use ($token) {
                $query->wherePivot('token', $token);
            }])->first())) throw new Exception("Evaluation de gouvernance inconnue.", 500);


            if($evaluationDeGouvernance->statut == 1){
                return response()->json(['statut' => 'success', 'message' => "Lien expire", 'data' => null, 'statutCode' => Response::HTTP_NO_CONTENT], Response::HTTP_NO_CONTENT);
            }

            $organisation = $evaluationDeGouvernance->organisations->first();
            $terminer = false;

            if ($organisation != null) {

                /* if($evaluationDeGouvernance->soumissionsDePerception(null, $organisation->id)->where('statut', true)->count() == $organisation->pivot->nbreParticipants){
                    return response()->json(['statut' => 'success', 'message' => "Quota des soumissions atteints", 'data' => ['terminer' => true, 'formulaire_de_gouvernance' => null], 'statutCode' => Response::HTTP_OK], Response::HTTP_OK);
                } */

                if(($soumission = $evaluationDeGouvernance->soumissionDePerception($paricipant_id, $organisation->id)->first())){

                    if($soumission->statut === true){
                        $terminer = true;
                        $formulaire_de_perception_de_gouvernance = false;
                    }
                    else{
                        $formulaire_de_perception_de_gouvernance = new FormulairesDeGouvernanceResource($soumission->formulaireDeGouvernance, true, $soumission->id);
                    }
                }
                else{
                    $formulaire_de_perception_de_gouvernance = new FormulairesDeGouvernanceResource($evaluationDeGouvernance->formulaire_de_perception_de_gouvernance());
                }
            }
            else{
                $formulaire_de_perception_de_gouvernance = new FormulairesDeGouvernanceResource($evaluationDeGouvernance->formulaire_de_perception_de_gouvernance());
            }

            return response()->json(['statut' => 'success', 'message' => null, 'data' => [
                'id' => $evaluationDeGouvernance->secure_id,
                'intitule' => $evaluationDeGouvernance->intitule,
                'description' => $evaluationDeGouvernance->description,
                'objectif_attendu' => $evaluationDeGouvernance->objectif_attendu,
                'debut' => Carbon::parse($evaluationDeGouvernance->debut)->format("Y-m-d"),
                'fin' => Carbon::parse($evaluationDeGouvernance->fin)->format("Y-m-d"),
                'annee_exercice' => $evaluationDeGouvernance->annee_exercice,
                'statut' => $evaluationDeGouvernance->statut,
                'terminer' => $terminer,
                'programmeId' => $evaluationDeGouvernance->programme->secure_id, 'formulaire_de_gouvernance' => $formulaire_de_perception_de_gouvernance], 'statutCode' => Response::HTTP_OK], Response::HTTP_OK);
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

            if ((Auth::user()->hasRole('organisation') || ( get_class(auth()->user()->profilable) == Organisation::class))) {
                $attributs['organisationId'] = Auth::user()->profilable->id;
            }
            else{
                return response()->json(['statut' => 'error', 'message' => "Pas le droit", 'data' => null, 'statutCode' => Response::HTTP_FORBIDDEN], Response::HTTP_FORBIDDEN);
            }

            SendInvitationJob::dispatch($evaluationDeGouvernance, $attributs, 'invitation-enquete-de-collecte');

            return response()->json(['statut' => 'success', 'message' => "Invitation envoye", 'data' => null, 'statutCode' => Response::HTTP_OK], Response::HTTP_OK);
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

            if (!(Auth::user()->hasRole('organisation')) && ( get_class(auth()->user()->profilable) != Organisation::class)) {
                return response()->json(['statut' => 'error', 'message' => "Pas la permission pour", 'data' => null, 'statutCode' => Response::HTTP_FORBIDDEN], Response::HTTP_FORBIDDEN);
            }

            $organisationId = Auth::user()->profilable->id;

            if (($evaluationOrganisation = $evaluationDeGouvernance->organisations($organisationId)->first())) {

                $participants = [];
                // Decode and merge participants from the organisation's pivot data
                $participants = array_merge($participants, $evaluationOrganisation->pivot->participants ? json_decode($evaluationOrganisation->pivot->participants, true) : []);
                
                // Filter participants for those with "email" contact type
                $emailParticipants = array_filter($participants, function ($participant) {
                    return $participant["type_de_contact"] === "email";
                });

                // Extract email addresses for Mail::to()
                $emailAddresses = array_column($emailParticipants, 'email');
            
                // Send the email if there are any email addresses
                if (!empty($emailAddresses)) {

                    $url = config("app.url");

                    // If the URL is localhost, append the appropriate IP address and port
                    if (strpos($url, 'localhost') !== false) {
                        $url = 'http://192.168.1.16:3000';
                    }

                    $details['view'] = "emails.auto-evaluation.rappel_soumission_participant";

                    $details['subject'] = "Rappel : Soumission à l'auto-évaluation de gouvernance";
                    $details['content'] = [
                        "greeting" => "Bonjour, Monsieur/Madame!",
                        //"introduction" => "Nous vous rappelons que la soumission de votre évaluation de gouvernance pour le programme **{$evaluationDeGouvernance->programme->nom}** (année d'exercice **{$evaluationDeGouvernance->annee_exercice}**) est en attente.",
                        //"introduction" => "L'organisation **{$evaluationOrganisation->user->nom}** vous a invité(e) à participer à son enquête d'auto-évaluation dans le cadre du programme **{$evaluationDeGouvernance->programme->nom}** (année d'exercice **{$evaluationDeGouvernance->annee_exercice}**).",
                        "introduction" => "Nous, **{$evaluationOrganisation->user->nom}**, vous rappelons votre participation à notre enquête d'auto-évaluation de gouvernance. Votre contribution est essentielle pour renforcer notre gouvernance dans le cadre du programme **{$evaluationDeGouvernance->programme->nom}**, année d'exercice **{$evaluationDeGouvernance->annee_exercice}**.",

                        "body" => "Votre contribution est essentielle pour finaliser cette étape cruciale. Merci de compléter votre soumission dans les plus brefs délais.",
                        //"body" => "Nous comptons sur votre retour pour atteindre nos objectifs de transparence et d'amélioration continue.",

                        "lien" => $url . "/dashboard/tools-perception/{$evaluationOrganisation->pivot->token}",
                        "cta_text" => "Accéder au formulaire",
                        "signature" => "Cordialement, {$evaluationOrganisation->user->nom}",
                    ];

                    // Create the email instance
                    $mailer = new InvitationEnqueteDeCollecteEmail($details);

                    // Send the email later after a delay
                    $when = now()->addSeconds(5);
                    Mail::to($emailAddresses)->later($when, $mailer);
                }
            }

            return response()->json(['statut' => 'success', 'message' => "Rappel envoye", 'data' => null, 'statutCode' => Response::HTTP_OK], Response::HTTP_OK);
        } catch (\Throwable $th) {
            return response()->json(['statut' => 'error', 'message' => $th->getMessage(), 'errors' => []], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    public function resultats_syntheses($evaluationDeGouvernance) : JsonResponse
    {
        try
        {            
            if (!is_object($evaluationDeGouvernance) && !($evaluationDeGouvernance = $this->repository->findById($evaluationDeGouvernance))) throw new Exception("Evaluation de gouvernance inconnue.", 500);

            $resultats_syntheses = [];

            $organisationId = null;
            
            if((auth()->user()->type == "organisation") || get_class(auth()->user()->profilable) == Organisation::class){
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

        }
        catch (\Throwable $th)
        {
            return response()->json(['statut' => 'error', 'message' => $th->getMessage(), 'errors' => []], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
}
