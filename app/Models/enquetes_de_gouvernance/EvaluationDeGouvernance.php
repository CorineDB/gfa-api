<?php

namespace App\Models\enquetes_de_gouvernance;

use App\Models\Organisation;
use App\Models\Programme;
use App\Models\UniteeDeGestion;
use App\Models\User;
use Exception;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use SaiAshirwadInformatia\SecureIds\Models\Traits\HasSecureIds;

class EvaluationDeGouvernance extends Model
{
    protected $table = 'evaluations_de_gouvernance';
    public $timestamps = true;

    use HasSecureIds, HasFactory;

    protected $default = ['objectif_attendu' => 1];

    protected $dates = ['deleted_at'];

    protected $fillable = array('intitule', 'objectif_attendu', 'annee_exercice', 'description', 'debut', 'fin', 'statut', 'programmeId');

    protected $casts = ['statut'  => 'integer', 'debut'  => 'datetime', 'fin'  => 'datetime', 'annee_exercice' => 'integer', 'objectif_attendu' => 'double'];

    //protected $appends = ['pourcentage_evolution', 'pourcentage_evolution_des_soumissions_factuel', 'pourcentage_evolution_des_soumissions_de_perception', 'total_soumissions_factuel', 'total_soumissions_de_perception', 'total_soumissions_factuel_non_demarrer', 'total_soumissions_de_perception_non_demarrer', 'total_soumissions_factuel_terminer', 'total_soumissions_de_perception_terminer', 'total_participants_evaluation_factuel', 'total_participants_evaluation_de_perception', 'options_de_reponse_stats', 'organisations_ranking'];

    protected static function boot()
    {
        parent::boot();

        static::deleting(function ($evaluation_de_gouvernance) {

            DB::beginTransaction();

            try {

                if (($evaluation_de_gouvernance->soumissions->count() > 0) && ($evaluation_de_gouvernance->statut > -1)) {
                    // Prevent deletion by throwing an exception
                    throw new Exception("Impossible de supprimer cette évaluation de gouvernance. Veuillez d'abord supprimer toutes les soumissions associées.");
                }

                $evaluation_de_gouvernance->actions_a_mener()->delete();
                $evaluation_de_gouvernance->recommandations()->delete();
                $evaluation_de_gouvernance->profiles()->delete();
                $evaluation_de_gouvernance->fiches_de_synthese()->delete();
                $evaluation_de_gouvernance->soumissions()->delete();
                $evaluation_de_gouvernance->soumissionsFactuel()->delete();
                $evaluation_de_gouvernance->soumissionsDePerception()->delete();
                $evaluation_de_gouvernance->organisations()->detach();
                //$evaluation_de_gouvernance->formulaires_de_gouvernance()->detach();
                $evaluation_de_gouvernance->formulaires_factuel_de_gouvernance()->detach();
                $evaluation_de_gouvernance->formulaires_de_perception_de_gouvernance()->detach();

                DB::commit();

            } catch (\Throwable $th) {
                DB::rollBack();

                throw new Exception($th->getMessage(), 1);
            }
        });
    }

    public function soumissions()
    {
        if (auth()->check()) {
            return $this->hasMany(Soumission::class, 'evaluationId')->when((optional(auth()->user())->type === 'organisation' || get_class(auth()->user()->profilable) == Organisation::class), function ($query) {
                $organisationId = optional(auth()->user()->profilable)->id;

                // If the organisationId is null, return an empty collection
                if (is_null($organisationId)) {
                    $query->whereRaw('1 = 0'); // Ensures no results are returned
                } else {
                    $query->where('organisationId', $organisationId);
                }
            });
        } else {
            return $this->hasMany(Soumission::class, 'evaluationId');
        }
    }

    public function soumissionsDePerception()
    {
        if (auth()->check()) {
            return $this->hasMany(SoumissionDePerception::class, 'evaluationId')->when((optional(auth()->user())->type === 'organisation' || get_class(auth()->user()->profilable) == Organisation::class), function ($query) {
                $organisationId = optional(auth()->user()->profilable)->id;

                // If the organisationId is null, return an empty collection
                if (is_null($organisationId)) {
                    $query->whereRaw('1 = 0'); // Ensures no results are returned
                } else {
                    $query->where('organisationId', $organisationId);
                }
            });
        } else {
            return $this->hasMany(SoumissionDePerception::class, 'evaluationId');
        }
    }

    public function soumissionsFactuel()
    {
        return $this->hasMany(SoumissionFactuel::class, 'evaluationId')->when((optional(auth()->user())->type === 'organisation' || get_class(auth()->user()->profilable) == Organisation::class), function ($query) {
            $organisationId = optional(auth()->user()->profilable)->id;

            // If the organisationId is null, return an empty collection
            if (is_null($organisationId)) {
                $query->whereRaw('1 = 0'); // Ensures no results are returned
            } else {
                $query->where('organisationId', $organisationId);
            }
        });
    }

