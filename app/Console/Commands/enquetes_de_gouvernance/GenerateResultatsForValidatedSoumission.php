<?php

namespace App\Console\Commands\enquetes_de_gouvernance;

use App\Http\Resources\gouvernance\FicheDeSyntheseEvaluationFactuelleResource;
use App\Models\enquetes_de_gouvernance\FormulaireDePerceptionDeGouvernance;
use App\Models\enquetes_de_gouvernance\FormulaireFactuelDeGouvernance;
use App\Models\enquetes_de_gouvernance\EvaluationDeGouvernance;
use App\Models\enquetes_de_gouvernance\ProfileDeGouvernance;
use App\Repositories\enquetes_de_gouvernance\FicheDeSyntheseRepository;
use Illuminate\Console\Command;
use Illuminate\Support\Collection;

class GenerateResultatsForValidatedSoumission extends Command
{
    protected $signature = 'gouvernance:generate-results {evaluationId? : ID de l\'évaluation (optionnel, traite toutes si absent)}';
    protected $description = 'Generates evaluation results for validated soumissions. Optionally for a specific evaluation.';
    protected $evaluationDeGouvernance;

    public function handle()
    {
        $evaluationId = $this->argument('evaluationId');

        // Si un ID spécifique est fourni, traiter uniquement cette évaluation
        if ($evaluationId) {
            $evaluation = EvaluationDeGouvernance::find($evaluationId);

            if (!$evaluation) {
                $this->error("❌ Évaluation introuvable avec l'ID: {$evaluationId}");
                return 1;
            }

            $this->info("🔄 Génération des résultats pour l'évaluation: {$evaluation->intitule} (Année: {$evaluation->annee_exercice})");
            $this->evaluationDeGouvernance = $evaluation;
            $this->generateResultForEnquete($evaluation);
            $this->info("✅ Résultats générés pour l'évaluation: {$evaluation->intitule}");

            return 0;
        }

        // Sinon, traiter toutes les évaluations en cours (statut = 0 ou 1)
        $this->info("🔄 Génération des résultats pour toutes les évaluations en cours...");

        $evaluations = EvaluationDeGouvernance::whereIn("statut", [0, 1])->get();

        if ($evaluations->isEmpty()) {
            $this->warn("⚠️  Aucune évaluation en cours trouvée");
            return 0;
        }

        $this->info("📊 Nombre d'évaluations à traiter: {$evaluations->count()}");

        $evaluations->each(function ($evaluationDeGouvernance) {
            $this->line("   → Traitement: {$evaluationDeGouvernance->intitule} (Année: {$evaluationDeGouvernance->annee_exercice})");
            $this->evaluationDeGouvernance = $evaluationDeGouvernance;
            $this->generateResultForEnquete($evaluationDeGouvernance);
        });

        $this->info("✅ Résultats générés pour {$evaluations->count()} évaluation(s)");
        return 0;
    }

    protected function generateResultForEnquete(EvaluationDeGouvernance $evaluationDeGouvernance)
    {
        $evaluationDeGouvernance->organisations->each(function ($organisation) use ($evaluationDeGouvernance) {
            $organisationId = $organisation->id;
            $evaluationOrganisationId = $this->getEvaluationOrganisationId($evaluationDeGouvernance, $organisationId);

            if (!$evaluationOrganisationId) {
                return;
            }

            // Initialize ALL principles from formulaires with default values (0)
            $allPrincipes = $this->initializeAllPrincipes($evaluationDeGouvernance);

            $profile = null;

            // Process factuel evaluation
            if ($evaluationDeGouvernance->formulaire_factuel_de_gouvernance()) {
                $profile = $this->processFactuelEvaluation(
                    $evaluationDeGouvernance,
                    $organisationId,
                    $evaluationOrganisationId,
                    $profile,
                    $allPrincipes
                );
            }

            // Process perception evaluation
            if ($evaluationDeGouvernance->formulaire_de_perception_de_gouvernance()) {
                $profile = $this->processPerceptionEvaluation(
                    $evaluationDeGouvernance,
                    $organisationId,
                    $evaluationOrganisationId,
                    $profile,
                    $allPrincipes
                );
            }

            // Ensure ALL principles are in the profile (merge with defaults)
            $this->ensureAllPrincipesInProfile(
                $evaluationDeGouvernance,
                $organisationId,
                $evaluationOrganisationId,
                $allPrincipes
            );

            // Calculate synthetic index
            $this->calculateSyntheticIndex($evaluationDeGouvernance, $organisationId, $evaluationOrganisationId);
        });
    }

