<?php

namespace App\Models;

use Exception;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use SaiAshirwadInformatia\SecureIds\Models\Traits\HasSecureIds;

class EvaluationDeGouvernance extends Model
{
    protected $table = 'evaluations_de_gouvernance';
    public $timestamps = true;

    use HasSecureIds, HasFactory ;

    protected $default = ['objectif_attendu'=>0];

    protected $dates = ['deleted_at'];

    protected $fillable = array('intitule', 'objectif_attendu', 'annee_exercice', 'description', 'debut', 'fin', 'statut', 'programmeId');

    protected $casts = ['statut'  => 'integer', 'debut'  => 'datetime', 'fin'  => 'datetime', 'annee_exercice' => 'integer', 'objectif_attendu' => 'double'];

    protected $appends = ['pourcentage_evolution', 'pourcentage_evolution_des_soumissions_factuel', 'pourcentage_evolution_des_soumissions_de_perception', 'total_soumissions_factuel', 'total_soumissions_de_perception', 'total_soumissions_factuel_non_demarrer', 'total_soumissions_de_perception_non_demarrer', 'total_soumissions_factuel_terminer', 'total_soumissions_de_perception_terminer', 'total_participants_evaluation_factuel', 'total_participants_evaluation_de_perception', 'options_de_reponse_stats', 'organisations_ranking'];

    protected static function boot()
    {
        parent::boot();

        static::deleting(function ($evaluation_de_gouvernance) {

            DB::beginTransaction();
            try {

                if (($evaluation_de_gouvernance->soumissions->count() > 0) || ($evaluation_de_gouvernance->statut > -1)) {
                    // Prevent deletion by throwing an exception
                    throw new Exception("Cannot delete because there are associated resource.");
                }
                
            } catch (\Throwable $th) {
                DB::rollBack();

                throw new Exception($th->getMessage(), 1);
            }
        });

        static::deleted(function ($evaluation_de_gouvernance) {

            DB::beginTransaction();
            try {

                $evaluation_de_gouvernance->recommandations()->delete();
                $evaluation_de_gouvernance->actions_a_mener()->delete();
                $evaluation_de_gouvernance->fiches_de_synthese()->delete();
                $evaluation_de_gouvernance->soumissions()->delete();
                $evaluation_de_gouvernance->formulaires_de_gouvernance()->delete();
                $evaluation_de_gouvernance->organisations()->delete();

                DB::commit();
            } catch (\Throwable $th) {
                DB::rollBack();

                throw new Exception($th->getMessage(), 1);
            }
        });
    }

    public function soumissions()
    {
        if(auth()->check()){
            return $this->hasMany(Soumission::class, 'evaluationId')->when((optional(auth()->user())->type === 'organisation' || get_class(auth()->user()->profilable) == Organisation::class), function($query) {
                $organisationId = optional(auth()->user()->profilable)->id;
    
                // If the organisationId is null, return an empty collection
                if (is_null($organisationId)) {
                    $query->whereRaw('1 = 0'); // Ensures no results are returned
                } else {
                    $query->where('organisationId', $organisationId);
                }
            });
        }
        else{
            return $this->hasMany(Soumission::class, 'evaluationId');
        }
    }

    public function soumissionsDePerception()
    {
        if(auth()->check()){
            return $this->hasMany(Soumission::class, 'evaluationId')->where("type", 'perception')->when((optional(auth()->user())->type === 'organisation' || get_class(auth()->user()->profilable) == Organisation::class), function($query) {
                $organisationId = optional(auth()->user()->profilable)->id;

                // If the organisationId is null, return an empty collection
                if (is_null($organisationId)) {
                    $query->whereRaw('1 = 0'); // Ensures no results are returned
                } else {
                    $query->where('organisationId', $organisationId);
                }
            });
        }else{
            return $this->hasMany(Soumission::class, 'evaluationId')->where("type", 'perception');
        }
    }

