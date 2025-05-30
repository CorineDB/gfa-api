<?php

namespace App\Console\Commands;

use App\Http\Resources\gouvernance\FicheDeSyntheseEvaluationFactuelleResource;
use App\Models\enquetes_de_gouvernance\FormulaireDePerceptionDeGouvernance;
use App\Models\enquetes_de_gouvernance\FormulaireFactuelDeGouvernance;
use App\Models\EvaluationDeGouvernance;
use App\Models\enquetes_de_gouvernance\EvaluationDeGouvernance as EvaluationGouvernance;
use App\Models\FormulaireDeGouvernance;
use App\Models\ProfileDeGouvernance;
use App\Models\Soumission;
use App\Repositories\FicheDeSyntheseRepository;
use App\Repositories\enquetes_de_gouvernance\FicheDeSyntheseRepository as FichesDeSyntheseRepository;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class GenerateResultatsForValidatedSoumission extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'generate:report-for-validated-soumissions';

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
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        EvaluationGouvernance::where("statut", 0)
            /* ->where(function ($query) {
                $query->whereHas("soumissionsFactuel")
                    ->orWhereHas("soumissionsDePerception");
            }) *//* ->where("debut",">=", now())->where("fin","<=", now()) */->get()->map(function ($evaluationDeGouvernance) {
                $this->evaluationDeGouvernance = $evaluationDeGouvernance;
                $this->generateResultForEnquete($evaluationDeGouvernance);
                //$this->generateResultForEvaluation($evaluationDeGouvernance);
            });

        $this->info("Generated result for soumissions");
        return 0; // Indicates successful execution
    }

    protected function generateResultForEnquete(EvaluationGouvernance $evaluationDeGouvernance)
    {

        $evaluationDeGouvernance->organisations->map(function ($organisation) use ($evaluationDeGouvernance) {

            $results = [];

            $groups_soumissions['factuel'] = $organisation->sousmissions_enquete_factuel()->where("evaluationId", $evaluationDeGouvernance->id)->get();

            $groups_soumissions['perception'] = $organisation->sousmissions_enquete_de_perception()->where("evaluationId", $evaluationDeGouvernance->id)->get();

            $profile = null;
            $organisationId = $organisation->id;

            if (!$evaluationOrganisationId = $evaluationDeGouvernance->organisations()->wherePivot('organisationId', $organisationId)->first()->pivot) {
                return;
            }

            $evaluationOrganisationId = $evaluationOrganisationId->id;

            if ($evaluationDeGouvernance->formulaire_factuel_de_gouvernance()) {

                [$indice_factuel, $results, $synthese] = $this->generateResultForFactuelEvaluation($evaluationDeGouvernance->formulaire_factuel_de_gouvernance(), $organisationId);

                if ($fiche_de_synthese = $evaluationDeGouvernance->fiches_de_synthese($organisationId, 'factuel')->first()) {
                    $fiche_de_synthese->update(['type' => 'factuel', 'indice_de_gouvernance' => $indice_factuel, 'resultats' => $results, 'synthese' => $synthese, 'evaluatedAt' => now(), 'evaluationDeGouvernanceId' => $evaluationDeGouvernance->id, 'formulaireDeGouvernance_id' => $evaluationDeGouvernance->formulaire_factuel_de_gouvernance()->id, 'formulaireDeGouvernance_type' => get_class($evaluationDeGouvernance->formulaire_factuel_de_gouvernance()), 'organisationId' => $organisationId, 'programmeId' => $evaluationDeGouvernance->programmeId]);
                } else {
                    //dd(['type' => 'factuel', 'indice_de_gouvernance' => $indice_factuel, 'resultats' => $results, 'synthese' => $synthese, 'evaluatedAt' => now(), 'evaluationDeGouvernanceId' => $evaluationDeGouvernance->id, 'formulaireDeGouvernance_id' => $evaluationDeGouvernance->formulaire_factuel_de_gouvernance()->id, 'formulaireDeGouvernance_type' => get_class($evaluationDeGouvernance->formulaire_factuel_de_gouvernance()), 'organisationId' => $organisationId, 'programmeId' => $evaluationDeGouvernance->programmeId]);
                    app(FichesDeSyntheseRepository::class)->create(['type' => 'factuel', 'indice_de_gouvernance' => $indice_factuel, 'resultats' => $results, 'synthese' => $synthese, 'evaluatedAt' => now(), 'evaluationDeGouvernanceId' => $evaluationDeGouvernance->id, 'formulaireDeGouvernance_id' => $evaluationDeGouvernance->formulaire_factuel_de_gouvernance()->id, 'formulaireDeGouvernance_type' => get_class($evaluationDeGouvernance->formulaire_factuel_de_gouvernance()), 'organisationId' => $organisationId, 'programmeId' => $evaluationDeGouvernance->programmeId]);
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

            if ($evaluationDeGouvernance->formulaire_de_perception_de_gouvernance()) {
                [$indice_de_perception, $results, $synthese] = $this->generateResultForPerceptionEvaluation($evaluationDeGouvernance->formulaire_de_perception_de_gouvernance(), $organisationId);

                if ($fiche_de_synthese = $evaluationDeGouvernance->fiches_de_synthese($organisationId, 'perception')->first()) {
                    $fiche_de_synthese->update(['type' => 'perception', 'indice_de_gouvernance' => $indice_de_perception, 'synthese' => $synthese, 'evaluatedAt' => now(), 'evaluationDeGouvernanceId' => $evaluationDeGouvernance->id, 'formulaireDeGouvernance_id' => $evaluationDeGouvernance->formulaire_de_perception_de_gouvernance()->id, 'formulaireDeGouvernance_type' => get_class($evaluationDeGouvernance->formulaire_de_perception_de_gouvernance()), 'organisationId' => $organisationId, 'programmeId' => $evaluationDeGouvernance->programmeId]);
                } else {
                    app(FichesDeSyntheseRepository::class)->create(['type' => 'perception', 'indice_de_gouvernance' => $indice_de_perception, 'synthese' => $synthese, 'evaluatedAt' => now(), 'evaluationDeGouvernanceId' => $evaluationDeGouvernance->id, 'formulaireDeGouvernance_id' => $evaluationDeGouvernance->formulaire_de_perception_de_gouvernance()->id, 'organisationId' => $organisationId, 'formulaireDeGouvernance_type' => get_class($evaluationDeGouvernance->formulaire_de_perception_de_gouvernance()), 'programmeId' => $evaluationDeGouvernance->programmeId]);
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

            if ($profile = $evaluationDeGouvernance->profiles($organisationId, $evaluationOrganisationId)->first()) {

                // Convert $profile->resultat_synthetique to an associative collection for easy updating
                $resultat_synthetique = collect($profile->resultat_synthetique)->keyBy('id');

                // Iterate over each item in $results to update or add to $resultat_synthetique
                foreach ($results as $result) {
                    // Check if the entry exists in $resultat_synthetique
                    if ($existing = $resultat_synthetique->get($result['id'])) {

                        // Calculate indice_synthetique by summing indice_factuel and indice_de_perception
                        $existing['indice_synthetique'] = $this->geometricMean([($existing['indice_factuel'] ?? 0), ($existing['indice_de_perception'] ?? 0)]);

                        $resultat_synthetique[$result['id']] = array_merge($resultat_synthetique->get($result['id'], []), $existing);
                    }
                }

                // Convert back to a regular array if needed
                $updated_resultat_synthetique = $resultat_synthetique->values()->toArray();

                // Update the profile with the modified array
                $profile->update(['resultat_synthetique' => $updated_resultat_synthetique]);

                $this->info("Generated result for soumissions" . $profile);
            }
        });
    }

    protected function generateResultForEvaluation(EvaluationDeGouvernance $evaluationDeGouvernance)
    {
        $evaluationDeGouvernance->organisations->map(function ($organisation) use ($evaluationDeGouvernance) {
            $groups_soumissions = $organisation->soumissions()->where("evaluationId", $evaluationDeGouvernance->id)->get()->groupBy(['type']);

            // Initialize result with all types
            $result = new \Illuminate\Database\Eloquent\Collection(collect(["factuel", "perception"])->mapWithKeys(function ($type) use ($groups_soumissions) {
                return [$type => $groups_soumissions->get($type, new \Illuminate\Database\Eloquent\Collection())];
            }));

            $groups_soumissions = $result;

            $profile = null;
            $organisationId = $organisation->id;

            if (!$evaluationOrganisationId = $evaluationDeGouvernance->organisations()->wherePivot('organisationId', $organisationId)->first()->pivot) {
                return;
            }

            $evaluationOrganisationId = $evaluationOrganisationId->id;

            foreach ($groups_soumissions as $group_soumission => $soumissions) {

                if ($group_soumission === "factuel") {

                    [$indice_factuel, $results, $synthese] = $this->generateSyntheseForFactuelTool($evaluationDeGouvernance->formulaire_factuel_de_gouvernance(), $organisationId);

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
                if ($group_soumission === "perception") {

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
                        $existing['indice_synthetique'] = $this->geometricMean([($existing['indice_factuel'] ?? 0), ($existing['indice_de_perception'] ?? 0)]);

                        $resultat_synthetique[$result['id']] = array_merge($resultat_synthetique->get($result['id'], []), $existing);
                    }
                }

                // Convert back to a regular array if needed
                $updated_resultat_synthetique = $resultat_synthetique->values()->toArray();

                // Update the profile with the modified array
                $profile->update(['resultat_synthetique' => $updated_resultat_synthetique]);

                $this->info("Generated result for soumissions" . $profile);
            }
        });

        $organisation_group_soumissions = $evaluationDeGouvernance->soumissions()->where("statut", true)->get()->groupBy(['organisationId', 'type']);

        foreach ($organisation_group_soumissions as $organisationId => $groups_soumissions) {

            // Initialize result with all types
            $result = new \Illuminate\Database\Eloquent\Collection(collect(["factuel", "perception"])->mapWithKeys(function ($type) use ($groups_soumissions) {
                return [$type => $groups_soumissions->get($type, new \Illuminate\Database\Eloquent\Collection())];
            }));

            $groups_soumissions = $result;

            $profile = null;

            foreach ($groups_soumissions as $group_soumission => $soumissions) {

                if (!$evaluationOrganisationId = $evaluationDeGouvernance->organisations()->wherePivot('organisationId', $organisationId)->first()->pivot) {
                    return;
                }

                $evaluationOrganisationId = $evaluationOrganisationId->id;

                if ($group_soumission === "factuel") {

                    [$indice_factuel, $results, $synthese] = $this->generateSyntheseForFactuelTool($evaluationDeGouvernance->formulaire_factuel_de_gouvernance(), $organisationId);

                    //[$indice_factuel, $results, $synthese] = $this->generateSyntheseForFactuelleSoumission($soumissions->first(), $organisationId);

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
                } else if ($group_soumission === "perception") {

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
                        $existing['indice_synthetique'] = $this->geometricMean([($existing['indice_factuel'] ?? 0), ($existing['indice_de_perception'] ?? 0)]);

                        $resultat_synthetique[$result['id']] = array_merge($resultat_synthetique->get($result['id'], []), $existing);
                    }
                }

                // Convert back to a regular array if needed
                $updated_resultat_synthetique = $resultat_synthetique->values()->toArray();

                // Update the profile with the modified array
                $profile->update(['resultat_synthetique' => $updated_resultat_synthetique]);

                $this->info("Generated result for soumissions" . $profile);
            }
        }
    }

    function geometricMean(array $numbers): float
    {
        // Filter out non-positive numbers, as geometric mean is undefined for them
        $filteredNumbers = array_filter($numbers, fn($number) => $number > 0);

        // If the filtered array is empty, return 0
        if (empty($filteredNumbers)) {
            return 0;
        }

        // Calculate the product of the numbers
        $product = array_product($filteredNumbers);

        // Count the number of elements
        $n = count($filteredNumbers);

        // Calculate the geometric mean
        $geometricMean = pow($product, 1 / $n);

        // Return the result rounded to 2 decimal places
        return round($geometricMean, 2);
    }

    /**
     *
     */
    public function generateSyntheseForPerceptionSoumission(FormulaireDeGouvernance $formulaireDeGouvernance, $organisationId)
    {
        $options_de_reponse = $formulaireDeGouvernance->options_de_reponse;
        $principes_de_gouvernance = collect([]);

        $results_categories_de_gouvernance = $formulaireDeGouvernance->categories_de_gouvernance()->with('questions_de_gouvernance.reponses')->get()->each(function ($categorie_de_gouvernance) use ($organisationId, $options_de_reponse, &$principes_de_gouvernance) {
            $categorie_de_gouvernance->questions_de_gouvernance->load(['reponses' => function ($query) use ($organisationId) {
                $query->where('type', 'question_operationnelle')->whereHas("soumission", function ($query) use ($organisationId) {
                    $query->where('evaluationId', $this->evaluationDeGouvernance->id)->where('organisationId', $organisationId);
                });
            }])->each(function ($question_de_gouvernance) use ($organisationId, $options_de_reponse) {

                // Get the total number of responses for NBRE_R
                $nbre_r = $question_de_gouvernance->reponses/* ()->where('type', 'question_operationnelle')->whereHas("soumission", function ($query) use ($organisationId) {
                    $query->where('evaluationId', $this->evaluationDeGouvernance->id)->where('organisationId', $organisationId);
                }) */->count();

                // Initialize the weighted sum
                $weighted_sum = 0;
                $index = 0;
                $question_de_gouvernance->options_de_reponse = collect([]);

                $counts = $question_de_gouvernance->reponses()
                    ->selectRaw('optionDeReponseId, COUNT(*) as count')
                    ->groupBy('optionDeReponseId')
                    ->pluck('count', 'optionDeReponseId');

                foreach ($options_de_reponse as $key => $option_de_reponse) {
                    //$reponses_count = $question_de_gouvernance->reponses()->where("optionDeReponseId", $option_de_reponse->id)->count();

                    $reponses_count = $counts[$option_de_reponse->id] ?? 0;
                    $optionPoint = $option_de_reponse->pivot->point;

                    // Accumulate the weighted sum
                    $weighted_sum += $moyenne_ponderee_i = $optionPoint * $reponses_count;

                    $option = $option_de_reponse;

                    $option->reponses_count = $reponses_count;

                    $option->moyenne_ponderee_i = $moyenne_ponderee_i;

                    $question_de_gouvernance->options_de_reponse[$key] = $option;
                }

                // Calculate the weighted average
                if ($nbre_r > 0) {
                    $question_de_gouvernance->moyenne_ponderee = round(($weighted_sum / $nbre_r), 2);
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

    public function generateResultForPerceptionEvaluation(FormulaireDePerceptionDeGouvernance $formulaireDeGouvernance, $organisationId)
    {
        $options_de_reponse = $formulaireDeGouvernance->options_de_reponse;
        $principes_de_gouvernance = collect([]);

        $results_categories_de_gouvernance = $formulaireDeGouvernance->categories_de_gouvernance()->with('questions_de_gouvernance.reponses')->get()->each(function ($categorie_de_gouvernance) use ($organisationId, $options_de_reponse, &$principes_de_gouvernance) {
            $categorie_de_gouvernance->questions_de_gouvernance->load(['reponses' => function ($query) use ($organisationId) {
                $query->whereHas("soumission", function ($query) use ($organisationId) {
                    $query->where('evaluationId', $this->evaluationDeGouvernance->id)->where('organisationId', $organisationId);
                });
            }])->each(function ($question_de_gouvernance) use ($organisationId, $options_de_reponse) {

                // Get the total number of responses for NBRE_R
                $nbre_r = $question_de_gouvernance->reponses/* ()->where('type', 'question_operationnelle')->whereHas("soumission", function ($query) use ($organisationId) {
                    $query->where('evaluationId', $this->evaluationDeGouvernance->id)->where('organisationId', $organisationId);
                }) */->count();

                // Initialize the weighted sum
                $weighted_sum = 0;
                $index = 0;
                $question_de_gouvernance->options_de_reponse = collect([]);

                $counts = $question_de_gouvernance->reponses()
                    ->selectRaw('optionDeReponseId, COUNT(*) as count')
                    ->groupBy('optionDeReponseId')
                    ->pluck('count', 'optionDeReponseId');

                foreach ($options_de_reponse as $key => $option_de_reponse) {
                    //$reponses_count = $question_de_gouvernance->reponses()->where("optionDeReponseId", $option_de_reponse->id)->count();

                    $reponses_count = $counts[$option_de_reponse->id] ?? 0;
                    $optionPoint = $option_de_reponse->pivot->point;

                    // Accumulate the weighted sum
                    $weighted_sum += $moyenne_ponderee_i = $optionPoint * $reponses_count;

                    $option = $option_de_reponse;

                    $option->reponses_count = $reponses_count;

                    $option->moyenne_ponderee_i = $moyenne_ponderee_i;

                    $question_de_gouvernance->options_de_reponse[$key] = $option;
                }

                // Calculate the weighted average
                if ($nbre_r > 0) {
                    $question_de_gouvernance->moyenne_ponderee = round(($weighted_sum / $nbre_r), 2);
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
        return [$indice_de_perception, $principes_de_gouvernance, FicheDeSyntheseEvaluationFactuelleResource::collection($results_categories_de_gouvernance) ];
    }

    public function generateResultForFactuelEvaluation(FormulaireFactuelDeGouvernance $formulaireDeGouvernance, $organisationId)
    {

        $principes_de_gouvernance = collect([]);

        $results_categories_de_gouvernance = $formulaireDeGouvernance->categories_de_gouvernance()->with(['sousCategoriesDeGouvernance' => function ($query) use ($organisationId) {
            // Call the recursive function to load nested relationships
            $this->loadCategories($query, $organisationId);
        }])->get()->each(function ($categorie_de_gouvernance) use ($organisationId, &$principes_de_gouvernance) {
            $categorie_de_gouvernance->sousCategoriesDeGouvernance->each(function ($sous_categorie_de_gouvernance) use ($organisationId, &$principes_de_gouvernance) {
                $reponses = $this->interpretData($sous_categorie_de_gouvernance, $organisationId);

                $indicateurs = $this->getIndicateurs($sous_categorie_de_gouvernance, $organisationId);

                // Calculate indice_factuel
                if (count($indicateurs) > 0 && $reponses->sum('point') > 0) {
                    $sous_categorie_de_gouvernance->score_factuel = round(($reponses->sum('point') / count($indicateurs)), 2);
                } else {
                    $sous_categorie_de_gouvernance->score_factuel = 0;
                }

                if ($principes_de_gouvernance->count()) {
                    // Check if the item exists in the collection
                    if ($principes_de_gouvernance->firstWhere('id', $sous_categorie_de_gouvernance->categorieable_id)) {
                        // Update the collection item by transforming it
                        $principes_de_gouvernance = $principes_de_gouvernance->transform(function ($item) use ($sous_categorie_de_gouvernance) {

                            if ($item['id'] === $sous_categorie_de_gouvernance->categorieable_id) {
                                // Update the score_factuel
                                $item['indice_factuel'] += $sous_categorie_de_gouvernance->score_factuel;
                            }
                            return $item;
                        });
                    } else {
                        // If the item doesn't exist push the new item
                        $principes_de_gouvernance->push(['id' => $sous_categorie_de_gouvernance->categorieable_id, 'nom' => $sous_categorie_de_gouvernance->categorieable->nom, 'indice_factuel' => $sous_categorie_de_gouvernance->score_factuel]);
                    }
                } else {
                    // If the collection is empty, push the new item
                    $principes_de_gouvernance->push(['id' => $sous_categorie_de_gouvernance->categorieable_id, 'nom' => $sous_categorie_de_gouvernance->categorieable->nom, 'indice_factuel' => $sous_categorie_de_gouvernance->score_factuel]);
                }
            });

            // Calculate indice_factuel
            if ($categorie_de_gouvernance->sousCategoriesDeGouvernance->count() > 0 && $categorie_de_gouvernance->sousCategoriesDeGouvernance->sum('score_factuel') > 0) {
                $categorie_de_gouvernance->indice_factuel = round(($categorie_de_gouvernance->sousCategoriesDeGouvernance->sum('score_factuel') / $categorie_de_gouvernance->sousCategoriesDeGouvernance->count()), 2);
            } else {
                $categorie_de_gouvernance->indice_factuel = 0;
            }
        });

        $indice_factuel = round(($results_categories_de_gouvernance->sum('indice_factuel') / $results_categories_de_gouvernance->count()), 2);

        return [$indice_factuel, $principes_de_gouvernance, FicheDeSyntheseEvaluationFactuelleResource::collection($results_categories_de_gouvernance)];
    }

    public function generateSyntheseForFactuelTool(FormulaireDeGouvernance $formulaireDeGouvernance, $organisationId)
    {

        $principes_de_gouvernance = collect([]);

        $results_categories_de_gouvernance = $formulaireDeGouvernance->categories_de_gouvernance()->with(['sousCategoriesDeGouvernance' => function ($query) use ($organisationId) {
            // Call the recursive function to load nested relationships
            $this->loadCategories($query, $organisationId);
        }])->get()->each(function ($categorie_de_gouvernance) use ($organisationId, &$principes_de_gouvernance) {
            $categorie_de_gouvernance->sousCategoriesDeGouvernance->each(function ($sous_categorie_de_gouvernance) use ($organisationId, &$principes_de_gouvernance) {
                $reponses = $this->interpretData($sous_categorie_de_gouvernance, $organisationId);

                $indicateurs = $this->getIndicateurs($sous_categorie_de_gouvernance, $organisationId);

                // Calculate indice_factuel
                if (count($indicateurs) > 0 && $reponses->sum('point') > 0) {
                    $sous_categorie_de_gouvernance->score_factuel = round(($reponses->sum('point') / count($indicateurs)), 2);
                } else {
                    $sous_categorie_de_gouvernance->score_factuel = 0;
                }

                if ($principes_de_gouvernance->count()) {
                    // Check if the item exists in the collection
                    if ($principes_de_gouvernance->firstWhere('id', $sous_categorie_de_gouvernance->categorieable_id)) {
                        // Update the collection item by transforming it
                        $principes_de_gouvernance = $principes_de_gouvernance->transform(function ($item) use ($sous_categorie_de_gouvernance) {

                            if ($item['id'] === $sous_categorie_de_gouvernance->categorieable_id) {
                                // Update the score_factuel
                                $item['indice_factuel'] += $sous_categorie_de_gouvernance->score_factuel;
                            }
                            return $item;
                        });
                    } else {
                        // If the item doesn't exist push the new item
                        $principes_de_gouvernance->push(['id' => $sous_categorie_de_gouvernance->categorieable_id, 'nom' => $sous_categorie_de_gouvernance->categorieable->nom, 'indice_factuel' => $sous_categorie_de_gouvernance->score_factuel]);
                    }
                } else {
                    // If the collection is empty, push the new item
                    $principes_de_gouvernance->push(['id' => $sous_categorie_de_gouvernance->categorieable_id, 'nom' => $sous_categorie_de_gouvernance->categorieable->nom, 'indice_factuel' => $sous_categorie_de_gouvernance->score_factuel]);
                }
            });

            // Calculate indice_factuel
            if ($categorie_de_gouvernance->sousCategoriesDeGouvernance->count() > 0 && $categorie_de_gouvernance->sousCategoriesDeGouvernance->sum('score_factuel') > 0) {
                $categorie_de_gouvernance->indice_factuel = round(($categorie_de_gouvernance->sousCategoriesDeGouvernance->sum('score_factuel') / $categorie_de_gouvernance->sousCategoriesDeGouvernance->count()), 2);
            } else {
                $categorie_de_gouvernance->indice_factuel = 0;
            }
        });

        $indice_factuel = round(($results_categories_de_gouvernance->sum('indice_factuel') / $results_categories_de_gouvernance->count()), 2);

        return [$indice_factuel, $principes_de_gouvernance, FicheDeSyntheseEvaluationFactuelleResource::collection($results_categories_de_gouvernance)];
    }

    public function generateSyntheseForFactuelleSoumission(Soumission $soumission, $organisationId)
    {
        $principes_de_gouvernance = collect([]);

        $results_categories_de_gouvernance = $soumission->formulaireDeGouvernance->categories_de_gouvernance()->with(['sousCategoriesDeGouvernance' => function ($query) use ($organisationId) {
            // Call the recursive function to load nested relationships
            $this->loadCategories($query, $organisationId);
        }])->get()->each(function ($categorie_de_gouvernance) use ($organisationId, &$principes_de_gouvernance) {
            $categorie_de_gouvernance->sousCategoriesDeGouvernance->each(function ($sous_categorie_de_gouvernance) use ($organisationId, &$principes_de_gouvernance) {
                $reponses = $this->interpretData($sous_categorie_de_gouvernance, $organisationId);

                $indicateurs = $this->getIndicateurs($sous_categorie_de_gouvernance, $organisationId);

                // Calculate indice_factuel
                if (count($indicateurs) > 0 && $reponses->sum('point') > 0) {
                    $sous_categorie_de_gouvernance->score_factuel = round(($reponses->sum('point') / count($indicateurs)), 2);
                } else {
                    $sous_categorie_de_gouvernance->score_factuel = 0;
                }

                if ($principes_de_gouvernance->count()) {
                    // Check if the item exists in the collection
                    if ($principes_de_gouvernance->firstWhere('id', $sous_categorie_de_gouvernance->categorieable_id)) {
                        // Update the collection item by transforming it
                        $principes_de_gouvernance = $principes_de_gouvernance->transform(function ($item) use ($sous_categorie_de_gouvernance) {

                            if ($item['id'] === $sous_categorie_de_gouvernance->categorieable_id) {
                                // Update the score_factuel
                                $item['indice_factuel'] += $sous_categorie_de_gouvernance->score_factuel;
                            }
                            return $item;
                        });
                    } else {
                        // If the item doesn't exist push the new item
                        $principes_de_gouvernance->push(['id' => $sous_categorie_de_gouvernance->categorieable_id, 'nom' => $sous_categorie_de_gouvernance->categorieable->nom, 'indice_factuel' => $sous_categorie_de_gouvernance->score_factuel]);
                    }
                } else {
                    // If the collection is empty, push the new item
                    $principes_de_gouvernance->push(['id' => $sous_categorie_de_gouvernance->categorieable_id, 'nom' => $sous_categorie_de_gouvernance->categorieable->nom, 'indice_factuel' => $sous_categorie_de_gouvernance->score_factuel]);
                }
            });

            // Calculate indice_factuel
            if ($categorie_de_gouvernance->sousCategoriesDeGouvernance->count() > 0 && $categorie_de_gouvernance->sousCategoriesDeGouvernance->sum('score_factuel') > 0) {
                $categorie_de_gouvernance->indice_factuel = round(($categorie_de_gouvernance->sousCategoriesDeGouvernance->sum('score_factuel') / $categorie_de_gouvernance->sousCategoriesDeGouvernance->count()), 2);
            } else {
                $categorie_de_gouvernance->indice_factuel = 0;
            }
        });

        $indice_factuel = round(($results_categories_de_gouvernance->sum('indice_factuel') / $results_categories_de_gouvernance->count()), 2);

        return [$indice_factuel, $principes_de_gouvernance, FicheDeSyntheseEvaluationFactuelleResource::collection($results_categories_de_gouvernance)];
    }

    public function loadCategories($query, $organisationId)
    {
        $query->with(['sousCategoriesDeGouvernance' => function ($query) use ($organisationId) {
            // Recursively load sousCategoriesDeGouvernance
            $this->loadCategories($query, $organisationId);
        }, 'questions_de_gouvernance.reponses' => function ($query) use ($organisationId) {
            $query->whereHas("soumission", function ($query) use ($organisationId) {
                $query->where('evaluationId', $this->evaluationDeGouvernance->id)->where('organisationId', $organisationId);
            })->sum('point');
        }]);
    }

    public function interpretData($categorie_de_gouvernance, $organisationId)
    {
        $reponses = [];
        if ($categorie_de_gouvernance->sousCategoriesDeGouvernance->count()) {
            $categorie_de_gouvernance->sousCategoriesDeGouvernance->each(function ($sous_categorie_de_gouvernance) use (&$reponses, $organisationId) {
                $reponses_data = $this->interpretData($sous_categorie_de_gouvernance, $organisationId);
                $reponses = array_merge($reponses, $reponses_data->toArray());
            });
        } else {
            $categorie_de_gouvernance->questions_de_gouvernance->each(function ($question_de_gouvernance) use (&$reponses, $organisationId) {
                $reponses_de_collecte = $question_de_gouvernance->reponses()->whereHas("soumission", function ($query) use ($organisationId) {
                    $query->where('evaluationId', $this->evaluationDeGouvernance->id)->where('organisationId', $organisationId);
                })->get()->toArray();
                $reponses = array_merge($reponses, $reponses_de_collecte);
            });
        }

        return collect($reponses);
    }

    public function getIndicateurs($categorie_de_gouvernance, $organisationId)
    {
        $indicateurs = [];
        if ($categorie_de_gouvernance->sousCategoriesDeGouvernance->count()) {
            $categorie_de_gouvernance->sousCategoriesDeGouvernance->each(function ($sous_categorie_de_gouvernance) use (&$indicateurs, $organisationId) {
                $data = $this->getIndicateurs($sous_categorie_de_gouvernance, $organisationId);

                $indicateurs = array_merge($indicateurs, $data->toArray());
            });
        } else {
            $indicateurs = array_merge($indicateurs, $categorie_de_gouvernance->questions_de_gouvernance->toArray());
        }

        return collect($indicateurs);
    }
}