    protected function processFactuelEvaluation(
        EvaluationDeGouvernance $evaluationDeGouvernance,
        $organisationId,
        $evaluationOrganisationId,
        $profile,
        Collection $allPrincipes
    ) {
        [$indice_factuel, $results, $synthese] = $this->generateResultForFactuelEvaluation(
            $evaluationDeGouvernance->formulaire_factuel_de_gouvernance(),
            $organisationId
        );

        $this->updateOrCreateFicheDeSynthese($evaluationDeGouvernance, $organisationId, 'factuel', [
            'type' => 'factuel',
            'indice_de_gouvernance' => $indice_factuel,
            'resultats' => $results,
            'synthese' => $synthese,
            'evaluatedAt' => now(),
            'evaluationDeGouvernanceId' => $evaluationDeGouvernance->id,
            'formulaireDeGouvernance_id' => $evaluationDeGouvernance->formulaire_factuel_de_gouvernance()->id,
            'formulaireDeGouvernance_type' => get_class($evaluationDeGouvernance->formulaire_factuel_de_gouvernance()),
            'organisationId' => $organisationId,
            'programmeId' => $evaluationDeGouvernance->programmeId
        ]);

        return $this->updateOrCreateProfile(
            $evaluationDeGouvernance,
            $organisationId,
            $evaluationOrganisationId,
            $results,
            $profile,
            $allPrincipes
        );
    }

    protected function processPerceptionEvaluation(
        EvaluationDeGouvernance $evaluationDeGouvernance,
        $organisationId,
        $evaluationOrganisationId,
        $profile,
        Collection $allPrincipes
    ) {
        [$indice_de_perception, $results, $synthese] = $this->generateResultForPerceptionEvaluation(
            $evaluationDeGouvernance->formulaire_de_perception_de_gouvernance(),
            $organisationId
        );

        $this->updateOrCreateFicheDeSynthese($evaluationDeGouvernance, $organisationId, 'perception', [
            'type' => 'perception',
            'indice_de_gouvernance' => $indice_de_perception,
            'synthese' => $synthese,
            'evaluatedAt' => now(),
            'evaluationDeGouvernanceId' => $evaluationDeGouvernance->id,
            'formulaireDeGouvernance_id' => $evaluationDeGouvernance->formulaire_de_perception_de_gouvernance()->id,
            'formulaireDeGouvernance_type' => get_class($evaluationDeGouvernance->formulaire_de_perception_de_gouvernance()),
            'organisationId' => $organisationId,
            'programmeId' => $evaluationDeGouvernance->programmeId
        ]);

        return $this->updateOrCreateProfile(
            $evaluationDeGouvernance,
            $organisationId,
            $evaluationOrganisationId,
            $results,
            $profile,
            $allPrincipes
        );
    }

    protected function calculateSyntheticIndex(
        EvaluationDeGouvernance $evaluationDeGouvernance,
        $organisationId,
        $evaluationOrganisationId
    ) {
        // Only calculate synthetic index if BOTH formulaires exist
        $hasFactuel = $evaluationDeGouvernance->formulaire_factuel_de_gouvernance() !== null;
        $hasPerception = $evaluationDeGouvernance->formulaire_de_perception_de_gouvernance() !== null;

        if (!$hasFactuel || !$hasPerception) {
            // Skip synthetic calculation if only one formulaire exists
            return;
        }

        $profile = $evaluationDeGouvernance->profiles($organisationId, $evaluationOrganisationId)->first();

        if (!$profile) {
            return;
        }

        $resultat_synthetique = collect($profile->resultat_synthetique)->keyBy('id');

        $resultat_synthetique = $resultat_synthetique->map(function ($item) {
            // Only calculate if both indices exist
            if (isset($item['indice_factuel']) && isset($item['indice_de_perception'])) {
                $item['indice_synthetique'] = $this->geometricMean([
                    $item['indice_factuel'],
                    $item['indice_de_perception']
                ]);
            }
            return $item;
        });

        $profile->update(['resultat_synthetique' => $resultat_synthetique->values()->toArray()]);
        $this->info("Generated result for soumissions - Profile ID: {$profile->id}");
    }