    public function soumissionsFactuel()
    {
        return $this->hasMany(Soumission::class, 'evaluationId')->where("type", 'factuel')->when((optional(auth()->user())->type === 'organisation' || get_class(auth()->user()->profilable) == Organisation::class), function($query) {
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
        $soumissionFactuel = $this->hasOne(Soumission::class, 'evaluationId')->where("type", 'factuel')->when((optional(auth()->user())->type === 'organisation' || get_class(auth()->user()->profilable) == Organisation::class), function($query) {
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
        $soumissionDePerception = $this->hasMany(Soumission::class, 'evaluationId')->where("type", 'perception');
        
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
        $organisations = $this->belongsToMany(Organisation::class,'evaluation_organisations', 'evaluationDeGouvernanceId', 'organisationId')->wherePivotNull('deleted_at')->withPivot(['id', 'nbreParticipants', 'participants', 'token'])->whereHas('user.profilable');

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
    public function organisations_user(){
        return User::whereHas('profilable', function ($query) {
            $query->whereIn('profilable_id', $this->organisations->pluck('id'))->where('profilable_type', Organisation::class);
        })->get();
    }

    public function formulaires_de_gouvernance()
    {
        return $this->belongsToMany(FormulaireDeGouvernance::class,'evaluation_formulaires_de_gouvernance', 'evaluationDeGouvernanceId', 'formulaireDeGouvernanceId')->wherePivotNull('deleted_at');
    }

    public function formulaire_factuel_de_gouvernance()
    {
        return $this->belongsToMany(FormulaireDeGouvernance::class,'evaluation_formulaires_de_gouvernance', 'evaluationDeGouvernanceId', 'formulaireDeGouvernanceId')->wherePivotNull('deleted_at')->where("type", 'factuel')->first();
    }

    public function formulaire_de_perception_de_gouvernance()
    {
        return $this->belongsToMany(FormulaireDeGouvernance::class,'evaluation_formulaires_de_gouvernance', 'evaluationDeGouvernanceId', 'formulaireDeGouvernanceId')->wherePivotNull('deleted_at')->where("type", 'perception')->first();
    }

    public function principes_de_gouvernance()
    {
        return $this->formulaire_de_perception_de_gouvernance()->principes_de_gouvernance();
    }

    public function objectifs_par_principe()
    {
        return $this->belongsToMany(PrincipeDeGouvernance::class,'evaluation_principes_de_gouvernance_objectifs', 'evaluationId', 'principeId')->wherePivotNull('deleted_at')->withPivot(['objectif_attendu', 'programmeId']);
    }

    public function recommandations()
    {
        return $this->hasMany(Recommandation::class, 'evaluationId');
        return $this->morphMany(Recommandation::class, "recommandationable");
    }

    public function actions_a_mener()
    {
        return $this->hasMany(ActionAMener::class, 'evaluationId');
        return $this->morphMany(ActionAMener::class, "actionable");
    }

    public function evaluation()
    {
        return $this->belongsTo(EvaluationDeGouvernance::class, 'evaluationId');
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
        $fiches_de_synthese = $this->hasMany(FicheDeSynthese::class, 'evaluationDeGouvernanceId')->where("type","factuel");

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

    public function failedProfilesDeGouvernance(?int $organisationId = null, ?int $evaluationOrganisationId = null, ?float $threshold  = 0.5){
       
        // Start the query by calling the profiles method
        $profile = $this->profiles();

        $threshold = $this->objectif_attendu ? $this->objectif_attendu : $threshold;

        // Apply filtering for 'indice_synthetique' under the dynamic threshold using MySQL JSON functions
        return $profile->whereRaw('CAST(JSON_UNQUOTE(JSON_EXTRACT(resultat_synthetique, "$[*].indice_synthetique")) AS UNSIGNED) < ?', [$threshold]);
    }

    public function getPourcentageEvolutionAttribute()
    {
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
    
        // Return the difference
        return $totalOrganisations - $totalSoumissionsFactuel;
    }

    public function getTotalSoumissionsDePerceptionNonDemarrerAttribute()
    {
        return $this->total_participants_evaluation_de_perception - $this->soumissionsDePerception()->count();
    }

    public function getTotalSoumissionsFactuelTerminerAttribute()
    {
        return $this->soumissionsFactuel()->where('statut', true)->count();
    }

    public function getTotalSoumissionsDePerceptionTerminerAttribute()
    {
        return $this->soumissionsDePerception()->where('statut', true)->count();
    }

    public function getTotalParticipantsEvaluationFactuelAttribute(){
        // Sum the 'nbreParticipants' attribute from the pivot table
        if((auth()->user()->type == 'organisation') || get_class(optional(auth()->user()->profilable)) == Organisation::class){
            if(auth()->user()->profilable){
                return $this->organisations(optional(auth()->user()->profilable)->id)->count() ?? 0;
            }
            else{ return 0; }
        }
        elseif((auth()->user()->type == 'unitee-de-gestion') || get_class(optional(auth()->user()->profilable)) == UniteeDeGestion::class){
            return $this->organisations()->count();
        }
        else{ return 0; }
        
    }

    public function getTotalParticipantsEvaluationDePerceptionAttribute(){

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
        $ranking = $this->organisations->map(function($organisation){
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
                'pourcentage_evolution' => $organisation->getPourcentageEvolutionAttribute($this->id),
            ];
        });
    
        // Sort organizations by completion rate (descending)
        return $ranking->sortByDesc('pourcentage_evolution')->values();
    }

    public function getOptionsDeReponseStatsAttribute(){

        // Get all soumission IDs
        $soumissionIds = $this->soumissionsDePerception->pluck("id");
        
        // Get all options (options_de_reponse) and their IDs
        $options = $this->formulaire_de_perception_de_gouvernance()->options_de_reponse;
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
                'intitule' => $organisation->sigle." - ".$organisation->user->nom,
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
    
}