    public function soumissionFactuel(?int $organisationId = null, ?string $token = null)
    {
        $soumissionFactuel = $this->hasOne(SoumissionFactuel::class, 'evaluationId')->when((optional(auth()->user())->type === 'organisation' || get_class(auth()->user()->profilable) == Organisation::class), function ($query) {
            $query->where('organisationId', optional(auth()->user()->profilable)->id);
        })/* ->where('organisationId', $organisationId)->orWhere(function($query) use($token){
            $query->whereHas('organisation', function($query) use($token){
                $query->whereHas('evaluations_de_gouvernance', function($query) use($token){
                    $query->wherePivot('token', $token);
                });
            });
        }) */;

        if ($organisationId) {
            $soumissionFactuel = $soumissionFactuel->where('organisationId', $organisationId);
        }

        if ($token) {
            $soumissionFactuel = $soumissionFactuel->where('organisationId', $this->organisations($organisationId, $token)->first()->id);
        }

        return $soumissionFactuel;
    }


    public function soumissionDePerception(?string $identifier_of_participant = null, ?int $organisationId = null, ?string $token = null)
    {
        $soumissionDePerception = $this->hasMany(SoumissionDePerception::class, 'evaluationId');

        if ($identifier_of_participant) {
            $soumissionDePerception = $soumissionDePerception->where("identifier_of_participant", $identifier_of_participant);
        }

        if ($organisationId) {
            $soumissionDePerception = $soumissionDePerception->where('organisationId', $organisationId);
        }

        if ($token) {
            $soumissionDePerception = $soumissionDePerception->where('organisationId', $this->organisations($organisationId, $token)->first()->id);
        }

        return $soumissionDePerception;
    }

    public function programme()
    {
        return $this->belongsTo(Programme::class, 'programmeId');
    }

    public function organisations(int $organisationId = null, string $token = null)
    {
        // Start with the base relationship
        $organisations = $this->belongsToMany(Organisation::class, 'evaluation_organisations', 'evaluationDeGouvernanceId', 'organisationId')->wherePivotNull('deleted_at')->withPivot(['id', 'nbreParticipants', 'participants', 'token'])->whereHas('user.profilable');

        if ($organisationId) {
            $organisations = $organisations->wherePivot("organisationId", $organisationId);
        }

        if ($token) {
            $organisations = $organisations->wherePivot("token", $token);
        }

        return $organisations;
    }


    /**
     * Get the users associated with the organisations of the evaluation.
     */
    public function organisations_user()
    {
        return User::whereHas('profilable', function ($query) {
            $query->whereIn('profilable_id', $this->organisations->pluck('id'))->where('profilable_type', Organisation::class);
        })->get();
    }

    public function formulaires_de_gouvernance()
    {
        return $this->belongsToMany(FormulaireDeGouvernance::class, 'evaluation_de_gouvernance_formulaires', 'evaluationDeGouvernanceId', 'formulaireDeGouvernanceId')->wherePivotNull('deleted_at');
    }

    public function formulaires_factuel_de_gouvernance()
    {
        return $this->belongsToMany(FormulaireFactuelDeGouvernance::class, 'evaluation_de_gouvernance_formulaires', 'evaluationDeGouvernanceId', 'formulaireFactuelId')->whereNotNull('formulaireFactuelId')->wherePivotNull('deleted_at');
    }

    public function formulaires_de_perception_de_gouvernance()
    {
        return $this->belongsToMany(FormulaireDePerceptionDeGouvernance::class, 'evaluation_de_gouvernance_formulaires', 'evaluationDeGouvernanceId', 'formulaireDePerceptionId')->whereNotNull('formulaireDePerceptionId')->wherePivotNull('deleted_at');
    }

    public function formulaire_factuel_de_gouvernance()
    {
        return $this->belongsToMany(FormulaireFactuelDeGouvernance::class, 'evaluation_de_gouvernance_formulaires', 'evaluationDeGouvernanceId', 'formulaireFactuelId')->whereNotNull('formulaireFactuelId')->wherePivotNull('deleted_at')->first();
    }

    public function formulaire_de_perception_de_gouvernance()
    {
        return $this->belongsToMany(FormulaireDePerceptionDeGouvernance::class, 'evaluation_de_gouvernance_formulaires', 'evaluationDeGouvernanceId', 'formulaireDePerceptionId')->whereNotNull('formulaireDePerceptionId')->wherePivotNull('deleted_at')->first();
    }

    public function principes_de_gouvernance()
    {
        return $this->formulaire_de_perception_de_gouvernance()->principes_de_gouvernance();
    }

    public function recommandations()
    {
        return $this->hasMany(Recommandation::class, 'evaluationId')
        ->when((Auth::user()->hasRole('organisation') || (get_class(auth()->user()->profilable) == Organisation::class)), function ($query) {
            // Return 0 if user type is neither 'organisation' nor 'unitee-de-gestion'
            dd((auth()->user()->profilable->id));
            $query->where('organisationId', auth()->user()->profilable->id); // Ensures no results are returned
        });
        return $this->morphMany(Recommandation::class, "recommandationable");
    }

