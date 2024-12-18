<?php

namespace App\Models;

use Exception;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\Relations\MorphToMany;
use Illuminate\Support\Facades\DB;
use SaiAshirwadInformatia\SecureIds\Models\Traits\HasSecureIds;

class PrincipeDeGouvernance extends Model
{
    protected $table = 'principes_de_gouvernance';
    public $timestamps = true;

    use HasSecureIds, HasFactory ;

    protected $dates = ['deleted_at'];

    protected $fillable = array('nom', 'description', 'programmeId');

    protected static function boot()
    {
        parent::boot();

        static::deleted(function ($principe_de_gouvernance) {

            DB::beginTransaction();
            try {

                $principe_de_gouvernance->update([
                    'nom' => time() . '::' . $principe_de_gouvernance->nom
                ]);

                DB::commit();
            } catch (\Throwable $th) {
                DB::rollBack();

                throw new Exception($th->getMessage(), 1);
            }
        });
    }
    
    /**
     * Charger la liste des indicateurs de tous les criteres de gouvernance
     */
    public function indicateurs_criteres_de_gouvernance()
    {
        return $this->hasManyThrough(
            IndicateurDeGouvernance::class,    // Final Model
            CritereDeGouvernance::class,       // Intermediate Model
            'principeDeGouvernanceId',      // Foreign key on the criteres_de_gouvernance table
            'principeable_id',       // Foreign key on the indicateurs_de_gouvernance table
            'id',                              // Local key on the principe_de_gouvernance table
            'id'                               // Local key on the criteres_de_gouvernance table
        )->where('principeable_type', CritereDeGouvernance::class);
    }

    public function indicateurs_criteres_de_gouvernance_count(){
        $this->criteres_de_gouvernance->each->count();
    }

    public function objectifs_par_principe()
    {
        return $this->belongsToMany(EvaluationDeGouvernance::class, 'evaluation_principes_de_gouvernance_objectifs', 'principeId', 'evaluationId')->wherePivotNull('deleted_at')->withPivot(['objectif_attendu', 'programmeId']);
    }

    /**
     * Renvoie le programme auquel est lié le principe de gouvernance
     * 
     * @return BelongsTo
     */
    public function programme()
    {
        return $this->belongsTo(Programme::class, 'programmeId');
    }

    /**
     * Renvoie la liste des catégories de gouvernance liées au principe de gouvernance
     * Si l'année d'exercice est fournie, seules les catégories liées  des formulaires
     * de gouvernance de l'année d'exercice sont renvoyées.
     *
     * @param int|null $annee_exercice L'année d'exercice
     * @return \Illuminate\Database\Eloquent\Relations\MorphMany
     */
    public function categories_de_gouvernance($annee_exercice = null)
    {
        $categories_de_gouvernance = $this->morphMany(CategorieDeGouvernance::class, 'categorieable');

        if($annee_exercice){
            $categories_de_gouvernance = $categories_de_gouvernance->whereHas("formulaire_de_gouvernance", function($query) use ($annee_exercice){
                $query->where('annee_exercice', $annee_exercice);
            });
        }

        return $categories_de_gouvernance;
    }


    /**
     * Renvoie la liste des sous-catégories de gouvernance liées au principe de gouvernance
     * Si l'année d'exercice est fournie, seules les sous-catégories liées  des formulaires
     * de gouvernance de l'année d'exercice sont renvoy es.
     *
     * @param int|null $annee_exercice L'année d'exercice
     * @return \Illuminate\Database\Eloquent\Relations\HasManyThrough
     */
    public function sous_categories_de_gouvernance($annee_exercice = null)
    {
        $sous_categories = $this->hasManyThrough(
            CategorieDeGouvernance::class,// Final Model
            CategorieDeGouvernance::class,// Intermediate Model
            'categorieable_id',
            'categorieDeGouvernanceId',
            'id',
            'id'
        );

        if($annee_exercice){
            $sous_categories = $sous_categories->whereHas("formulaire_de_gouvernance", function($query) use ($annee_exercice){
                $query->where('annee_exercice', $annee_exercice);
            });
        }

        return $sous_categories;
    }

    /**
     * Renvoie la liste des critères de gouvernance liés au principe de gouvernance
     * Si l'année d'exercice est fournie, seuls les critères liés  des formulaires
     * de gouvernance de l'année d'exercice sont renvoyés.
     *
     * @param int|null $annee_exercice L'année d'exercice
     * @return \Illuminate\Database\Eloquent\Relations\HasManyThrough
     */
    public function criteres_de_gouvernance($annee_exercice = null)
    {
        return $this->sous_categories_de_gouvernance($annee_exercice)->where("categorieable_type", get_class(new CritereDeGouvernance));
    }
    
