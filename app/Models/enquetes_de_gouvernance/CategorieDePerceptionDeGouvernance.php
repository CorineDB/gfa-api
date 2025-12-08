<?php

namespace App\Models\enquetes_de_gouvernance;

use App\Models\Programme;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use SaiAshirwadInformatia\SecureIds\Models\Traits\HasSecureIds;

class CategorieDePerceptionDeGouvernance extends Model
{
    protected $table = 'categories_de_perception_de_gouvernance';
    public $timestamps = true;

    use HasSecureIds, HasFactory ;

    protected $dates = ['deleted_at'];

    protected $fillable = array('categorieable_id', 'categorieable_type', 'position', 'categorieDePerceptionDeGouvernanceId', 'formulaireDePerceptionId', 'programmeId');

    protected $casts = [
        "position" => "integer"
    ];

    protected $with = ["categorieable"];

    protected static function boot()
    {
        parent::boot();
    }

    public function categories_de_gouvernance()
    {
        return $this->hasMany(CategorieDePerceptionDeGouvernance::class, 'categorieDePerceptionDeGouvernanceId');
    }

    public function sousCategoriesDeGouvernance()
    {
        return $this->hasMany(CategorieDePerceptionDeGouvernance::class, 'categorieDePerceptionDeGouvernanceId');
    }

    public function categorieDeGouvernanceParent()
    {
        return $this->belongsTo(CategorieDePerceptionDeGouvernance::class, 'categorieDePerceptionDeGouvernanceId');
    }

    public function programme()
    {
        return $this->belongsTo(Programme::class, 'programmeId');
    }

    public function categorieable()
    {
        return $this->morphTo();
    }

    public function formulaire_de_gouvernance()
    {
        return $this->belongsTo(FormulaireDePerceptionDeGouvernance::class, 'formulaireDePerceptionId');
    }

    public function formulaires_de_gouvernance()
    {
        return $this->belongsToMany(FormulaireDePerceptionDeGouvernance::class, 'formulaires_de_perception_de_gouvernance', 'categorieDePerceptionDeGouvernanceId', 'formulaireDePerceptionId')->wherePivotNull('deleted_at')->withPivot(['id', 'position', 'questionOperationnelleId', 'programmeId']);
    }

    public function questions_de_gouvernance($formulaireDePerceptionId = null, $annee_exercice = null)
    {
        $questions_de_gouvernance = $this->hasMany(QuestionDePerceptionDeGouvernance::class, 'categorieDePerceptionDeGouvernanceId')
                                    ->orderBy('position','asc');

        if($formulaireDePerceptionId){

            $questions_de_gouvernance = $questions_de_gouvernance->where("formulaireDePerceptionId", $formulaireDePerceptionId);
        }

        if($annee_exercice){
            $questions_de_gouvernance = $questions_de_gouvernance->whereHas("formulaire_de_gouvernance", function($query) use ($annee_exercice){
                $query->where('annee_exercice', $annee_exercice);
            });
        }

        return $questions_de_gouvernance;
    }
}