    protected function getEvaluationOrganisationId(EvaluationDeGouvernance $evaluationDeGouvernance, $organisationId)
    {
        $pivot = $evaluationDeGouvernance->organisations()
            ->wherePivot('organisationId', $organisationId)
            ->first()
            ?->pivot;

        return $pivot?->id;
    }

    protected function updateOrCreateFicheDeSynthese(
        EvaluationDeGouvernance $evaluationDeGouvernance,
        $organisationId,
        $type,
        array $data
    ) {
        $fiche = $evaluationDeGouvernance->fiches_de_synthese($organisationId, $type)->first();

        if ($fiche) {
            $fiche->update($data);
        } else {
            app(FicheDeSyntheseRepository::class)->create($data);
        }
    }

    protected function updateOrCreateProfile(
        EvaluationDeGouvernance $evaluationDeGouvernance,
        $organisationId,
        $evaluationOrganisationId,
        $results,
        $profile = null,
        Collection $allPrincipes = null
    ) {
        $profile = $profile ?: $evaluationDeGouvernance->profiles($organisationId, $evaluationOrganisationId)->first();

        $this->info(json_encode($profile));

        // If allPrincipes provided, start with all principles at 0
        $baseResults = $allPrincipes ? $allPrincipes->toArray() : [];

        if ($profile) {
            // Merge: base (all principles) <- existing profile <- new results
            $resultat_synthetique = $this->mergeResults($baseResults, $profile->resultat_synthetique);
            $resultat_synthetique = $this->mergeResults($resultat_synthetique, $results);
            $profile->update(['resultat_synthetique' => $resultat_synthetique]);
        } else {
            // Merge: base (all principles) <- new results
            $resultat_synthetique = $this->mergeResults($baseResults, $results);
            $profile = ProfileDeGouvernance::create([
                'resultat_synthetique' => $resultat_synthetique,
                'evaluationOrganisationId' => $evaluationOrganisationId,
                'evaluationDeGouvernanceId' => $evaluationDeGouvernance->id,
                'organisationId' => $organisationId,
                'programmeId' => $evaluationDeGouvernance->programmeId
            ]);
        }

        return $profile;
    }

    protected function mergeResults($existingResults, $newResults): array
    {
        // Convert to array if Collection
        $existingResults = $existingResults instanceof Collection ? $existingResults->toArray() : $existingResults;
        $newResults = $newResults instanceof Collection ? $newResults->toArray() : $newResults;

        $resultat_synthetique = collect($existingResults)->keyBy('id');

        foreach ($newResults as $result) {
            // Ensure $result is an array
            $result = is_array($result) ? $result : (array) $result;

            if (isset($result['id'])) {
                $resultat_synthetique[$result['id']] = array_merge(
                    $resultat_synthetique->get($result['id'], []),
                    $result
                );
            }
        }

        return $resultat_synthetique->values()->toArray();
    }

    public function generateResultForPerceptionEvaluation(
        FormulaireDePerceptionDeGouvernance $formulaireDeGouvernance,
        $organisationId
    ) {
        $options_de_reponse = $formulaireDeGouvernance->options_de_reponse;
        $principes_de_gouvernance = collect([]);

        $results_categories = $formulaireDeGouvernance->categories_de_gouvernance()
            ->with('questions_de_gouvernance.reponses')
            ->get()
            ->each(function ($categorie) use ($organisationId, $options_de_reponse, &$principes_de_gouvernance) {
                $this->processPerceptionCategory($categorie, $organisationId, $options_de_reponse);

                // ✅ Filtrer : Ne garder QUE les PrincipeDeGouvernance (exclure TypeDeGouvernance)
                $categorieableType = get_class($categorie->categorieable);
                $isPrincipe = str_contains($categorieableType, 'Principe');

                if ($isPrincipe) {
                    $principes_de_gouvernance->push([
                        'id' => $categorie->categorieable->id,
                        'nom' => $categorie->categorieable->nom,
                        'indice_de_perception' => $categorie->indice_de_perception
                    ]);
                }
            });

        $indice_de_perception = $this->calculateAverageIndex($results_categories, 'indice_de_perception');

        return [
            $indice_de_perception,
            $principes_de_gouvernance,
            FicheDeSyntheseEvaluationFactuelleResource::collection($results_categories)
        ];
    }

