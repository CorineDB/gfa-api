<?php

namespace App\Models\enquetes_de_gouvernance;

use App\Models\Programme;
use Exception;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\Relations\MorphToMany;
use Illuminate\Support\Facades\DB;
use SaiAshirwadInformatia\SecureIds\Models\Traits\HasSecureIds;

class PrincipeDeGouvernanceFactuel extends Model
{
    protected $table = 'principes_de_gouvernance_factuel';
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
        $categories_de_gouvernance = $this->morphMany(CategorieFactuelDeGouvernance::class, 'categorieable');

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
            CategorieFactuelDeGouvernance::class,// Final Model
            CategorieFactuelDeGouvernance::class,// Intermediate Model
            'categorieable_id',
            'categorieFactuelDeGouvernanceId',
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
        return $this->sous_categories_de_gouvernance($annee_exercice)->where("categorieable_type", get_class(new CritereDeGouvernanceFactuel));
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
            QuestionFactuelDeGouvernance::class,// Final Model
            CategorieFactuelDeGouvernance::class,// Intermediate Model
            'categorieable_id',
            'categorieFactuelDeGouvernanceId',
            'id',
            'id'
        );

        if($annee_exercice){
            $indicateurs_de_gouvernance = $indicateurs_de_gouvernance->whereHas("formulaire_de_gouvernance", function($query) use ($annee_exercice){
                $query->where('annee_exercice', $annee_exercice);
            });
        }

        return $indicateurs_de_gouvernance;
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
