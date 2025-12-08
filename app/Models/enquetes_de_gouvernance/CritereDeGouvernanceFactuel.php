<?php

namespace App\Models\enquetes_de_gouvernance;

use App\Models\Programme;
use Exception;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use SaiAshirwadInformatia\SecureIds\Models\Traits\HasSecureIds;

class CritereDeGouvernanceFactuel extends Model
{
    protected $table = 'criteres_de_gouvernance_factuel';
    public $timestamps = true;

    use HasSecureIds, HasFactory ;

    protected $dates = ['deleted_at'];

    protected $fillable = array('nom', 'description', 'programmeId');

    protected static function boot()
    {
        parent::boot();

        static::deleted(function ($critere_de_gouvernance) {

            DB::beginTransaction();
            try {

                $critere_de_gouvernance->update([
                    'nom' => time() . '::' . $critere_de_gouvernance->nom
                ]);

                DB::commit();
            } catch (\Throwable $th) {
                DB::rollBack();

                throw new Exception($th->getMessage(), 1);
            }
        });
    }

    public function programme()
    {
        return $this->belongsTo(Programme::class, 'programmeId');
    }

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

    // Relationship to parent categories through child categories
    public function parentCategories($annee_exercice = null)
    {
        $query = $this->hasManyThrough(
            CategorieFactuelDeGouvernance::class,
            CategorieFactuelDeGouvernance::class,
            'categorieFactuelDeGouvernanceId', // Foreign key on child categories
            'id', // Foreign key on parent categories
            'id',
            'categorieFactuelDeGouvernanceId'
        );

        // Optionally filter by 'annee_exercice'
        if ($annee_exercice) {
            $query->whereHas('formulaire_de_gouvernance', function ($query) use ($annee_exercice) {
                $query->where('annee_exercice', $annee_exercice);
            });
        }

        return $query;
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
}