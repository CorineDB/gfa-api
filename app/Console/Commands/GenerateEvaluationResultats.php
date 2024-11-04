<?php

namespace App\Console\Commands;

use App\Http\Resources\gouvernance\FicheSyntheseEvaluationDePerceptionResource;
use App\Http\Resources\gouvernance\FicheSyntheseEvaluationFactuelleResource;
use App\Models\EvaluationDeGouvernance;
use App\Models\Soumission;
use App\Repositories\EvaluationDeGouvernanceRepository;
use App\Repositories\FicheDeSyntheseRepository;
use Illuminate\Console\Command;

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

        // Retrieve all soumissions (assuming that's how they're represented in your model)
        $soumissions = $this->evaluationDeGouvernance->soumissions;

        $this->alert("Generated result for soumission ID SO");
        // Process each soumission to generate results
        foreach ($soumissions as $soumission) {
            $result = $this->generateResultForSoumission($soumission);
            $fiche = app(FicheDeSyntheseRepository::class)->create(['type' => $soumission->type, 'synthese' => $result, 'evaluatedAt' => now(), 'soumissionId' => $soumission->id, 'programmeId' => $soumission->programmeId]);
            $this->info("Generated result for soumission ID {$soumission->id}: {$fiche}");
        }

        return 0; // Indicates successful execution
    }

    /**
     * Generate a result for a given soumission.
     *
     * @param EvaluationDeGouvernance $soumission
     * @return string
     */
    protected function generateResultForSoumission(Soumission $soumission)
    {
        switch ($soumission->type) {
            case 'factuel':
                return $this->generateSyntheseForFactuelleSoumission($soumission);
                break;
            case 'perception':
                return $this->generateSyntheseForPerceptionSoumission($soumission);
                break;

            default:
                return [];
                break;
        }

        // Placeholder for your logic to generate the result
        // This could involve calculations, data manipulations, etc.
        // Return a string or a result based on the processing
        return "Result for soumission with ID {$soumission->id}";
    }

    public function generateSyntheseForPerceptionSoumission(Soumission $soumission)
    {

        return $results_categories_de_gouvernance = $soumission->formulaireDeGouvernance->categories_de_gouvernance()->with('questions_de_gouvernance.reponses')->get()->each(function ($categorie_de_gouvernance) {
            $categorie_de_gouvernance->questions_de_gouvernance->each(function ($question_de_gouvernance) {
                $question_de_gouvernance->moyenne_ponderee = $question_de_gouvernance->reponses->sum('point');
            });
            $categorie_de_gouvernance->indice_de_perception = $categorie_de_gouvernance->questions_de_gouvernance->sum('moyenne_ponderee') / $categorie_de_gouvernance->questions_de_gouvernance->count();
        });

        dd($results_categories_de_gouvernance);

        // Placeholder for your logic to generate the result
        // This could involve calculations, data manipulations, etc.
        // Return a string or a result based on the processing
        return FicheSyntheseEvaluationDePerceptionResource::collection([]);
    }

    public function generateSyntheseForFactuelleSoumission(Soumission $soumission)
    {
        return $results_categories_de_gouvernance = $soumission->formulaireDeGouvernance->categories_de_gouvernance()->with(['sousCategoriesDeGouvernance' => function ($query) {
            // Call the recursive function to load nested relationships
            $this->loadCategories($query);
        }])->get()->each(function ($categorie_de_gouvernance) {
            $categorie_de_gouvernance->sousCategoriesDeGouvernance->each(function ($sous_categorie_de_gouvernance) {
                $reponses = $this->interprateData($sous_categorie_de_gouvernance);

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

        /*$results_categories_de_gouvernance = $soumission->formulaireDeGouvernance->categories_de_gouvernance->each(function($categorie_de_gouvernance){

        });*/
        // Placeholder for your logic to generate the result
        // This could involve calculations, data manipulations, etc.
        // Return a string or a result based on the processing
        return FicheSyntheseEvaluationFactuelleResource::collection([]);
    }

    public function loadCategories($query)
    {
        $query->with(['sousCategoriesDeGouvernance' => function ($query) {
            // Recursively load sousCategoriesDeGouvernance
            $this->loadCategories($query);
        }, 'questions_de_gouvernance.reponses' => function ($query) {
            $query->sum('point');
        },]);
    }

    public function interprateData($categorie_de_gouvernance)
    {
        $reponses = [];
        if ($categorie_de_gouvernance->sousCategoriesDeGouvernance->count()) {
            $categorie_de_gouvernance->sousCategoriesDeGouvernance->each(function ($sous_categorie_de_gouvernance) {
                $this->interprateData($sous_categorie_de_gouvernance);
            });
        } else {
            $categorie_de_gouvernance->questions_de_gouvernance->each(function ($question_de_gouvernance) use (&$reponses) {
                $reponses = array_merge($reponses, $question_de_gouvernance->reponses->toArray());
            });
        }

        return collect($reponses);
    }
}
