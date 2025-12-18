<?php

namespace App\Models\enquetes_de_gouvernance;

use App\Models\CritereDeGouvernance;
use App\Models\Programme;
use Exception;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\Relations\MorphToMany;
use Illuminate\Support\Facades\DB;
use SaiAshirwadInformatia\SecureIds\Models\Traits\HasSecureIds;

class PrincipeDeGouvernancePerception extends Model
{
    protected $table = 'principes_de_gouvernance_de_perception';
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
        return $this->morphMany(CategorieDePerceptionDeGouvernance::class, 'categorieable');
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
    public function questions_operationnelle($annee_exercice = null)
    {
        $questions_operationnelle = $this->hasManyThrough(
            QuestionDePerceptionDeGouvernance::class,// Final Model
            CategorieDePerceptionDeGouvernance::class,// Intermediate Model
            'categorieable_id',
            'categorieDePerceptionDeGouvernanceId',
            'id',
            'id'
        );

        if($annee_exercice){
            $questions_operationnelle = $questions_operationnelle->whereHas("formulaire_de_gouvernance", function($query) use ($annee_exercice){
                $query->where('annee_exercice', $annee_exercice);
            });
        }

        return $questions_operationnelle;
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