    protected function processPerceptionCategory($categorie, $organisationId, $options_de_reponse)
    {
        // Load all questions with their responses (filtered by organisation)
        // Questions without responses will have an empty reponses collection
        $categorie->questions_de_gouvernance
            ->load(['reponses' => function ($query) use ($organisationId) {
                $query->whereHas("soumission", function ($q) use ($organisationId) {
                    $q->where('evaluationId', $this->evaluationDeGouvernance->id)
                        ->where('organisationId', $organisationId);
                });
            }])
            ->each(function ($question) use ($options_de_reponse) {
                // Calculate moyenne_ponderee for each question
                // If no response, moyenne_ponderee will be 0
                $this->calculateQuestionMoyennePonderee($question, $options_de_reponse);
            });

        // Calculate indice using ALL questions (including those with 0 moyenne_ponderee)
        $total_moyenne = $categorie->questions_de_gouvernance->sum('moyenne_ponderee');
        $count = $categorie->questions_de_gouvernance->count();
        $categorie->indice_de_perception = $count > 0 ? round($total_moyenne / $count, 2) : 0;
    }

    protected function calculateQuestionMoyennePonderee($question, $options_de_reponse)
    {
        /* ========== ANCIEN CODE (COMMENTÉ - PERMETTAIT DES DOUBLONS) ==========
        $nbre_r = $question->reponses->count();
        $weighted_sum = 0;
        $question->options_de_reponse = collect([]);

        // If no responses, moyenne_ponderee will be 0 (default value)
        if ($nbre_r === 0) {
            $question->moyenne_ponderee = 0;
            return;
        }

        $counts = $question->reponses()
            ->selectRaw('optionDeReponseId, COUNT(*) as count')
            ->groupBy('optionDeReponseId')
            ->pluck('count', 'optionDeReponseId');

        foreach ($options_de_reponse as $key => $option_de_reponse) {
            $reponses_count = $counts[$option_de_reponse->id] ?? 0;
            $optionPoint = $option_de_reponse->pivot->point;
            $moyenne_ponderee_i = $optionPoint * $reponses_count;
            $weighted_sum += $moyenne_ponderee_i;

            $option = clone $option_de_reponse;
            $option->reponses_count = $reponses_count;
            $option->moyenne_ponderee_i = $moyenne_ponderee_i;
            $question->options_de_reponse[$key] = $option;
        }

        $question->moyenne_ponderee = $nbre_r > 0 ? round($weighted_sum / $nbre_r, 2) : 0;
        ========== FIN ANCIEN CODE ========== */

        // ========== NOUVEAU CODE (CORRIGÉ - UNE SEULE RÉPONSE PAR SOUMISSION) ==========
        $weighted_sum = 0;
        $question->options_de_reponse = collect([]);

        // Grouper les réponses par soumissionId et ne garder que la plus récente pour chaque soumission
        $reponses_uniques = $question->reponses
            ->groupBy('soumissionId')
            ->map(function ($reponses_par_soumission) {
                // Pour chaque soumission, prendre la réponse la plus récente
                return $reponses_par_soumission->sortByDesc('created_at')->first();
            })
            ->values();

        // Nombre de soumissions uniques (pas le nombre total de réponses)
        $nbre_soumissions = $reponses_uniques->count();

        // If no responses, moyenne_ponderee will be 0 (default value)
        if ($nbre_soumissions === 0) {
            $question->moyenne_ponderee = 0;
            return;
        }

        // Compter les réponses uniques par optionDeReponseId
        $counts = $reponses_uniques->countBy('optionDeReponseId');

        foreach ($options_de_reponse as $key => $option_de_reponse) {
            $reponses_count = $counts[$option_de_reponse->id] ?? 0;
            $optionPoint = $option_de_reponse->pivot->point;
            $moyenne_ponderee_i = $optionPoint * $reponses_count;
            $weighted_sum += $moyenne_ponderee_i;

            $option = clone $option_de_reponse;
            $option->reponses_count = $reponses_count;
            $option->moyenne_ponderee_i = $moyenne_ponderee_i;
            $question->options_de_reponse[$key] = $option;
        }

        // Calculer la moyenne à partir du nombre de soumissions (pas du nombre de réponses)
        $question->moyenne_ponderee = $nbre_soumissions > 0
            ? round(min(100, $weighted_sum / $nbre_soumissions), 2) // Limiter à 100 par sécurité
            : 0;
    }