    public function actions_a_mener()
    {
        return $this->hasMany(ActionAMener::class, 'evaluationId')
        ->when((Auth::user()->hasRole('organisation') || (get_class(auth()->user()->profilable) == Organisation::class)), function ($query) {
            // Return 0 if user type is neither 'organisation' nor 'unitee-de-gestion'
            $query->where('organisationId', auth()->user()->profilable->id); // Ensures no results are returned
        });
        return $this->morphMany(ActionAMener::class, "actionable");
    }

    /*public function fiches_de_synthese()
    {
        return $this->hasManyThrough(
            FicheDeSynthese::class,
            Soumission::class,
            'evaluationId',
            'soumissionId',
            'id',
            'id'
        );
    }*/

    /**
     * Retrieve fiches de synthese associated with the evaluation.
     *
     * This method returns a collection of FicheDeSynthese models related to the
     * EvaluationDeGouvernance, with optional filtering by organisation ID and type.
     *
     * @param int|null $organisationId Optional organisation ID to filter the fiches de synthese.
     * @param string|null $type Optional type to filter the fiches de synthese.
     * @return \Illuminate\Database\Eloquent\Builder
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function fiches_de_synthese(?int $organisationId = null, ?string $type = null): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        // Start with the base relationship
        $fiches_de_synthese = $this->hasMany(FicheDeSynthese::class, 'evaluationDeGouvernanceId');

        // Apply additional filtering conditions if needed
        if ($type) {
            $fiches_de_synthese = $fiches_de_synthese->where("type", $type);
        }

        if ($organisationId) {
            $fiches_de_synthese = $fiches_de_synthese->where("organisationId", $organisationId);
        }

        // Get the results and apply grouping on the collection level

        return $fiches_de_synthese;
    }


    public function fiches_de_synthese_factuel(?int $organisationId = null): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        // Start with the base relationship
        $fiches_de_synthese = $this->hasMany(FicheDeSynthese::class, 'evaluationDeGouvernanceId')->where("type", "factuel");

        if ($organisationId) {
            $fiches_de_synthese = $fiches_de_synthese->where("organisationId", $organisationId);
        }

        // Get the results and apply grouping on the collection level

        return $fiches_de_synthese;
    }

    public function fiches_de_synthese_de_perception(?int $organisationId = null): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        // Start with the base relationship
        $fiches_de_synthese = $this->hasMany(FicheDeSynthese::class, 'evaluationDeGouvernanceId')->where("type", "perception");

        if ($organisationId) {
            $fiches_de_synthese = $fiches_de_synthese->where("organisationId", $organisationId);
        }

        // Get the results and apply grouping on the collection level

        return $fiches_de_synthese;
    }

    public function profiles(?int $organisationId = null, ?int $evaluationOrganisationId = null): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        // Start with the base relationship
        $profiles = $this->hasMany(ProfileDeGouvernance::class, 'evaluationDeGouvernanceId');

        if ($organisationId) {
            $profiles = $profiles->where("organisationId", $organisationId);
        }

        if ($evaluationOrganisationId) {
            $profiles = $profiles->where("evaluationOrganisationId", $evaluationOrganisationId);
        }

        // Get the results and apply grouping on the collection level

        return $profiles;
    }

    public function failedProfilesDeGouvernance(?int $organisationId = null, ?int $evaluationOrganisationId = null, ?float $threshold  = 0.5)
    {

        // Start the query by calling the profiles method
        $profile = $this->profiles();

        $threshold = $this->objectif_attendu ? $this->objectif_attendu : $threshold;

        // Apply filtering for 'indice_synthetique' under the dynamic threshold using MySQL JSON functions
        return $profile->whereRaw('CAST(JSON_UNQUOTE(JSON_EXTRACT(resultat_synthetique, "$[*].indice_synthetique")) AS UNSIGNED) < ?', [$threshold]);
    }

    public function organisationsClassement()
    {
        $profiles = $this->profiles();

        // Calculate averages for all indices in one query
        $averages = $profiles
            ->selectRaw('
            AVG(CAST(JSON_UNQUOTE(JSON_EXTRACT(resultat_synthetique, "$[*].indice_factuel")) AS DECIMAL(10, 2))) AS avg_indice_factuel,
            AVG(CAST(JSON_UNQUOTE(JSON_EXTRACT(resultat_synthetique, "$[*].indice_de_perception")) AS DECIMAL(10, 2))) AS avg_indice_de_perception,
            AVG(CAST(JSON_UNQUOTE(JSON_EXTRACT(resultat_synthetique, "$[*].indice_synthetique")) AS DECIMAL(10, 2))) AS avg_indice_synthetique
        ')->first();

        // Extract averages
        $avgIndiceFactuel = $averages->avg_indice_factuel;
        $avgIndiceDePerception = $averages->avg_indice_de_perception;
        $avgIndiceSynthetique = $averages->avg_indice_synthetique;

        // Fetch profiles with organization information and values
        $profilesData = $profiles
            // Join the users table to access the organisation information via polymorphic relation
            ->join('users', 'profiles_de_gouvernance.organisationId', '=', 'users.profilable_id') // Join users table using the polymorphic relation
            ->join('organisations', function ($join) {
                // Join organisations table using polymorphic relation
                $join->on('organisations.id', '=', 'users.profilable_id')
                     ->where('users.profilable_type', '=', 'App\\Models\\Organisation');
            })
            // Select necessary fields
            ->select('profiles_de_gouvernance.id',
                     'profiles_de_gouvernance.organisationId',
                     DB::raw('CONCAT(users.nom, " - ", organisations.sigle) as organisationName') // Combine `users.nom` and `organisations.sigle`
                    )
            ->selectRaw('
                CAST(JSON_UNQUOTE(JSON_EXTRACT(resultat_synthetique, "$[*].indice_factuel")) AS DECIMAL(10, 2)) AS indice_factuel,
                CAST(JSON_UNQUOTE(JSON_EXTRACT(resultat_synthetique, "$[*].indice_de_perception")) AS DECIMAL(10, 2)) AS indice_de_perception,
                CAST(JSON_UNQUOTE(JSON_EXTRACT(resultat_synthetique, "$[*].indice_synthetique")) AS DECIMAL(10, 2)) AS indice_synthetique
            ')->get();

        // Group profiles into greater than and lower than for each index
        $groupedData = [
            'indice_factuel_avg' => [
                'greater_than_avg' => [],
                'lower_than_avg' => []
            ],
            'indice_de_perception_avg' => [
                'greater_than_avg' => [],
                'lower_than_avg' => []
            ],
            'indice_synthetique_avg' => [
                'greater_than_avg' => [],
                'lower_than_avg' => []
            ],
        ];

        foreach ($profilesData as $profile) {
            $organisationId = $profile->organisation->secure_id; // Access the related Organisation model

            // Factuel
            $groupedData['indice_factuel_avg'][$profile->indice_factuel >= $avgIndiceFactuel ? 'greater_than_avg' : 'lower_than_avg'][] = [
                'organisationId'   => $organisationId,
                'organisationName' => $profile->organisationName,
                'indice_factuel'   => (float) $profile->indice_factuel,
            ];

            // Perception
            $groupedData['indice_de_perception_avg'][$profile->indice_de_perception >= $avgIndiceDePerception ? 'greater_than_avg' : 'lower_than_avg'][] = [
                'organisationId'       => $organisationId,
                'organisationName'     => $profile->organisationName,
                'indice_de_perception' => (float) $profile->indice_de_perception,
            ];

            // Synthetique
            $groupedData['indice_synthetique_avg'][$profile->indice_synthetique >= $avgIndiceSynthetique ? 'greater_than_avg' : 'lower_than_avg'][] = [
                'organisationId'     => $organisationId,
                'organisationName'   => $profile->organisationName,
                'indice_synthetique' => (float) $profile->indice_synthetique,
            ];
        }

        return $groupedData;

        // Start the query by calling the profiles method
        $profiles = $this->profiles();

        // Calculate the average of `indice_factuel` across all profiles
        $avgIndiceFactuel = $profiles
            ->selectRaw('AVG(CAST(JSON_UNQUOTE(JSON_EXTRACT(resultat_synthetique, "$[*].indice_factuel")) AS DECIMAL(10, 2))) AS avg_indice_factuel')
            ->value('avg_indice_factuel');

        // Group profiles into two categories: greater than or less than the average
        $greaterThanAvgIndiceFactuel = $profiles
            ->select('id', 'organisationId')
            ->selectRaw('CAST(JSON_UNQUOTE(JSON_EXTRACT(resultat_synthetique, "$[*].indice_factuel")) AS DECIMAL(10, 2)) AS indice_factuel')
            ->whereRaw('CAST(JSON_UNQUOTE(JSON_EXTRACT(resultat_synthetique, "$[*].indice_factuel")) AS DECIMAL(10, 2)) >= ?', [$avgIndiceFactuel])
            ->get();

        $lowerThanAvgIndiceFactuel = $profiles
            ->select('id', 'organisationId')
            ->selectRaw('CAST(JSON_UNQUOTE(JSON_EXTRACT(resultat_synthetique, "$[*].indice_factuel")) AS DECIMAL(10, 2)) AS indice_factuel')
            ->whereRaw('CAST(JSON_UNQUOTE(JSON_EXTRACT(resultat_synthetique, "$[*].indice_factuel")) AS DECIMAL(10, 2)) < ?', [$avgIndiceFactuel])
            ->get();

        // Calculate the average of `indice_de_perception` across all profiles
        $avgIndiceDePerception = $profiles
            ->selectRaw('AVG(CAST(JSON_UNQUOTE(JSON_EXTRACT(resultat_synthetique, "$[*].indice_de_perception")) AS DECIMAL(10, 2))) AS avg_indice_de_perception')
            ->value('avg_indice_de_perception');

        // Group profiles into two categories: greater than or less than the average
        $greaterThanAvgIndiceDePerception = $profiles
            ->select('id', 'organisationId')
            ->selectRaw('CAST(JSON_UNQUOTE(JSON_EXTRACT(resultat_synthetique, "$[*].indice_de_perception")) AS DECIMAL(10, 2)) AS indice_de_perception')
            ->whereRaw('CAST(JSON_UNQUOTE(JSON_EXTRACT(resultat_synthetique, "$[*].indice_de_perception")) AS DECIMAL(10, 2)) >= ?', [$avgIndiceDePerception])
            ->get();

        $lowerThanAvgIndiceDePerception = $profiles
            ->select('id', 'organisationId')
            ->selectRaw('CAST(JSON_UNQUOTE(JSON_EXTRACT(resultat_synthetique, "$[*].indice_de_perception")) AS DECIMAL(10, 2)) AS indice_de_perception')
            ->whereRaw('CAST(JSON_UNQUOTE(JSON_EXTRACT(resultat_synthetique, "$[*].indice_de_perception")) AS DECIMAL(10, 2)) < ?', [$avgIndiceDePerception])
            ->get();

        // Calculate the average of `indice_synthetique` across all profiles
        $avgIndiceSynthetique = $profiles
            ->selectRaw('AVG(CAST(JSON_UNQUOTE(JSON_EXTRACT(resultat_synthetique, "$[*].indice_synthetique")) AS DECIMAL(10, 2))) AS avg_indice_synthetique')
            ->value('avg_indice_synthetique');

        // Group profiles into two categories: greater than or less than the average
        $greaterThanAvgIndiceSynthetique = $profiles
            ->select('id', 'organisationId')
            ->selectRaw('CAST(JSON_UNQUOTE(JSON_EXTRACT(resultat_synthetique, "$[*].indice_synthetique")) AS DECIMAL(10, 2)) AS indice_synthetique')
            ->whereRaw('CAST(JSON_UNQUOTE(JSON_EXTRACT(resultat_synthetique, "$[*].indice_synthetique")) AS DECIMAL(10, 2)) >= ?', [$avgIndiceSynthetique])
            ->get();

        $lowerThanAvgIndiceSynthetique = $profiles
            ->select('id', 'organisationId')
            ->selectRaw('CAST(JSON_UNQUOTE(JSON_EXTRACT(resultat_synthetique, "$[*].indice_synthetique")) AS DECIMAL(10, 2)) AS indice_synthetique')
            ->whereRaw('CAST(JSON_UNQUOTE(JSON_EXTRACT(resultat_synthetique, "$[*].indice_synthetique")) AS DECIMAL(10, 2)) < ?', [$avgIndiceSynthetique])
            ->get();

        return [
            'indice_factuel_avg' => [
                'greater_than_avg' => $greaterThanAvgIndiceFactuel,
                'lower_than_avg' => $lowerThanAvgIndiceFactuel
            ],
            'indice_de_perception_avg' => [
                'greater_than_avg' => $greaterThanAvgIndiceDePerception,
                'lower_than_avg' => $lowerThanAvgIndiceDePerception
            ],
            'indice_synthetique_avg' => [
                'greater_than_avg' => $greaterThanAvgIndiceSynthetique,
                'lower_than_avg' => $lowerThanAvgIndiceSynthetique
            ],
        ];
    }

    public function getPourcentageEvolutionAttribute()
    {

        if($this->formulaire_de_perception_de_gouvernance() && $this->formulaire_factuel_de_gouvernance()){

            // Avoid division by zero by checking that total participants are non-zero
            if ($this->pourcentage_evolution_des_soumissions_factuel == 0 && $this->pourcentage_evolution_des_soumissions_de_perception == 0) {
                return 0;
            }

            return round(($this->pourcentage_evolution_des_soumissions_factuel + $this->pourcentage_evolution_des_soumissions_de_perception) / 2, 2);

        }
        elseif($this->formulaire_de_perception_de_gouvernance()){

            // Avoid division by zero by checking that total participants are non-zero
            return $this->pourcentage_evolution_des_soumissions_de_perception;

        }
        elseif($this->formulaire_factuel_de_gouvernance()){
            return $this->pourcentage_evolution_des_soumissions_factuel;
        }

        // Avoid division by zero by checking that total participants are non-zero
        if ($this->pourcentage_evolution_des_soumissions_factuel == 0 && $this->pourcentage_evolution_des_soumissions_de_perception == 0) {
            return 0;
        }

        return round(($this->pourcentage_evolution_des_soumissions_factuel + $this->pourcentage_evolution_des_soumissions_de_perception) / 2, 2);

        return ($this->pourcentage_evolution_des_soumissions_factuel + $this->pourcentage_evolution_des_soumissions_de_perception) / 2;
    }

    public function getPourcentageEvolutionDesSoumissionsFactuelAttribute()
    {
        // Avoid division by zero by checking that total soumissions factuel or total participants are non-zero
        if ($this->total_soumissions_factuel == 0 || $this->total_participants_evaluation_factuel == 0) {
            return 0;
        }

        return round((($this->total_soumissions_factuel * 100) / $this->total_participants_evaluation_factuel), 2);
    }

    public function getPourcentageEvolutionDesSoumissionsDePerceptionAttribute()
    {
        // Avoid division by zero by checking that total soumissions de perception or total participants are non-zero
        if ($this->total_soumissions_de_perception == 0 || $this->total_participants_evaluation_de_perception == 0) {
            return 0;
        }

        return round((($this->total_soumissions_de_perception  * 100) / $this->total_participants_evaluation_de_perception), 2);
    }

    public function getTotalSoumissionsFactuelAttribute()
    {
        return $this->soumissionsFactuel()->count();
    }

    public function getTotalSoumissionsDePerceptionAttribute()
    {
        return $this->soumissionsDePerception()->count();
    }

    public function getTotalSoumissionsFactuelNonDemarrerAttribute()
    {
        // Filter organisations if the authenticated user's type is 'organisation'
        $totalOrganisations = $this->organisations()->when(((auth()->user()->type == 'organisation') || get_class(optional(auth()->user()->profilable)) == Organisation::class), function ($query) {
            // Get the organisation ID of the authenticated user
            $organisationId = optional(auth()->user()->profilable)->id;

            // If profilable is null or ID is missing, return 0
            if (!$organisationId) {
                return 0;
            }

            // Filter the organisations and sum the 'nbreParticipants' from the pivot table
            $query->where('organisations.id', $organisationId);
        })
            ->when(((!in_array(auth()->user()->type, ['organisation', 'unitee-de-gestion'])) && (get_class(optional(auth()->user()->profilable)) != Organisation::class && get_class(optional(auth()->user()->profilable)) != UniteeDeGestion::class)), function ($query) {
                // Return 0 if user type is neither 'organisation' nor 'unitee-de-gestion'
                $query->whereRaw('1 = 0'); // Ensures no results are returned
            })->count();

        // Calculate total soumissionsFactuel count
        $totalSoumissionsFactuel = $this->soumissionsFactuel()->count();

        if($totalOrganisations){

            // Return the difference
            return $totalOrganisations - $totalSoumissionsFactuel;

        }
        return 0;
    }

    public function getTotalSoumissionsDePerceptionNonDemarrerAttribute()
    {

        if($this->total_participants_evaluation_de_perception>0){
            return $this->total_participants_evaluation_de_perception - $this->soumissionsDePerception()->count();
        }
        return 0;
    }

    public function getTotalSoumissionsFactuelTerminerAttribute()
    {
        return $this->soumissionsFactuel()->where('statut', true)->count();
    }

    public function getTotalSoumissionsDePerceptionTerminerAttribute()
    {
        return $this->soumissionsDePerception()->where('statut', true)->count();
    }

    public function getTotalParticipantsEvaluationFactuelAttribute()
    {
        // Sum the 'nbreParticipants' attribute from the pivot table
        if ((auth()->user()->type == 'organisation') || get_class(optional(auth()->user()->profilable)) == Organisation::class) {
            if (auth()->user()->profilable) {
                return $this->formulaire_factuel_de_gouvernance() ? ( $this->organisations(optional(auth()->user()->profilable)->id)->count() ?? 0) : 0;
            } else {
                return 0;
            }
        } elseif ((auth()->user()->type == 'unitee-de-gestion') || get_class(optional(auth()->user()->profilable)) == UniteeDeGestion::class) {
            return $this->organisations()->count();
        } else {
            return 0;
        }
    }

    public function getTotalParticipantsEvaluationDePerceptionAttribute()
    {

        return $this->organisations()
            ->when((auth()->user()->type == 'organisation' || get_class(optional(auth()->user()->profilable)) == Organisation::class), function ($query) {
                // Get the organisation ID of the authenticated user
                $organisationId = optional(auth()->user()->profilable)->id;

                // If profilable is null or ID is missing, return 0
                if (!$organisationId) {
                    return 0;
                }

                // Filter the organisations and sum the 'nbreParticipants' from the pivot table
                $query->where('organisations.id', $organisationId);
            })
            ->when(((!in_array(auth()->user()->type, ['organisation', 'unitee-de-gestion'])) && (optional(auth()->user()->profilable) != Organisation::class && auth()->user()->profilable != UniteeDeGestion::class)), function ($query) {
                // Return 0 if user type is neither 'organisation' nor 'unitee-de-gestion'
                $query->whereRaw('1 = 0'); // Ensures no results are returned
            })->get()  // Retrieve organisations
            ->sum(function ($organisation) {
                return $organisation->pivot->nbreParticipants ?? 0;
            });
    }

    public function getOrganisationsRankingAttribute()
    {
        // Calculate completion for each organization and rank
        $ranking = $this->organisations->map(function ($organisation) {
            return [
                "id"                    => $organisation->secure_id,
                'nom'                   => $organisation->user->nom ?? null,
                'sigle'                 => $organisation->sigle,
                'code'                  => $organisation->code,
                'nom_point_focal'       => $organisation->nom_point_focal,
                'prenom_point_focal'    => $organisation->prenom_point_focal,
                'contact_point_focal'   => $organisation->contact_point_focal,/*
                'nbreParticipants'              => $organisation->pivot->nbreParticipants,
                'PerceptionSubmissionsCompletion' => $organisation->getPerceptionSubmissionsCompletionAttribute($this->id), */
                'pourcentage_evolution' => $organisation->getSubmissionRateAttribute($this->id),
            ];
        });

        // Sort organizations by completion rate (descending)
        return $ranking->sortByDesc('pourcentage_evolution')->values();
    }

    public function getOptionsDeReponseStatsAttribute()
    {

        // Get all soumission IDs
        $soumissionIds = $this->soumissionsDePerception->pluck("id");

        // Get all options (options_de_reponse) and their IDs
        $options = $this->formulaire_de_perception_de_gouvernance() ? $this->formulaire_de_perception_de_gouvernance()->options_de_reponse : collect([]);
        $optionIds = $options->pluck('id');
        $optionLibelles = $options->pluck('libelle', 'id');

        // Categories to include in the Cartesian product
        $categories = ['membre_de_conseil_administration', 'employe_association', 'membre_association'];

        // Generate the Cartesian product of all organisations, categories, and options
        $organisations = $this->organisations;

        // Generate the Cartesian product of all categories and options
        $combinations = [];
        foreach ($organisations as $organisation) {
            foreach ($categories as $category) {
                foreach ($optionLibelles as $optionId => $optionLibelle) {
                    $combinations[] = [
                        'organisationId' => $organisation->id,
                        'categorieDeParticipant' => $category,
                        'optionDeReponseId' => $optionId,
                        'libelle' => $optionLibelle
                    ];
                }
            }
        }

        // Get the response counts from the database
        $responseCounts = DB::table('reponses_de_la_collecte')
            ->join('soumissions', 'reponses_de_la_collecte.soumissionId', '=', 'soumissions.id')
            ->join('options_de_reponse', 'reponses_de_la_collecte.optionDeReponseId', '=', 'options_de_reponse.id')
            ->select(
                'soumissions.organisationId',
                'soumissions.categorieDeParticipant',
                'options_de_reponse.libelle',
                DB::raw('COUNT(reponses_de_la_collecte.id) as count')
            )
            ->whereIn('reponses_de_la_collecte.soumissionId', $soumissionIds)
            ->whereIn('reponses_de_la_collecte.optionDeReponseId', $optionIds)
            ->groupBy('soumissions.organisationId', 'soumissions.categorieDeParticipant', 'options_de_reponse.libelle')
            ->get();

        // Combine the counts with the pre-generated combinations, ensuring no missing combinations
        $query = collect($combinations)->map(function ($combination) use ($responseCounts) {

            // Find the response count for this combination using where with multiple conditions
            $responseCount = $responseCounts->where('organisationId', $combination['organisationId'])
                ->where('categorieDeParticipant', $combination['categorieDeParticipant'])
                ->where('libelle', $combination['libelle'])
                ->first(); // Get the first matching response (or null if none)

            // If no response count found, set to 0
            $combination['count'] = $responseCount ? $responseCount->count : 0;

            return $combination;
        });

        // Reorganize data under each organisation and categorieDeParticipant
        $groupedStats = $query->groupBy('organisationId')->map(function ($dataByOrganisation, $organisationId) use ($organisations) {
            $organisation = $organisations->firstWhere('id', $organisationId);
            return [
                'id' => $organisation->secure_id,
                'intitule' => $organisation->sigle . " - " . $organisation->user->nom,
                'categories' => $dataByOrganisation->groupBy('categorieDeParticipant')->map(function ($optionsDeReponse, $categorie) {
                    return [
                        'categorieDeParticipant' => $categorie,
                        'options_de_reponse' => $optionsDeReponse->map(function ($optionDeReponse) {
                            return [
                                'label' => $optionDeReponse['libelle'],
                                'count' => $optionDeReponse['count'],
                            ];
                        }),
                    ];
                })->values(),
            ];
        });

        // Return the grouped stats as values
        return $groupedStats->values();

        $query = DB::table('reponses_de_la_collecte')
            //->join('soumissions', 'reponses_de_la_collecte.soumissionId', '=', 'soumissions.id')
            ->join('soumissions', function ($join) {
                $join->on('reponses_de_la_collecte.soumissionId', '=', 'soumissions.id')->where('soumissions.statut', true);
            })
            ->join('options_de_reponse', 'reponses_de_la_collecte.optionDeReponseId', '=', 'options_de_reponse.id')
            ->select(
                'soumissions.categorieDeParticipant',  // Group by participant category
                'options_de_reponse.libelle as label', // Group by label label
                DB::raw('COUNT(*) as count') // Count occurrences
            )
            ->when(!empty($soumissionIds), function ($query) use ($soumissionIds) {
                return $query->whereIn('reponses_de_la_collecte.soumissionId', $soumissionIds);
            })
            ->when(!empty($optionIds), function ($query) use ($optionIds) {
                return $query->whereIn('reponses_de_la_collecte.optionDeReponseId', $optionIds);
            })
            ->groupBy('soumissions.categorieDeParticipant', 'options_de_reponse.libelle') // Grouping logic
            ->orderBy('soumissions.categorieDeParticipant')
            ->orderBy('options_de_reponse.libelle')
            ->get();

        return $query;
        $responseCounts = DB::table('reponses_de_la_collecte')
            ->join('soumissions', 'reponses_de_la_collecte.soumissionId', '=', 'soumissions.id')
            ->join('options_de_reponse', 'reponses_de_la_collecte.optionDeReponseId', '=', 'options_de_reponse.id')
            ->select(
                'soumissions.categorieDeParticipant',  // Group by soumission categorieDeParticipant
                'options_de_reponse.libelle',          // Group by option libelle (response)
                DB::raw('COUNT(*) as count') // Count occurrences
            )
            ->groupBy('soumissions.categorieDeParticipant', 'options_de_reponse.libelle') // Grouping logic
            ->orderBy('soumissions.categorieDeParticipant')
            ->orderBy('options_de_reponse.libelle')
            ->get();

        return $responseCounts;
    }

    public function getOptionsDeReponseGouvernanceStatsAttribute()
    {
        // Get all soumission IDs
        $soumissionIds = $this->soumissionsDePerception->pluck("id");

        // Get all options (options_de_reponse) and their IDs
        $options = $this->formulaire_de_perception_de_gouvernance() ? $this->formulaire_de_perception_de_gouvernance()->options_de_reponse : collect([]);
        $optionIds = $options->pluck('id');
        $optionLibelles = $options->pluck('libelle', 'id');

        // Categories to include in the Cartesian product
        $categories = ['membre_de_conseil_administration', 'employe_association', 'membre_association'];

        // Generate the Cartesian product of all organisations, categories, and options
        $organisations = $this->organisations;

        // Generate the Cartesian product of all categories and options
        $combinations = [];
        foreach ($organisations as $organisation) {
            foreach ($categories as $category) {
                foreach ($optionLibelles as $optionId => $optionLibelle) {
                    $combinations[] = [
                        'organisationId' => $organisation->id,
                        'categorieDeParticipant' => $category,
                        'optionDeReponseId' => $optionId,
                        'libelle' => $optionLibelle
                    ];
                }
            }
        }

        // Get the response counts from the database
        $responseCounts = DB::table('reponses_de_la_collecte_de_perception')
            ->join('soumissions_de_perception', 'reponses_de_la_collecte_de_perception.soumissionId', '=', 'soumissions_de_perception.id')
            ->join('options_de_reponse_gouvernance', 'reponses_de_la_collecte_de_perception.optionDeReponseId', '=', 'options_de_reponse_gouvernance.id')
            ->select(
                'soumissions_de_perception.organisationId',
                'soumissions_de_perception.categorieDeParticipant',
                'options_de_reponse_gouvernance.libelle',
                DB::raw('COUNT(reponses_de_la_collecte_de_perception.id) as count')
            )
            ->whereIn('reponses_de_la_collecte_de_perception.soumissionId', $soumissionIds)
            ->whereIn('reponses_de_la_collecte_de_perception.optionDeReponseId', $optionIds)
            ->groupBy('soumissions_de_perception.organisationId', 'soumissions_de_perception.categorieDeParticipant', 'options_de_reponse_gouvernance.libelle')
            ->get();

        // Combine the counts with the pre-generated combinations, ensuring no missing combinations
        $query = collect($combinations)->map(function ($combination) use ($responseCounts) {

            // Find the response count for this combination using where with multiple conditions
            $responseCount = $responseCounts->where('organisationId', $combination['organisationId'])
                ->where('categorieDeParticipant', $combination['categorieDeParticipant'])
                ->where('libelle', $combination['libelle'])
                ->first(); // Get the first matching response (or null if none)

            // If no response count found, set to 0
            $combination['count'] = $responseCount ? $responseCount->count : 0;

            return $combination;
        });

        // Reorganize data under each organisation and categorieDeParticipant
        $groupedStats = $query->groupBy('organisationId')->map(function ($dataByOrganisation, $organisationId) use ($organisations) {
            $organisation = $organisations->firstWhere('id', $organisationId);
            return [
                'id' => $organisation->secure_id,
                'intitule' => $organisation->sigle . " - " . $organisation->user->nom,
                'categories' => $dataByOrganisation->groupBy('categorieDeParticipant')->map(function ($optionsDeReponse, $categorie) {
                    return [
                        'categorieDeParticipant' => $categorie,
                        'options_de_reponse' => $optionsDeReponse->map(function ($optionDeReponse) {
                            return [
                                'label' => $optionDeReponse['libelle'],
                                'count' => $optionDeReponse['count'],
                            ];
                        }),
                    ];
                })->values(),
            ];
        });

        // Return the grouped stats as values
        return $groupedStats->values();
    }
}
