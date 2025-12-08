<?php

namespace App\Console\Commands;

use App\Http\Resources\gouvernance\FicheSyntheseEvaluationDePerceptionResource;
use App\Http\Resources\gouvernance\FicheDeSyntheseEvaluationFactuelleResource;
use App\Models\EvaluationDeGouvernance;
use App\Models\FormulaireDeGouvernance;
use App\Models\ProfileDeGouvernance;
use App\Models\Soumission;
use App\Repositories\EvaluationDeGouvernanceRepository;
use App\Repositories\FicheDeSyntheseRepository;
use Illuminate\Console\Command;
use Illuminate\Database\Eloquent\Collection;

class GenerateEvaluation extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'generate:report-evaluation-resultats {evaluationId}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Generates evaluation results for all soumissions';

    /**
     * The EvaluationDeGouvernance model instance.
     *
     * @var EvaluationDeGouvernance
     */
    protected $evaluationDeGouvernance;

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct(EvaluationDeGouvernance $evaluationDeGouvernance)
    {
        parent::__construct();
        $this->evaluationDeGouvernance = $evaluationDeGouvernance;
    }

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        // Récupérer l'ID d'évaluation passé en argument
        $evaluationId = $this->argument('evaluationId');
        $this->evaluationDeGouvernance = app(EvaluationDeGouvernanceRepository::class)->findById($evaluationId);

        $this->generateResultForEvaluation($this->evaluationDeGouvernance);

        $this->info("Generated result for soumission ID {$this->evaluationDeGouvernance->id}:");
        return 0; // Indicates successful execution
    }
    protected function generateResultForEvaluation(EvaluationDeGouvernance $evaluationDeGouvernance)
    {
        $organisation_group_soumissions = $evaluationDeGouvernance->soumissions->groupBy(['organisationId', 'type']);

        foreach ($organisation_group_soumissions as $organisationId => $groups_soumissions) {

            $profile = null;

            foreach ($groups_soumissions as $group_soumission => $soumissions) {
                if(!$evaluationOrganisationId = $evaluationDeGouvernance->organisations()->wherePivot('organisationId', $organisationId)->first()->pivot){
                    return;
                }

                $evaluationOrganisationId = $evaluationOrganisationId->id;

                if ($group_soumission === "factuel") {

                    [$indice_factuel, $results, $synthese] = $this->generateSyntheseForFactuelleSoumission($soumissions->first(), $organisationId);

                    if ($fiche_de_synthese = $evaluationDeGouvernance->fiches_de_synthese($organisationId, $group_soumission)->first()) {
                        $fiche_de_synthese->update(['type' => 'factuel', 'indice_de_gouvernance' => $indice_factuel, 'resultats' => $results, 'synthese' => $synthese, 'evaluatedAt' => now(), 'evaluationDeGouvernanceId' => $evaluationDeGouvernance->id, 'formulaireDeGouvernanceId' => $evaluationDeGouvernance->formulaire_factuel_de_gouvernance()->id, 'organisationId' => $organisationId, 'programmeId' => $evaluationDeGouvernance->programmeId]);
                    } else {
                        app(FicheDeSyntheseRepository::class)->create(['type' => 'factuel', 'indice_de_gouvernance' => $indice_factuel, 'resultats' => $results, 'synthese' => $synthese, 'evaluatedAt' => now(), 'evaluationDeGouvernanceId' => $evaluationDeGouvernance->id, 'formulaireDeGouvernanceId' => $evaluationDeGouvernance->formulaire_factuel_de_gouvernance()->id, 'organisationId' => $organisationId, 'programmeId' => $evaluationDeGouvernance->programmeId]);
                    }

                    if ($profile || ($profile = $evaluationDeGouvernance->profiles($organisationId, $evaluationOrganisationId)->first())) {
                        
                        // Convert $profile->resultat_synthetique to an associative array for easy updating
                        $resultat_synthetique = collect($profile->resultat_synthetique)->keyBy('id');

                        // Iterate over each item in $results to update or add to $resultat_synthetique
                        foreach ($results as $result) {
                            $resultat_synthetique[$result['id']] = array_merge($resultat_synthetique->get($result['id'], []), $result);
                        }

                        // Convert back to a regular array if needed
                        $updated_resultat_synthetique = $resultat_synthetique->values()->toArray();

                        $profile->update(['resultat_synthetique' => $updated_resultat_synthetique]);

                    } else {
                        // Convert $results to an associative array for easy updating
                        $resultat_synthetique = collect($results)->keyBy('id');

                        // Iterate over each item in $results to update or add to $resultat_synthetique
                        foreach ($results as $result) {
                            $resultat_synthetique[$result['id']] = array_merge($resultat_synthetique->get($result['id'], []), $result);
                        }

                        // Convert back to a regular array if needed
                        $results = $resultat_synthetique->values()->toArray();

                        $profile = ProfileDeGouvernance::create(['resultat_synthetique' => $results, 'evaluationOrganisationId' => $evaluationOrganisationId, 'evaluationDeGouvernanceId' => $evaluationDeGouvernance->id, 'organisationId' => $organisationId, 'programmeId' => $evaluationDeGouvernance->programmeId]);
                    }
                }
                else if ($group_soumission === "perception") {

                    [$indice_de_perception, $results, $synthese] = $this->generateSyntheseForPerceptionSoumission($evaluationDeGouvernance->formulaire_de_perception_de_gouvernance(), $organisationId);

                    if ($fiche_de_synthese = $evaluationDeGouvernance->fiches_de_synthese($organisationId, 'perception')->first()) {
                        $fiche_de_synthese->update(['type' => 'perception', 'indice_de_gouvernance' => $indice_de_perception, 'synthese' => $synthese, 'evaluatedAt' => now(), 'evaluationDeGouvernanceId' => $evaluationDeGouvernance->id, 'formulaireDeGouvernanceId' => $evaluationDeGouvernance->formulaire_de_perception_de_gouvernance()->id, 'organisationId' => $organisationId, 'programmeId' => $evaluationDeGouvernance->programmeId]);
                    } else {
                        app(FicheDeSyntheseRepository::class)->create(['type' => 'perception', 'synthese' => $synthese, 'evaluatedAt' => now(), 'evaluationDeGouvernanceId' => $evaluationDeGouvernance->id, 'formulaireDeGouvernanceId' => $evaluationDeGouvernance->formulaire_de_perception_de_gouvernance()->id, 'organisationId' => $organisationId, 'programmeId' => $evaluationDeGouvernance->programmeId]);
                    }

                    if ($profile || ($profile = $evaluationDeGouvernance->profiles($organisationId, $evaluationOrganisationId)->first())) {
                        
                        // Convert $profile->resultat_synthetique to an associative array for easy updating
                        $resultat_synthetique = collect($profile->resultat_synthetique)->keyBy('id');

                        // Iterate over each item in $results to update or add to $resultat_synthetique
                        foreach ($results as $result) {
                            $resultat_synthetique[$result['id']] = array_merge($resultat_synthetique->get($result['id'], []), $result);
                        }

                        // Convert back to a regular array if needed
                        $updated_resultat_synthetique = $resultat_synthetique->values()->toArray();

                        $profile->update(['resultat_synthetique' => $updated_resultat_synthetique]);

                    } else {

                        // Convert $results to an associative array for easy updating
                        $resultat_synthetique = collect($results)->keyBy('id');

                        // Iterate over each item in $results to update or add to $resultat_synthetique
                        foreach ($results as $result) {
                            $resultat_synthetique[$result['id']] = array_merge($resultat_synthetique->get($result['id'], []), $result);
                        }

                        // Convert back to a regular array if needed
                        $results = $resultat_synthetique->values()->toArray();

                        $profile = ProfileDeGouvernance::create(['resultat_synthetique' => $results, 'evaluationOrganisationId' => $evaluationOrganisationId, 'evaluationDeGouvernanceId' => $evaluationDeGouvernance->id, 'organisationId' => $organisationId, 'programmeId' => $evaluationDeGouvernance->programmeId]);
                    }
                }
            }

            if ($profile = $evaluationDeGouvernance->profiles($organisationId, $evaluationOrganisationId)->first()) {
                        
                // Convert $profile->resultat_synthetique to an associative collection for easy updating
                $resultat_synthetique = collect($profile->resultat_synthetique)->keyBy('id');

                // Iterate over each item in $results to update or add to $resultat_synthetique
                foreach ($results as $result) {
                    // Check if the entry exists in $resultat_synthetique
                    if ($existing = $resultat_synthetique->get($result['id'])) {

                        // Calculate indice_synthetique by summing indice_factuel and indice_de_perception
                        $existing['indice_synthetique'] = $this->geomean([($existing['indice_factuel'] ?? 0), ($existing['indice_de_perception'] ?? 0)]);

                        $resultat_synthetique[$result['id']] = array_merge($resultat_synthetique->get($result['id'], []), $existing);
                    }
                }

                // Convert back to a regular array if needed
                $updated_resultat_synthetique = $resultat_synthetique->values()->toArray();

                // Update the profile with the modified array
                $profile->update(['resultat_synthetique' => $updated_resultat_synthetique]);

            }
        }
    }

    private function geomean(array $numbers) {
        // Remove any non-positive numbers since geometric mean is undefined for those
        $numbers = array_filter($numbers, fn($number) => $number > 0);
    
        $count = count($numbers);
    
        // If there are no valid numbers left, return null or handle as needed
        if ($count === 0) {
            return 0; // or 0, depending on how you want to handle it
        }
    
        // Calculate the product of all numbers
        $product = array_product($numbers);
            // Calculate the nth root (geometric mean)

        // Calculate the nth root (geometric mean)
        return pow($product, 1 / $count);
    }

    /**
     * 
     */
    public function generateSyntheseForPerceptionSoumission(FormulaireDeGouvernance $formulaireDeGouvernance, $organisationId)
    {
        $options_de_reponse = $formulaireDeGouvernance->options_de_reponse;
        $principes_de_gouvernance = collect([]);
        
        $results_categories_de_gouvernance = $formulaireDeGouvernance->categories_de_gouvernance()->with('questions_de_gouvernance.reponses')->get()->each(function ($categorie_de_gouvernance) use ($organisationId, $options_de_reponse, &$principes_de_gouvernance) {
            $categorie_de_gouvernance->questions_de_gouvernance->each(function ($question_de_gouvernance) use ($organisationId, $options_de_reponse) {

                // Get the total number of responses for NBRE_R
                $nbre_r = $question_de_gouvernance->reponses()->where('type', 'question_operationnelle')->whereHas("soumission", function ($query) use ($organisationId) {
                    $query->where('evaluationId', $this->evaluationDeGouvernance->id)->where('organisationId', $organisationId);
                })->count();

                // Initialize the weighted sum
                $weighted_sum = 0;
                $index = 0;
                $question_de_gouvernance->options_de_reponse = collect([]);

                $options_de_reponse->loadCount([
                    'reponses' => function ($query) use ($question_de_gouvernance, $organisationId) {
                        $query->where('questionId', $question_de_gouvernance->id)->where('type', 'question_operationnelle')->whereHas("soumission", function ($query) use ($organisationId) {
                            $query->where('evaluationId', $this->evaluationDeGouvernance->id)->where('organisationId', $organisationId);
                        });
                    }
                ])->each(function ($option_de_reponse) use (&$weighted_sum, $question_de_gouvernance, &$index) {

                    $note_i = $option_de_reponse->pivot->point ?? 0; // Default to 0 if there's no point
                    $nbre_i = $option_de_reponse->reponses_count ?? 0; // Default to 0 if there are no responses

                    // Accumulate the weighted sum
                    $weighted_sum += $option_de_reponse->moyenne_ponderee_i = $note_i * $nbre_i;

                    $question_de_gouvernance->options_de_reponse[$index] = $option_de_reponse;

                    $index++;
                });

                // Calculate the weighted average
                if ($nbre_r > 0) {
                    $question_de_gouvernance->moyenne_ponderee = round($weighted_sum / $nbre_r, 2);
                } else {
                    $question_de_gouvernance->moyenne_ponderee = 0; // Avoid division by zero
                }
            });

            // Now, calculate the 'indice_de_perception' for the category
            $total_moyenne_ponderee = $categorie_de_gouvernance->questions_de_gouvernance->sum('moyenne_ponderee');
            $nbre_questions_operationnelle = $categorie_de_gouvernance->questions_de_gouvernance->count();

            // Check to avoid division by zero
            $categorie_de_gouvernance->indice_de_perception = ($nbre_questions_operationnelle > 0) ? round(($total_moyenne_ponderee / $nbre_questions_operationnelle), 2) : 0;

            $principes_de_gouvernance->push(['id' => $categorie_de_gouvernance->categorieable->id, 'nom' => $categorie_de_gouvernance->categorieable->nom, 'indice_de_perception' => $categorie_de_gouvernance->indice_de_perception]);

        });
        $indice_de_perception = round(($results_categories_de_gouvernance->sum('indice_de_perception') / $results_categories_de_gouvernance->count()), 2);
        return [$indice_de_perception, $principes_de_gouvernance, FicheDeSyntheseEvaluationFactuelleResource::collection($results_categories_de_gouvernance)];
    }

    public function generateSyntheseForFactuelleSoumission(Soumission $soumission, $organisationId)
    {
        /*
            $results_categories_de_gouvernance = $soumission->formulaireDeGouvernance->categories_de_gouvernance()->with(['sousCategoriesDeGouvernance' => function ($query) {
                // Call the recursive function to load nested relationships
                $this->loadCategories($query);
            }])->get()->each(function ($categorie_de_gouvernance) {
                $categorie_de_gouvernance->sousCategoriesDeGouvernance->each(function ($sous_categorie_de_gouvernance) {
                    $reponses = $this->interpretData($sous_categorie_de_gouvernance);

                    // Calculate indice_factuel
                    if (count($reponses) > 0 && $reponses->sum('point') > 0) {

                        $sous_categorie_de_gouvernance->score_factuel = $reponses->sum('point') / count($reponses);
                    } else {
                        $sous_categorie_de_gouvernance->score_factuel = 0;
                    }
                });

                // Calculate indice_factuel
                if ($categorie_de_gouvernance->sousCategoriesDeGouvernance->count() > 0 && $categorie_de_gouvernance->sousCategoriesDeGouvernance->sum('score_factuel') > 0) {

                    $categorie_de_gouvernance->indice_factuel = $categorie_de_gouvernance->sousCategoriesDeGouvernance->sum('score_factuel') / $categorie_de_gouvernance->sousCategoriesDeGouvernance->count();
                } else {
                    $categorie_de_gouvernance->indice_factuel = 0;
                }

            });
        */

        $principes_de_gouvernance = collect([]);

        $results_categories_de_gouvernance = $soumission->formulaireDeGouvernance->categories_de_gouvernance()->with(['sousCategoriesDeGouvernance' => function ($query) use ($organisationId) {
            // Call the recursive function to load nested relationships
            $this->loadCategories($query, $organisationId);
        }])->get()->each(function ($categorie_de_gouvernance) use ($organisationId, &$principes_de_gouvernance) {
            $categorie_de_gouvernance->sousCategoriesDeGouvernance->each(function ($sous_categorie_de_gouvernance) use ($organisationId, &$principes_de_gouvernance) {
                $reponses = $this->interpretData($sous_categorie_de_gouvernance, $organisationId);

                // Calculate indice_factuel
                if (count($reponses) > 0 && $reponses->sum('point') > 0) {
                    $sous_categorie_de_gouvernance->score_factuel = $reponses->sum('point') / count($reponses);
                } else {
                    $sous_categorie_de_gouvernance->score_factuel = 0;
                }
                
                if($principes_de_gouvernance->count()){
                    // Check if the item exists in the collection
                    if($principes_de_gouvernance->firstWhere('id', $sous_categorie_de_gouvernance->categorieable_id)){
                        // Update the collection item by transforming it
                        $principes_de_gouvernance = $principes_de_gouvernance->transform(function ($item) use ($sous_categorie_de_gouvernance) {
                            
                            if ($item['id'] === $sous_categorie_de_gouvernance->categorieable_id) {
                                // Update the score_factuel
                                $item['indice_factuel'] += $sous_categorie_de_gouvernance->score_factuel;
                            }
                            return $item;
                        });
                    }
                    else{
                        // If the item doesn't exist push the new item                        
                        $principes_de_gouvernance->push(['id' => $sous_categorie_de_gouvernance->categorieable_id, 'nom' => $sous_categorie_de_gouvernance->categorieable->nom, 'indice_factuel' => $sous_categorie_de_gouvernance->score_factuel]);

                    }
                }
                else {
                    // If the collection is empty, push the new item
                    $principes_de_gouvernance->push(['id' => $sous_categorie_de_gouvernance->categorieable_id, 'nom' => $sous_categorie_de_gouvernance->categorieable->nom, 'indice_factuel' => $sous_categorie_de_gouvernance->score_factuel]);
                }
            });

            // Calculate indice_factuel
            if ($categorie_de_gouvernance->sousCategoriesDeGouvernance->count() > 0 && $categorie_de_gouvernance->sousCategoriesDeGouvernance->sum('score_factuel') > 0) {
                $categorie_de_gouvernance->indice_factuel = $categorie_de_gouvernance->sousCategoriesDeGouvernance->sum('score_factuel') / $categorie_de_gouvernance->sousCategoriesDeGouvernance->count();
            } else {
                $categorie_de_gouvernance->indice_factuel = 0;
            }
        });

        $indice_factuel = $results_categories_de_gouvernance->sum('indice_factuel') / $results_categories_de_gouvernance->count();

        return [$indice_factuel, $principes_de_gouvernance, FicheDeSyntheseEvaluationFactuelleResource::collection($results_categories_de_gouvernance)];

    }

    public function loadCategories($query, $organisationId)
    {
        $query->with(['sousCategoriesDeGouvernance' => function ($query) use ($organisationId) {
            // Recursively load sousCategoriesDeGouvernance
            $this->loadCategories($query, $organisationId);
        }, 'questions_de_gouvernance.reponses' => function ($query) use ($organisationId) {
            $query->where('type', 'indicateur')->whereHas("soumission", function ($query) use ($organisationId) {
                $query->where('evaluationId', $this->evaluationDeGouvernance->id)->where('organisationId', $organisationId);
            })->sum('point');
        }]);
    }

    public function interpretData($categorie_de_gouvernance, $organisationId)
    {
        $reponses = [];
        if ($categorie_de_gouvernance->sousCategoriesDeGouvernance->count()) {
            $categorie_de_gouvernance->sousCategoriesDeGouvernance->each(function ($sous_categorie_de_gouvernance) use ($organisationId) {
                $this->interpretData($sous_categorie_de_gouvernance, $organisationId);
            });
        } else {
            $categorie_de_gouvernance->questions_de_gouvernance->each(function ($question_de_gouvernance) use (&$reponses, $organisationId) {
                $reponses_de_collecte = $question_de_gouvernance->reponses()->where('type', 'indicateur')->whereHas("soumission", function ($query) use ($organisationId) {
                    $query->where('evaluationId', $this->evaluationDeGouvernance->id)->where('organisationId', $organisationId);
                })->get()->toArray();
                $reponses = array_merge($reponses, $reponses_de_collecte);
            });
        }

        return collect($reponses);
    }
}