    public function generateResultForFactuelEvaluation(
        FormulaireFactuelDeGouvernance $formulaireDeGouvernance,
        $organisationId
    ) {
        $principes_de_gouvernance = collect([]);

        $results_categories = $formulaireDeGouvernance->categories_de_gouvernance()
            ->with(['sousCategoriesDeGouvernance' => function ($query) use ($organisationId) {
                $this->loadCategories($query, $organisationId);
            }])
            ->get()
            ->each(function ($categorie) use ($organisationId, &$principes_de_gouvernance) {
                $this->processFactuelCategory($categorie, $organisationId, $principes_de_gouvernance);
            });

        // Calculer la moyenne des scores pour chaque principe (somme / nombre de sous-catégories)
        $principes_de_gouvernance = $this->finalizePrincipesDeGouvernance($principes_de_gouvernance);

        $indice_factuel = $this->calculateAverageIndex($results_categories, 'indice_factuel');

        return [
            $indice_factuel,
            $principes_de_gouvernance,
            FicheDeSyntheseEvaluationFactuelleResource::collection($results_categories)
        ];
    }

    protected function processFactuelCategory($categorie, $organisationId, &$principes_de_gouvernance)
    {
        $categorie->sousCategoriesDeGouvernance->each(function ($sous_categorie) use ($organisationId, &$principes_de_gouvernance) {
            // Get responses for this organisation (may be empty)
            $reponses = $this->interpretData($sous_categorie, $organisationId);

            // Get ALL indicators for this category (not just those with responses)
            $indicateurs = $this->getIndicateurs($sous_categorie, $organisationId);

            // Calculate score: sum(points) / total_indicators
            // Indicators without response contribute 0 to the numerator but are counted in denominator
            $sous_categorie->score_factuel = $this->calculateScoreFactuel($reponses, $indicateurs);
            $this->updatePrincipesDeGouvernance($principes_de_gouvernance, $sous_categorie);
        });

        // Calculate indice using ALL subcategories (including those with score 0)
        $categorie->indice_factuel = $this->calculateCategoryIndex($categorie->sousCategoriesDeGouvernance);
    }

    protected function calculateScoreFactuel($reponses, $indicateurs): float
    {
        $count = count($indicateurs);
        $sum = $reponses->sum('point');

        return $count > 0 ? round($sum / $count, 2) : 0;
    }

    protected function calculateCategoryIndex($sousCategories): float
    {
        $count = $sousCategories->count();
        $sum = $sousCategories->sum('score_factuel');

        return $count > 0 ? round($sum / $count, 2) : 0;
    }

    protected function calculateAverageIndex($collection, $field): float
    {
        $count = $collection->count();
        return $count > 0 ? round($collection->sum($field) / $count, 2) : 0;
    }

    protected function updatePrincipesDeGouvernance(&$principes, $sous_categorie)
    {
        // ✅ Filtrer : Ne traiter QUE les PrincipeDeGouvernance (exclure TypeDeGouvernance)
        $categorieableType = get_class($sous_categorie->categorieable);
        $isPrincipe = str_contains($categorieableType, 'Principe');

        if (!$isPrincipe) {
            return; // Ignorer les types de gouvernance
        }

        $existing = $principes->firstWhere('id', $sous_categorie->categorieable_id);

        if ($existing) {
            $principes = $principes->transform(function ($item) use ($sous_categorie) {
                if ($item['id'] === $sous_categorie->categorieable_id) {
                    // Additionner les scores et compter les sous-catégories
                    $item['indice_factuel'] += $sous_categorie->score_factuel;
                    $item['_sous_categories_count'] = ($item['_sous_categories_count'] ?? 1) + 1;
                }
                return $item;
            });
        } else {
            $principes->push([
                'id' => $sous_categorie->categorieable_id,
                'nom' => $sous_categorie->categorieable->nom,
                'indice_factuel' => $sous_categorie->score_factuel,
                '_sous_categories_count' => 1  // Première sous-catégorie
            ]);
        }
    }

