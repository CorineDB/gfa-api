<?php

namespace App\Services;

use App\Http\Resources\gouvernance\AppreciationResource;
use App\Http\Resources\gouvernance\EnqueteDeCollecteResource;
use App\Http\Resources\gouvernance\EnqueteDeGouvernanceResource;
use App\Http\Resources\gouvernance\FicheSyntheseEvaluationDePerceptionResource;
use App\Http\Resources\gouvernance\FicheSyntheseEvaluationFactuelleResource;
use App\Http\Resources\OrganisationResource;
use App\Models\Organisation;
use App\Models\ReponseCollecter;
use App\Repositories\EnqueteDeCollecteRepository;
use App\Repositories\OrganisationRepository;
use App\Repositories\OptionDeReponseRepository;
use App\Repositories\PrincipeDeGouvernanceRepository;
use App\Repositories\TypeDeGouvernanceRepository;
use Core\Services\Contracts\BaseService;
use Core\Services\Interfaces\EnqueteDeCollecteServiceInterface;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use App\Traits\Helpers\LogActivity;
use Carbon\Carbon;

/**
* Interface EnqueteDeCollecteServiceInterface
* @package Core\Services\Interfaces
*/
class EnqueteDeCollecteService extends BaseService implements EnqueteDeCollecteServiceInterface
{

    /**
     * @var service    public function resultats($enqueteId, $organisationId, array $attributs = ['*'], array $relations = []): JsonResponse{

     */
    protected $repository;

    /**
     * EnqueteDeCollecteRepository constructor.
     *
     * @param EnqueteDeCollecteRepository $enqueteDeCollecteRepository
     */
    public function __construct(EnqueteDeCollecteRepository $enqueteDeCollecteRepository)
    {
        parent::__construct($enqueteDeCollecteRepository);
    }

