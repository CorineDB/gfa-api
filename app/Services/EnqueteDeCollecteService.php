<?php

namespace App\Services;

use App\Http\Resources\gouvernance\EnqueteDeGouvernanceResource;
use App\Http\Resources\gouvernance\FicheSyntheseEvaluationDePerceptionResource;
use App\Http\Resources\gouvernance\FicheSyntheseEvaluationFactuelleResource;
use App\Repositories\EnqueteDeCollecteRepository;
use App\Repositories\PrincipeDeGouvernanceRepository;
use App\Repositories\TypeDeGouvernanceRepository;
use App\Repositories\UserRepository;
use Core\Services\Contracts\BaseService;
use Core\Services\Interfaces\EnqueteDeCollecteServiceInterface;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use App\Traits\Helpers\LogActivity;

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
                throw new Exception("Cette enquete n'existe pas", 500);

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
                throw new Exception("Cette enquete n'existe pas", 500);

            $collected = $enqueteDeCollecte->reponses_collecter()->create($attributs);

            $collected->refresh();


            DB::commit();

            $acteur = Auth::check() ? Auth::user()->nom . " ". Auth::user()->prenom : "Inconnu";

            $message = Str::ucfirst($acteur) . " a collecter des donnees pour le compte de l'enquete {$collected->enquete->nom}.";

            LogActivity::addToLog("Enregistrement", $message, get_class($collected), $collected->id);

            return response()->json(['statut' => 'success', 'message' => "Les données collectée on ete enregistrer avec succes", 'data' =>  $collected, 'statutCode' => Response::HTTP_OK], Response::HTTP_OK);



            return response()->json(['statut' => 'success', 'message' => "Donnee enregistrer modifié", 'data' => [], 'statutCode' => Response::HTTP_OK], Response::HTTP_OK);
        } catch (\Throwable $th) {
            DB::rollback();
            return response()->json(['statut' => 'error', 'message' => $th->getMessage(), 'errors' => []], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    public function resultats($enqueteId, $organisationId, array $attributs = ['*'], array $relations = []): JsonResponse{

        try {
            if (!($enqueteDeCollecte = $this->repository->findById($enqueteId)))
                throw new Exception("Cette enquete n'existe pas", 500);
            
            if (!($organisation = app(UserRepository::class)->findById($organisationId)))
                throw new Exception("Cette enquete n'existe pas", 500);

            $resultats = [
                'id' => $organisation->secure_id,
                'nom' => $organisation->nom,
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
        
        $types = app(TypeDeGouvernanceRepository::class)
            ->all()
            ->load([

                'principes_de_gouvernance.indicateurs_criteres_de_gouvernance' => function ($query) use ($enqueteId, $organisationId) {
                    $query->selectRaw('
                        indicateurs_de_gouvernance.*, 
                        SUM(CASE 
                            WHEN options_de_reponse.slug = "oui" THEN 1 
                            ELSE 0 
                        END) as note
                    ')
                    ->leftJoin('reponses_collecter', 'indicateurs_de_gouvernance.id', '=', 'reponses_collecter.indicateurDeGouvernanceId')
                    ->leftJoin('options_de_reponse', 'reponses_collecter.optionDeReponseId', '=', 'options_de_reponse.id')
                    ->where('reponses_collecter.enqueteDeCollecteId', $enqueteId)
                    ->where('reponses_collecter.userId', $organisationId)
                    ->groupBy('indicateurs_de_gouvernance.id'); // Group by the indicator ID
                }
            ])
            ->each(function($type) { // Iterate over each governance type
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
            });            
    
            return FicheSyntheseEvaluationFactuelleResource::collection($types);
    }

    private function analyse_donnees_de_perception($enqueteId, $organisationId)
    {
        $types = app(PrincipeDeGouvernanceRepository::class)
                ->all()
                ->load([
                    'indicateurs_de_gouvernance' => function ($query) use ($enqueteId, $organisationId) {
                        $query->with(['options_de_reponse' => function ($subquery) use ($enqueteId, $organisationId) {
                            $subquery->withCount(['reponses' => function ($query) use ($enqueteId, $organisationId) {
                                // Filter based on the enquête and user
                                $query->where('reponses_collecter.enqueteDeCollecteId', $enqueteId)
                                    ->where('reponses_collecter.userId', $organisationId);
                            }])->addSelect([\DB::raw("
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
                                ]); // Ensure correct fields are selected
                        }]);
                    }
                ])
                ->each(function($principe) { // Iterate over each governance type
                    $nbreQO = $principe->indicateurs_de_gouvernance->count('reponses_count');
                    $moyPQO = 0;
                    $principe->indicateurs_de_gouvernance->each(function($indicateur) use(&$moyPQO){ // Iterate over each principle
                        
                        $nbreR = $indicateur->options_de_reponse->sum('reponses_count'); // Sum the notes
                        $moyPQO += $indicateur->moyPQO = $indicateur->options_de_reponse->each(function($option) use(&$nbreR){

                            if ($option->note > 0 && $option->reponses_count > 0) {
        
                                $option->moyPQOi = ($option->note * $option->reponses_count );
                            } else {
                                $option->moyPQOi = 0; // Handle case with no indicators
                            }

                        })->sum('moyPQOi') / $nbreR;

                    });
    
                    // Calculate indice_de_perception
                    if ($nbreQO > 0 && $moyPQO > 0) {
    
                        $principe->indice_de_perception = $moyPQO / $nbreQO;
                    } else {
                        $principe->indice_de_perception = 0; // Handle case with no indicators
                    }
                });

            return FicheSyntheseEvaluationDePerceptionResource::collection($types);
    }

}