    protected function finalizePrincipesDeGouvernance(Collection $principes): Collection
    {
        // Calculer la moyenne pour chaque principe
        return $principes->map(function ($principe) {
            $count = $principe['_sous_categories_count'] ?? 1;

            // Moyenne = somme / nombre de sous-catégories
            $principe['indice_factuel'] = $count > 0
                ? round($principe['indice_factuel'] / $count, 2)
                : 0;

            // Retirer le compteur temporaire
            unset($principe['_sous_categories_count']);

            return $principe;
        });
    }

    public function loadCategories($query, $organisationId)
    {
        $query->with([
            'sousCategoriesDeGouvernance' => function ($query) use ($organisationId) {
                $this->loadCategories($query, $organisationId);
            },
            'questions_de_gouvernance.reponses' => function ($query) use ($organisationId) {
                $query->whereHas("soumission", function ($q) use ($organisationId) {
                    $q->where('evaluationId', $this->evaluationDeGouvernance->id)
                        ->where('organisationId', $organisationId);
                });
            }
        ]);
    }

    public function interpretData($categorie, $organisationId): Collection
    {
        /* ========== ANCIEN CODE (COMMENTÉ - PERMETTAIT DES DOUBLONS) ==========
        $reponses = [];

        if ($categorie->sousCategoriesDeGouvernance->count()) {
            $categorie->sousCategoriesDeGouvernance->each(function ($sous_categorie) use (&$reponses, $organisationId) {
                $reponses = array_merge($reponses, $this->interpretData($sous_categorie, $organisationId)->toArray());
            });
        } else {
            $categorie->questions_de_gouvernance->each(function ($question) use (&$reponses, $organisationId) {
                $reponses_data = $question->reponses()
                    ->whereHas("soumission", function ($query) use ($organisationId) {
                        $query->where('evaluationId', $this->evaluationDeGouvernance->id)
                            ->where('organisationId', $organisationId);
                    })
                    ->get()
                    ->toArray();
                $reponses = array_merge($reponses, $reponses_data);
            });
        }

        return collect($reponses);
        ========== FIN ANCIEN CODE ========== */

        // ========== NOUVEAU CODE (CORRIGÉ - UNE SEULE RÉPONSE PAR QUESTION PAR SOUMISSION) ==========
        $reponses = [];

        if ($categorie->sousCategoriesDeGouvernance->count()) {
            $categorie->sousCategoriesDeGouvernance->each(function ($sous_categorie) use (&$reponses, $organisationId) {
                $reponses = array_merge($reponses, $this->interpretData($sous_categorie, $organisationId)->toArray());
            });
        } else {
            $categorie->questions_de_gouvernance->each(function ($question) use (&$reponses, $organisationId) {
                $reponses_data = $question->reponses()
                    ->whereHas("soumission", function ($query) use ($organisationId) {
                        $query->where('evaluationId', $this->evaluationDeGouvernance->id)
                            ->where('organisationId', $organisationId);
                    })
                    ->get();

                // Grouper par soumissionId et ne prendre que la réponse la plus récente par soumission
                $reponses_uniques = $reponses_data
                    ->groupBy('soumissionId')
                    ->map(function ($reponses_par_soumission) {
                        return $reponses_par_soumission->sortByDesc('created_at')->first();
                    })
                    ->values()
                    ->toArray();

                $reponses = array_merge($reponses, $reponses_uniques);
            });
        }

        return collect($reponses);
    }

    public function getIndicateurs($categorie, $organisationId): Collection
    {
        $indicateurs = [];

        if ($categorie->sousCategoriesDeGouvernance->count()) {
            $categorie->sousCategoriesDeGouvernance->each(function ($sous_categorie) use (&$indicateurs, $organisationId) {
                $indicateurs = array_merge($indicateurs, $this->getIndicateurs($sous_categorie, $organisationId)->toArray());
            });
        } else {
            $indicateurs = array_merge($indicateurs, $categorie->questions_de_gouvernance->toArray());
        }

        return collect($indicateurs);
    }