    public function all(array $columns = ['*'], array $relations = []): JsonResponse
    {
        try
        {
            if((Auth::user()->hasRole('administrateur') || auth()->user()->profilable_type == "App\\Models\\Administrateur")){
                $enquetesDeCollecte = $this->repository->all();
            }
            else{
                //$projets = $this->repository->allFiltredBy([['attribut' => 'programmeId', 'operateur' => '=', 'valeur' => auth()->user()->programme->id]]);
                $enquetesDeCollecte = Auth::user()->programme->enquetesDeCollecte;
            }

            return response()->json(['statut' => 'success', 'message' => null, 'data' => EnqueteDeCollecteResource::collection($enquetesDeCollecte), 'statutCode' => Response::HTTP_OK], Response::HTTP_OK);
        }

        catch (\Throwable $th)
        {
            return response()->json(['statut' => 'error', 'message' => $th->getMessage(), 'errors' => []], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    public function findById($enqueteDeCollecte, array $columns = ['*'], array $relations = [], array $appends = []): JsonResponse
    {
        try
        {
            if(!is_object($enqueteDeCollecte) && !($enqueteDeCollecte = $this->repository->findById($enqueteDeCollecte))) throw new Exception("Enquete introuvable", Response::HTTP_NOT_FOUND);

            return response()->json(['statut' => 'success', 'message' => null, 'data' => new EnqueteDeCollecteResource($enqueteDeCollecte), 'statutCode' => Response::HTTP_OK], Response::HTTP_OK);
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

            $attributs = array_merge($attributs, ['programmeId' => $programme->id]);

            $enqueteDeCollecte = $this->repository->create($attributs);

            $acteur = Auth::check() ? Auth::user()->nom . " ". Auth::user()->prenom : "Inconnu";

            $message = $message ?? Str::ucfirst($acteur) . " a créé un " . strtolower(class_basename($enqueteDeCollecte));

            //LogActivity::addToLog("Enrégistrement", $message, get_class($enqueteDeCollecte), $enqueteDeCollecte->id);

            DB::commit();

            return response()->json(['statut' => 'success', 'message' => "Enregistrement réussir", 'data' => new EnqueteDeCollecteResource($enqueteDeCollecte), 'statutCode' => Response::HTTP_CREATED], Response::HTTP_CREATED);

        } catch (\Throwable $th) {

            DB::rollBack();

            //throw $th;
            return response()->json(['statut' => 'error', 'message' => $th->getMessage(), 'errors' => [], 'statutCode' => Response::HTTP_INTERNAL_SERVER_ERROR], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    public function update($enqueteDeCollecte, array $attributs) : JsonResponse
    {
        DB::beginTransaction();

        try {

            if(!is_object($enqueteDeCollecte) && !($enqueteDeCollecte = $this->repository->findById($enqueteDeCollecte))) throw new Exception("Enquete introuvable", Response::HTTP_NOT_FOUND);

            unset($attributs['programmeId']);
            $this->repository->update($enqueteDeCollecte->id, $attributs);

            $enqueteDeCollecte->refresh();

            $acteur = Auth::check() ? Auth::user()->nom . " ". Auth::user()->prenom : "Inconnu";

            $message = $message ?? Str::ucfirst($acteur) . " a modifié un " . strtolower(class_basename($enqueteDeCollecte));

            //LogActivity::addToLog("Modification", $message, get_class($enqueteDeCollecte), $enqueteDeCollecte->id);

            DB::commit();

            return response()->json(['statut' => 'success', 'message' => "Enregistrement réussir", 'data' => new EnqueteDeCollecteResource($enqueteDeCollecte), 'statutCode' => Response::HTTP_CREATED], Response::HTTP_CREATED);

        } catch (\Throwable $th) {

            DB::rollBack();

            //throw $th;
            return response()->json(['statut' => 'error', 'message' => $th->getMessage(), 'errors' => [], 'statutCode' => Response::HTTP_INTERNAL_SERVER_ERROR], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * Liste des reponses de l'enquete.
     *
     * @param  $indicateurId
     * @return Illuminate\Http\JsonResponse
     */
    public function reponses_collecter($enqueteId, array $attributs = ['*'], array $relations = []): JsonResponse
    {

        try {
            if (!($enqueteDeCollecte = $this->repository->findById($enqueteId)))
                throw new Exception("Cette enquete n'existe pas", Response::HTTP_NOT_FOUND);

            $responses = new EnqueteDeGouvernanceResource($enqueteDeCollecte);

            return response()->json(['statut' => 'success', 'message' => null, 'data' => $responses, 'statutCode' => Response::HTTP_OK], Response::HTTP_OK);
        } catch (\Throwable $th) {
            DB::rollback();
            return response()->json(['statut' => 'error', 'message' => $th->getMessage(), 'errors' => []], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * Effectuer une collecte de donnees pour le compte d'une enquete.
     *
     * @param  $indicateurId
     * @return Illuminate\Http\JsonResponse
     */
    public function collecter($enqueteId, array $attributs = ['*'], array $relations = []): JsonResponse
    {
        
        DB::beginTransaction();

        try {

            if (!($enqueteDeCollecte = $this->repository->findById($enqueteId)))
                throw new Exception("Cette enquete n'existe pas", Response::HTTP_NOT_FOUND);

            $organisationId = Auth::user()->profilable_id;

            if(!isset($attributs["organisationId"])){
                $attributs = array_merge($attributs, ['organisationId' => $organisationId]);
            }

            if (!($organisation = app(OrganisationRepository::class)->findById($attributs["organisationId"])))
                throw new Exception("Cette organisation n'existe pas", Response::HTTP_NOT_FOUND);

            $data = [];

            if (isset($attributs["response_data"]["factuel"])) {

                foreach ($attributs["response_data"]["factuel"] as $key => $factuel_data) {

                    if (!($optionDeReponse = app(OptionDeReponseRepository::class)->findById($factuel_data["optionDeReponseId"])))
                        throw new Exception("Not found", Response::HTTP_NOT_FOUND);

                    array_push($data, new ReponseCollecter(array_merge(["organisationId" => $organisation->id, "userId" => auth()->id(), "note" => $optionDeReponse->note??0], $factuel_data)));
                }

            }

            if (isset($attributs["response_data"]["perception"])) {
                foreach ($attributs["response_data"]["perception"] as $key => $perception_data) {
                    if (!($optionDeReponse = app(OptionDeReponseRepository::class)->findById($perception_data["optionDeReponseId"])))
                        throw new Exception("Not found", Response::HTTP_NOT_FOUND);
                    array_push($data, new ReponseCollecter(array_merge(["organisationId" => $organisation->id, "userId" => auth()->id(), "note" => $optionDeReponse->note??0], $perception_data)));
                }
            }
            
            $collected = $enqueteDeCollecte->reponses_collecter()->saveMany($data);

            // Optionally, retrieve the inserted data if needed
            $collected = $enqueteDeCollecte->reponses_collecter()->where('organisationId', $organisation->id)
                                    ->where('userId', auth()->id())
                                    ->get();
            DB::commit();

            $acteur = Auth::check() ? Auth::user()->nom . " ". Auth::user()->prenom : "Inconnu";

            $message = Str::ucfirst($acteur) . " a collecter des donnees pour le compte de l'enquete {$enqueteDeCollecte->nom}.";

            //LogActivity::addToLog("Enregistrement", $message, get_class($enqueteDeCollecte), $enqueteDeCollecte->id);

            return response()->json(['statut' => 'success', 'message' => "Les données collectée on ete enregistrer avec succes", 'data' =>  $collected, 'statutCode' => Response::HTTP_OK], Response::HTTP_OK);

            return response()->json(['statut' => 'success', 'message' => "Donnee enregistrer modifié", 'data' => [], 'statutCode' => Response::HTTP_OK], Response::HTTP_OK);
        } catch (\Throwable $th) {
            DB::rollback();
            return response()->json(['statut' => 'error', 'message' => $th->getMessage(), 'errors' => []], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    public function resultat_appreciations($enqueteId, $organisationId, array $attributs = ['*'], array $relations = []): JsonResponse{

        try {
            if (!($enqueteDeCollecte = $this->repository->findById($enqueteId)))
                throw new Exception("Cette enquete n'existe pas", Response::HTTP_NOT_FOUND);
            
            if (!($organisation = app(OrganisationRepository::class)->findById($organisationId)))
                throw new Exception("Cette orgsnisation n'existe pas", Response::HTTP_NOT_FOUND);

            $resultats = $enqueteDeCollecte->notes_resultat()->where($attributs)->get("*");

            return response()->json(['statut' => 'success', 'message' => null, 'data' => AppreciationResource::collection($resultats), 'statutCode' => Response::HTTP_OK], Response::HTTP_OK);
        } catch (\Throwable $th) {
            DB::rollback();
            return response()->json(['statut' => 'error', 'message' => $th->getMessage(), 'errors' => []], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    public function resultats($enqueteId, $organisationId, array $attributs = ['*'], array $relations = []): JsonResponse{

        try {
            if (!($enqueteDeCollecte = $this->repository->findById($enqueteId)))
                throw new Exception("Cette enquete n'existe pas", Response::HTTP_NOT_FOUND);
            
            if (!($organisation = app(OrganisationRepository::class)->findById($organisationId)))
                throw new Exception("Cette orgsnisation n'existe pas", Response::HTTP_NOT_FOUND);

            //$last_reponse=ReponseCollecter::where('organisationId', $organisationId)->where('enqueteDeCollecteId', $enqueteDeCollecte->id)->orderByDesc("created_at")->first();
            $resultats = [
                'id' => $organisation->secure_id,
                'nom' => $organisation->user->nom,
                /*'nom_point_focal' => $organisation->nom_point_focal ?? null,
                'prenom_point_focal' => $organisation->prenom_point_focal ?? null,
                'contact_point_focal' => $organisation->contact_point_focal ?? null,
                "submitted_by"=> optional($last_reponse)->user,
                "submitted_at"=> $last_reponse ? Carbon::parse(optional($last_reponse)->updated_at)->format("Y-m-d") : "null",                */
                'analyse_factuel' => $this->analyse_donnees_factuelle($enqueteDeCollecte->id, $organisation->id),
                'analyse_perception' => $this->analyse_donnees_de_perception($enqueteDeCollecte->id, $organisation->id)
            ];

            return response()->json(['statut' => 'success', 'message' => null, 'data' => $resultats, 'statutCode' => Response::HTTP_OK], Response::HTTP_OK);
        } catch (\Throwable $th) {
            DB::rollback();
            return response()->json(['statut' => 'error', 'message' => $th->getMessage(), 'errors' => []], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    private function analyse_donnees_factuelle($enqueteId, $organisationId)
    {
        // Initialize variables for summing the perception indices and counting the principles
        $totalIndiceFactuel = 0;
        $nbreDeTypes = 0;
        
        $programme = auth()->user()->programme;

        $types = app(TypeDeGouvernanceRepository::class)->getInstance()->where("programmeId", $programme->id)
            ->get()
            ->load([
                'principes_de_gouvernance.criteres_de_gouvernance.indicateurs_de_gouvernance' => function ($query) use ($enqueteId, $organisationId) {
                    $query->selectRaw('
                        indicateurs_de_gouvernance.*, 
                        SUM(options_de_reponse.note) as note
                    ')/*
                    $query->selectRaw('
                        indicateurs_de_gouvernance.*, 
                        SUM(CASE 
                            WHEN options_de_reponse.slug = "oui" THEN 1 
                            ELSE 0 
                        END) as note
                    ')*/
                    ->leftJoin('reponses_collecter', 'indicateurs_de_gouvernance.id', '=', 'reponses_collecter.indicateurDeGouvernanceId')
                    ->leftJoin('options_de_reponse', 'reponses_collecter.optionDeReponseId', '=', 'options_de_reponse.id')
                    ->where('reponses_collecter.enqueteDeCollecteId', $enqueteId)
                    ->where('reponses_collecter.organisationId', $organisationId) 
                    ->groupBy('indicateurs_de_gouvernance.id'); // Group by the principle (or category) of governance
                }
            ])/*
            ->each(function($type) use (&$totalIndiceFactuel, &$nbreDeTypes)  { // Iterate over each governance type
                $nbrePrincipe = 0;
                $totalScoreFactuel = 0;
                $type->principes_de_gouvernance->each(function($principle) use(&$nbrePrincipe, &$totalScoreFactuel){ // Iterate over each principle
                    // Calculate score_factuel for each principle
                    $nbreIndicateurs = $principle->indicateurs_criteres_de_gouvernance->count(); // Count the indicators
                    $totalNote = $principle->indicateurs_criteres_de_gouvernance->sum('note'); // Sum the notes
        
                    // Calculate score_factuel
                    if ($nbreIndicateurs > 0 && $totalNote > 0) {

                        $principle->score_factuel = $totalNote / $nbreIndicateurs;
                    } else {
                        $principle->score_factuel = 0; // Handle case with no indicators
                    }

                    $totalScoreFactuel+=$principle->score_factuel;

                    $nbrePrincipe++;
                });

                // Calculate indice_factuel
                if ($nbrePrincipe > 0 && $totalScoreFactuel > 0) {

                    $type->indice_factuel = $totalScoreFactuel / $nbrePrincipe;
                } else {
                    $type->indice_factuel = 0; // Handle case with no indicators
                }

                // Add the calculated factuel index to the total sum
                $totalIndiceFactuel += $type->indice_factuel;
                $nbreDeTypes++; // Count the number of governance principles
            })*/
            ->each(function($type) use (&$totalIndiceFactuel, &$nbreDeTypes)  { // Iterate over each governance type
                $nbrePrincipe = 0;
                $totalScoreFactuel = 0;
                $type->principes_de_gouvernance->each(function($principle) use(&$nbrePrincipe, &$totalScoreFactuel){ // Iterate over each principle
                    // Calculate score_factuel for each principle
                    $nbreIndicateurs = 0;//$principle->indicateurs_criteres_de_gouvernance->count(); // Count the indicators
                    $totalNote = 0;//$principle->indicateurs_criteres_de_gouvernance->sum('note'); // Sum the notes
        

                    $principle->criteres_de_gouvernance->each(function($critere) use(&$nbreIndicateurs, &$totalNote){ // Iterate over each principle
                        // Calculate score_factuel for each principle
                        $nbreIndicateurs+= $critere->indicateurs_de_gouvernance->count(); // Count the indicators
                        $totalNote+= $critere->indicateurs_de_gouvernance->sum('note'); // Sum the notes
                    });

                    // Calculate score_factuel
                    if ($nbreIndicateurs > 0 && $totalNote > 0) {

                        $principle->score_factuel = $totalNote / $nbreIndicateurs;
                    } else {
                        $principle->score_factuel = 0; // Handle case with no indicators
                    }

                    $totalScoreFactuel+=$principle->score_factuel;

                    $nbrePrincipe++;
                });

                // Calculate indice_factuel
                if ($nbrePrincipe > 0 && $totalScoreFactuel > 0) {

                    $type->indice_factuel = $totalScoreFactuel / $nbrePrincipe;
                } else {
                    $type->indice_factuel = 0; // Handle case with no indicators
                }

                // Add the calculated factuel index to the total sum
                $totalIndiceFactuel += $type->indice_factuel;
                $nbreDeTypes++; // Count the number of governance principles
            });

            return [
                "indice_factuel" => $totalIndiceFactuel ? $totalIndiceFactuel/$nbreDeTypes : 0,
                "fiche_de_synthese_factuel" => FicheSyntheseEvaluationFactuelleResource::collection($types)
            ];
    }

    private function analyse_donnees_de_perception($enqueteId, $organisationId)
    {
        // Initialize variables for summing the perception indices and counting the principles
        $totalIndiceDePerception = 0;
        $nbreDePrincipes = 0;
        
        $programme = auth()->user()->programme;
        
        $principes = app(PrincipeDeGouvernanceRepository::class)->getInstance()->whereHas("type_de_gouvernance", function($query) use ($programme){
            $query->where("programmeId", $programme->id);
        })
                ->get()
                ->load([
                    'indicateurs_de_gouvernance' => function ($query) use ($enqueteId, $organisationId) {
                        $query->with(['options_de_reponse' => function ($subquery) use ($enqueteId, $organisationId) {
                            $subquery->withCount(['reponses' => function ($query) use ($enqueteId, $organisationId) {
                                // Filter based on the enquête and user
                                $query->where('reponses_collecter.enqueteDeCollecteId', $enqueteId)
                                    ->where('reponses_collecter.organisationId', $organisationId);
                            }])->addSelect([\DB::raw("
                                        (options_de_reponse.note) AS note
                                    ")
                                ]);/*
                                ->addSelect([\DB::raw("
                                        (
                                            CASE 
                                                WHEN options_de_reponse.slug = 'ne-peux-repondre' THEN 1
                                                WHEN options_de_reponse.slug = 'pas-du-tout' THEN 2
                                                WHEN options_de_reponse.slug = 'faiblement' THEN 3
                                                WHEN options_de_reponse.slug = 'moyennement' THEN 4
                                                WHEN options_de_reponse.slug = 'dans-une-grande-mesure' THEN 5
                                                WHEN options_de_reponse.slug = 'totalement' THEN 6
                                                ELSE 0
                                            END
                                        ) AS note
                                    ")
                                ]);*/
                        }]);
                    }
                ])
                ->each(function($principe) use (&$totalIndiceDePerception, &$nbreDePrincipes) { // Iterate over each governance type
                    $nbreQO = $principe->indicateurs_de_gouvernance->count('reponses_count');
                    $moyPQO = 0;
                    $principe->indicateurs_de_gouvernance->each(function($indicateur) use(&$moyPQO){
                        // Iterate over each principle
                        
                        $nbreR = $indicateur->options_de_reponse->sum('reponses_count'); // Sum the notes
                        $moyPQOi = $indicateur->moyPQO = $indicateur->options_de_reponse->each(function($option) use(&$nbreR){

                            if ($option->note > 0 && $option->reponses_count > 0) {
        
                                $option->moyPQOi = ($option->note * $option->reponses_count );
                            } else {
                                $option->moyPQOi = 0; // Handle case with no indicators
                            }

                        })->sum('moyPQOi');
    
                        // Calculate indice_de_perception
                        if ($nbreR > 0 && $moyPQOi > 0) {
        
                            $moyPQO += $moyPQOi / $nbreR;
                        } else {
                            $moyPQO += 0; // Handle case with no indicators
                        }
                        
                        //$moyPQO += $moyPQOi / $nbreR;

                    });
    
                    // Calculate indice_de_perception
                    if ($nbreQO > 0 && $moyPQO > 0) {
    
                        $principe->indice_de_perception = $moyPQO / $nbreQO;
                    } else {
                        $principe->indice_de_perception = 0; // Handle case with no indicators
                    }

                    // Add the calculated perception index to the total sum
                    $totalIndiceDePerception += $principe->indice_de_perception;
                    $nbreDePrincipes++; // Count the number of governance principles
                });

            return [
                "indice_de_perception" => $totalIndiceDePerception ? $totalIndiceDePerception/$nbreDePrincipes : 0,
                "fiche_de_synthese_de_perception" => FicheSyntheseEvaluationDePerceptionResource::collection($principes)
            ];
    }

    /**
     * Appreciation
     *
     * @param  $enqueteId
     * @return Illuminate\Http\JsonResponse
     */
    public function appreciation($enqueteId, array $attributs = ['*'], array $relations = []): JsonResponse
    {
        
        DB::beginTransaction();


        try {

            if (!($enqueteDeCollecte = $this->repository->findById($enqueteId)))
                throw new Exception("Cette enquete n'existe pas", Response::HTTP_NOT_FOUND);

            if (!app(OrganisationRepository::class)->findById($attributs["organisationId"]))
                throw new Exception("Cette organisation n'existe pas", Response::HTTP_NOT_FOUND);


            $note = $enqueteDeCollecte->notes_resultat()->create(array_merge($attributs, ["userId" => auth()->id()]));


            DB::commit();

            $acteur = Auth::check() ? Auth::user()->nom . " ". Auth::user()->prenom : "Inconnu";

            $message = Str::ucfirst($acteur) . " a collecter des donnees pour le compte de l'enquete {$note->nom}.";

            //LogActivity::addToLog("Enregistrement", $message, get_class($note), $note->id);

            return response()->json(['statut' => 'success', 'message' => "Les données collectée on ete enregistrer avec succes", 'data' => new AppreciationResource($note), 'statutCode' => Response::HTTP_OK], Response::HTTP_OK);

        } catch (\Throwable $th) {
            DB::rollback();
            return response()->json(['statut' => 'error', 'message' => $th->getMessage(), 'errors' => []], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
    
    /**
     * Retrieve a list of organisations that have not yet responded to a survey.
     *
     * This method will return a list of organisations that are part of the same
     * programme as the authenticated user, and that have not yet submitted a
     * response to the given survey.
     *
     * @param  String  $enqueteId The ID of the survey (enquete) to retrieve the waiting list for.
     * @return \Illuminate\Http\JsonResponse A JSON response containing the list of organisations that have not yet submitted responses to the survey.
     */
    public function surveyEligibleParticipants($enqueteId): JsonResponse
    {
        try {
            // Retrieve the survey record from the database
            if (!($enqueteDeCollecte = $this->repository->findById($enqueteId)))
                throw new Exception("Cette enquete n'existe pas", Response::HTTP_NOT_FOUND);

            // Filter organisations that are part of the same programme as the authenticated user
            // and have not yet submitted a response to the given survey
            $organisations = Organisation::whereHas("user", function($query) {
                return $query->where("programmeId", auth()->user()->programmeId);
            })->whereNotExists(function($query) use ($enqueteDeCollecte) {
                $query->select(DB::raw(1))
                    ->from("reponses_collecter")
                    ->whereRaw("reponses_collecter.organisationId = organisations.id")
                    ->where("reponses_collecter.enqueteDeCollecteId", $enqueteDeCollecte->id);
            })
            ->get();

            /*$enqueteId = $enqueteDeCollecte->id;
            
            $organisations = Organisation::whereHas("user", function($query) {
                return $query->where("programmeId", auth()->user()->programmeId);
            })->join("reponses_collecter", function($join) use ($enqueteId) {
                $join->on("organisations.id", "=", "reponses_collecter.organisationId")
                     ->where("reponses_collecter.enqueteDeCollecteId", $enqueteId);
            })
            ->get("id","nom");*/

            // Return the list of organisations in a JSON response
            return response()->json(['statut' => 'success', 'message' => null, 'data' => OrganisationResource::collection($organisations), 'statutCode' => Response::HTTP_OK], Response::HTTP_OK);
        } catch (\Throwable $th) {
            // Handle any errors that may occur
            DB::rollback();
            return response()->json(['statut' => 'error', 'message' => $th->getMessage(), 'errors' => []], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * Renvoie la liste des organisations qui ont deja repondu a l'enquête
     * 
     * @param  String  $enqueteId The ID of the survey (enquete) to retrieve the submitted list for.
     * @return \Illuminate\Http\JsonResponse A JSON response containing the list of organizations that have submitted responses to the survey.
     */
    public function surveySubmittedParticipants($enqueteId): JsonResponse{
        // Retrieve the list of submitted organisations for the given survey ID
        // The query will join the organisations and responses_collecter tables
        // and will filter out the organisations that have not submitted a response
        // for the given survey ID
        try {
            if (!($enqueteDeCollecte = $this->repository->findById($enqueteId)))
                throw new Exception("Cette enquete n'existe pas", Response::HTTP_NOT_FOUND);

            $enqueteId = $enqueteDeCollecte->id;
            $organisations = Organisation::join("reponses_collecter", "organisations.id", "=", "reponses_collecter.organisationId")
            ->where("reponses_collecter.enqueteDeCollecteId", $enqueteId)
            ->get();

            return response()->json(['statut' => 'success', 'message' => null, 'data' => OrganisationResource::collection($organisations), 'statutCode' => Response::HTTP_OK], Response::HTTP_OK);
        } catch (\Throwable $th) {
            DB::rollback();
            return response()->json(['statut' => 'error', 'message' => $th->getMessage(), 'errors' => []], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

}