    /**
     * Return the list of QuestionDeGouvernance of type "indicateur"
     * which are linked to the current CritereDeGouvernance
     * and which are linked to a FormulaireDeGouvernance of the given year
     * If the year is not given, return all the QuestionDeGouvernance of type "indicateur"
     * which are linked to the current CritereDeGouvernance
     *
     * @param int|null $annee_exercice The year of the FormulaireDeGouvernance
     * @return \Illuminate\Database\Eloquent\Relations\HasManyThrough
     */
    public function indicateurs_de_gouvernance($annee_exercice = null)
    {
        $indicateurs_de_gouvernance = $this->hasManyThrough(
            QuestionDeGouvernance::class,// Final Model
            CategorieDeGouvernance::class,// Intermediate Model
            'categorieable_id',
            'categorieDeGouvernanceId',
            'id',
            'id'
        )->where("type", "indicateur");

        if($annee_exercice){
            $indicateurs_de_gouvernance = $indicateurs_de_gouvernance->whereHas("formulaire_de_gouvernance", function($query) use ($annee_exercice){
                $query->where('annee_exercice', $annee_exercice);
            });
        }

        return $indicateurs_de_gouvernance;
    }
    
    /**
     * Return the list of QuestionDeGouvernance of type "indicateur"
     * which are linked to the current CritereDeGouvernance
     * and which are linked to a FormulaireDeGouvernance of the given year
     * If the year is not given, return all the QuestionDeGouvernance of type "indicateur"
     * which are linked to the current CritereDeGouvernance
     *
     * @param int|null $annee_exercice The year of the FormulaireDeGouvernance
     * @return \Illuminate\Database\Eloquent\Relations\HasManyThrough
     */
    public function sous_categories_de_gouvernance_indicateurs_de_gouvernance($annee_exercice = null)
    {
        // Single query using Eloquent and joins
        $query = DB::table('question_de_gouvernance as qg')
            ->join('categorie_de_gouvernance as cg', 'qg.categorieDeGouvernanceId', '=', 'cg.id')
            ->join('categorie_de_gouvernance as parent_cg', 'cg.categorieDeGouvernanceId', '=', 'parent_cg.id') // Correct parent category join
            ->where('cg.categorieable_type', '=', CritereDeGouvernance::class) // Current category is related to CritereDeGouvernance
            ->where('parent_cg.categorieable_type', '=', PrincipeDeGouvernance::class) // Parent category is related to PrincipeDeGouvernance
            ->where('qg.type', 'indicateur'); // Filter to only governance indicators
    
        // If a year is specified, add an additional join and filter
        if ($annee_exercice) {
            $query->join('formulaire_de_gouvernance as fg', 'qg.formulaireDeGouvernanceId', '=', 'fg.id')
                  ->where('fg.annee_exercice', $annee_exercice);
        }
    
        // Select the governance indicators
        $indicateurs_de_gouvernance = $query->select('qg.*');
    
        return $indicateurs_de_gouvernance;

        // Get all subcategories linked to the current PrincipeDeGouvernance or CritereDeGouvernance
        $sous_categories = $this->sous_categories_de_gouvernance($annee_exercice);
    
        // Query for governance indicators ('type' = 'indicateur') that are linked to these subcategories
        $indicateurs_de_gouvernance = QuestionDeGouvernance::whereHas('categorie_de_gouvernance', function($query) use ($sous_categories) {
            // Filter by the subcategories related to either PrincipeDeGouvernance or CritereDeGouvernance
            $query->whereIn('id', $sous_categories->pluck('id'));
        })->where('type', 'indicateur'); // Ensure we only get indicators of type 'indicateur'
    
        // If a year (annee_exercice) is provided, filter further by it
        if ($annee_exercice) {
            $indicateurs_de_gouvernance->whereHas('formulaires_de_gouvernance', function($query) use ($annee_exercice) {
                $query->where('annee_exercice', $annee_exercice);
            });
        }
    
        // Return the indicators
        return $indicateurs_de_gouvernance;
        
    }

    /**
     * Renvoie la liste des QuestionDeGouvernance de type "indicateur"
     * qui sont liés  des CritereDeGouvernance liés  des sous-catégories
     * de gouvernance liées au PrincipeDeGouvernance.
     *
     * Si l'année d'exercice est fournie, seuls les indicateurs de gouvernance
     * liés  des formulaires de gouvernance de l'année d'exercice sont renvoyés.
     *
     * @param int|null $annee_exercice L'année d'exercice
     * @return \Illuminate\Database\Eloquent\Relations\HasManyThrough
     */
    public function indicateurs_de_gouvernance_des_sous_categories_de_gouvernance($annee_exercice = null)
    {
        // Eloquent query to get all governance indicators
        $query = QuestionDeGouvernance::whereHas('categorie', function ($query) {
            // Child categories are linked to CritereDeGouvernance
            $query->where('categorieable_type', CritereDeGouvernance::class)
                  ->whereHas('categorieDeGouvernanceParent', function ($query) {
                      // Parent categories are linked to PrincipeDeGouvernance
                      $query->where('categorieable_type', PrincipeDeGouvernance::class);
                  });
        })->where('type', 'indicateur'); // Only governance indicators
    
        // If a year is specified, add an additional filter for the year
        if ($annee_exercice) {
            $query->whereHas('formulaire', function ($query) use ($annee_exercice) {
                $query->where('annee_exercice', $annee_exercice);
            });
        }
    
        return $query;
    }

    /**
     * Get all of the actions a mener for the principe.
     */
    public function actions_a_mener(): MorphToMany
    {
        return $this->morphToMany(ActionAMener::class, 'actionable');
    }

    /**
     * Get all of the recommandations for the principe.
     */
    public function recommandations(): MorphMany
    {
        return $this->morphMany(Recommandation::class, 'recommandationable');
    }
}