    protected function initializeAllPrincipes(EvaluationDeGouvernance $evaluationDeGouvernance): Collection
    {
        $principes = collect([]);
        $hasFactuel = $evaluationDeGouvernance->formulaire_factuel_de_gouvernance() !== null;
        $hasPerception = $evaluationDeGouvernance->formulaire_de_perception_de_gouvernance() !== null;

        // Get all principles from factuel formulaire
        if ($formulaire = $evaluationDeGouvernance->formulaire_factuel_de_gouvernance()) {
            $formulaire->categories_de_gouvernance()->get()->each(function ($categorie) use (&$principes, $hasFactuel, $hasPerception) {
                if ($categorie->categorieable) {
                    // ✅ Filtrer : Ne garder QUE les PrincipeDeGouvernance (exclure TypeDeGouvernance)
                    $categorieableType = get_class($categorie->categorieable);
                    $isPrincipe = str_contains($categorieableType, 'Principe');

                    if ($isPrincipe) {
                        $principe = [
                            'id' => $categorie->categorieable->id,
                            'nom' => $categorie->categorieable->nom,
                        ];

                        // Only initialize indices for formulaires that exist
                        if ($hasFactuel) {
                            $principe['indice_factuel'] = 0;
                        }
                        if ($hasPerception) {
                            $principe['indice_de_perception'] = 0;
                        }
                        // Only calculate synthetic if both exist
                        if ($hasFactuel && $hasPerception) {
                            $principe['indice_synthetique'] = 0;
                        }

                        $principes[$categorie->categorieable->id] = $principe;
                    }
                }
            });
        }

        // Get all principles from perception formulaire (merge with factuel)
        if ($formulaire = $evaluationDeGouvernance->formulaire_de_perception_de_gouvernance()) {
            $formulaire->categories_de_gouvernance()->get()->each(function ($categorie) use (&$principes, $hasFactuel, $hasPerception) {
                if ($categorie->categorieable) {
                    // ✅ Filtrer : Ne garder QUE les PrincipeDeGouvernance (exclure TypeDeGouvernance)
                    $categorieableType = get_class($categorie->categorieable);
                    $isPrincipe = str_contains($categorieableType, 'Principe');

                    if ($isPrincipe && !isset($principes[$categorie->categorieable->id])) {
                        $principe = [
                            'id' => $categorie->categorieable->id,
                            'nom' => $categorie->categorieable->nom,
                        ];

                        // Only initialize indices for formulaires that exist
                        if ($hasFactuel) {
                            $principe['indice_factuel'] = 0;
                        }
                        if ($hasPerception) {
                            $principe['indice_de_perception'] = 0;
                        }
                        // Only calculate synthetic if both exist
                        if ($hasFactuel && $hasPerception) {
                            $principe['indice_synthetique'] = 0;
                        }

                        $principes[$categorie->categorieable->id] = $principe;
                    }
                }
            });
        }

        return $principes;
    }

    protected function ensureAllPrincipesInProfile(
        EvaluationDeGouvernance $evaluationDeGouvernance,
        $organisationId,
        $evaluationOrganisationId,
        Collection $allPrincipes
    ) {
        $profile = $evaluationDeGouvernance->profiles($organisationId, $evaluationOrganisationId)->first();

        $this->info("Generated result for soumissions - Profile ID: {$profile->resultat_synthetique}");

        if (!$profile) {
            return;
        }

        // Start with all principles initialized to 0
        $resultat_synthetique = $allPrincipes;

        // Merge with existing profile data (overwrite defaults with actual values)
        $existingResults = collect($profile->resultat_synthetique)->keyBy('id');

        foreach ($existingResults as $id => $result) {
            if (isset($resultat_synthetique[$id])) {
                // Merge actual values with defaults
                $resultat_synthetique[$id] = array_merge($resultat_synthetique[$id], $result);
            }
        }

        // Update profile with complete data (all principles included)
        $profile->update(['resultat_synthetique' => $resultat_synthetique->values()->toArray()]);
    }

    protected function geometricMean(array $numbers): float
    {
        $filteredNumbers = array_filter($numbers, fn($number) => $number > 0);

        if (empty($filteredNumbers)) {
            return 0;
        }

        $product = array_product($filteredNumbers);
        $n = count($filteredNumbers);

        return round(pow($product, 1 / $n), 2);
    }
}
