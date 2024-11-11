<?php

namespace App\Console\Commands;

use App\Http\Resources\gouvernance\FicheSyntheseEvaluationDePerceptionResource;
use App\Http\Resources\gouvernance\FicheDeSyntheseEvaluationFactuelleResource;
use App\Models\EvaluationDeGouvernance;
use App\Models\FormulaireDeGouvernance;
use App\Models\Soumission;
use App\Repositories\EvaluationDeGouvernanceRepository;
use App\Repositories\FicheDeSyntheseRepository;
use Illuminate\Console\Command;
use Illuminate\Database\Eloquent\Collection;

class GenerateEvaluationResultats extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'command:generate-evaluation-resultats {evaluationId}';

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

            foreach ($groups_soumissions as $group_soumission => $soumissions) {
                if ($group_soumission === "factuel") {

                    $results = $this->generateSyntheseForFactuelleSoumission($soumissions->first(), $organisationId);

                    if ($fiche_de_synthese = $evaluationDeGouvernance->fiches_de_synthese($organisationId, $group_soumission)->first()) {
                        $fiche_de_synthese->update(['type' => 'factuel', 'synthese' => $results, 'evaluatedAt' => now(), 'evaluationDeGouvernanceId' => $evaluationDeGouvernance->id, 'formulaireDeGouvernanceId' => $evaluationDeGouvernance->formulaire_factuel_de_gouvernance()->id, 'organisationId' => $organisationId, 'programmeId' => $evaluationDeGouvernance->programmeId]);
                    } else {
                        app(FicheDeSyntheseRepository::class)->create(['type' => 'factuel', 'synthese' => $results, 'evaluatedAt' => now(), 'evaluationDeGouvernanceId' => $evaluationDeGouvernance->id, 'formulaireDeGouvernanceId' => $evaluationDeGouvernance->formulaire_factuel_de_gouvernance()->id, 'organisationId' => $organisationId, 'programmeId' => $evaluationDeGouvernance->programmeId]);
                    }
                } else if ($group_soumission === "perception") {

                    [$indice_de_perception, $results] = $this->generateSyntheseForPerceptionSoumission($evaluationDeGouvernance->formulaire_de_perception_de_gouvernance(), $organisationId);

                    if ($fiche_de_synthese = $evaluationDeGouvernance->fiches_de_synthese($organisationId, 'perception')->first()) {
                        $fiche_de_synthese->update(['type' => 'perception', 'indice_de_gouvernance' => $indice_de_perception, 'synthese' => $results, 'evaluatedAt' => now(), 'evaluationDeGouvernanceId' => $evaluationDeGouvernance->id, 'formulaireDeGouvernanceId' => $evaluationDeGouvernance->formulaire_de_perception_de_gouvernance()->id, 'organisationId' => $organisationId, 'programmeId' => $evaluationDeGouvernance->programmeId]);
                    } else {
                        app(FicheDeSyntheseRepository::class)->create(['type' => 'perception', 'synthese' => $results, 'evaluatedAt' => now(), 'evaluationDeGouvernanceId' => $evaluationDeGouvernance->id, 'formulaireDeGouvernanceId' => $evaluationDeGouvernance->formulaire_de_perception_de_gouvernance()->id, 'organisationId' => $organisationId, 'programmeId' => $evaluationDeGouvernance->programmeId]);
                    }
                }
            }
        }
    }

    /**
     * 
     */
    public function generateSyntheseForPerceptionSoumission(FormulaireDeGouvernance $formulaireDeGouvernance, $organisationId)
    {
        $options_de_reponse = $formulaireDeGouvernance->options_de_reponse;
        
        $results_categories_de_gouvernance = $formulaireDeGouvernance->categories_de_gouvernance()->with('questions_de_gouvernance.reponses')->get()->each(function ($categorie_de_gouvernance) use ($organisationId, $options_de_reponse, &$indice_de_perception) {
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
                    $question_de_gouvernance->moyenne_ponderee = $weighted_sum / $nbre_r;
                } else {
                    $question_de_gouvernance->moyenne_ponderee = 0; // Avoid division by zero
                }
            });

            // Now, calculate the 'indice_de_perception' for the category
            $total_moyenne_ponderee = $categorie_de_gouvernance->questions_de_gouvernance->sum('moyenne_ponderee');
            $nbre_questions_operationnelle = $categorie_de_gouvernance->questions_de_gouvernance->count();

            // Check to avoid division by zero
            $categorie_de_gouvernance->indice_de_perception = ($nbre_questions_operationnelle > 0) ? ($total_moyenne_ponderee / $nbre_questions_operationnelle) : 0;
        });
        $indice_de_perception = $results_categories_de_gouvernance->sum('indice_de_perception') / $results_categories_de_gouvernance->count();
        return [$indice_de_perception, FicheDeSyntheseEvaluationFactuelleResource::collection($results_categories_de_gouvernance)];

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

        $results_categories_de_gouvernance = $soumission->formulaireDeGouvernance->categories_de_gouvernance()->with(['sousCategoriesDeGouvernance' => function ($query) use ($organisationId) {
            // Call the recursive function to load nested relationships
            $this->loadCategories($query, $organisationId);
        }])->get()->each(function ($categorie_de_gouvernance) use ($organisationId) {
            $categorie_de_gouvernance->sousCategoriesDeGouvernance->each(function ($sous_categorie_de_gouvernance) use ($organisationId) {
                $reponses = $this->interpretData($sous_categorie_de_gouvernance, $organisationId);

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

        return FicheDeSyntheseEvaluationFactuelleResource::collection($results_categories_de_gouvernance);

        $results_categories_de_gouvernance = $evaluationDeGouvernance->soumissionFactuel->formulaireDeGouvernance->categories_de_gouvernance()->with(['sousCategoriesDeGouvernance' => function ($query) {
            // Call the recursive function to load nested relationships
            $this->loadCategories($query);
        }])->get()->each(function ($categorie_de_gouvernance) use ($organisationId) {
            $categorie_de_gouvernance->sousCategoriesDeGouvernance->each(function ($sous_categorie_de_gouvernance) use ($organisationId) {
                $reponses = $this->interpretData($sous_categorie_de_gouvernance, $organisationId);

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

        return FicheDeSyntheseEvaluationFactuelleResource::collection($results_categories_de_gouvernance);
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
