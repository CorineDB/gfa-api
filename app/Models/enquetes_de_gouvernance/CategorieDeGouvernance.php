<?php

namespace App\Models\enquetes_de_gouvernance;

use App\Models\Programme;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use SaiAshirwadInformatia\SecureIds\Models\Traits\HasSecureIds;

class CategorieDeGouvernance extends Model
{
    protected $table = 'categories_de_gouvernance';
    public $timestamps = true;

    use HasSecureIds, HasFactory ;

    protected $dates = ['deleted_at'];

    protected $fillable = array('categorieable_id', 'categorieable_type', /* 'position', */ 'categorieDeGouvernanceId', 'formulaireDeGouvernanceId', 'programmeId');

    protected $casts = [
        //"position" => "integer"
    ];

    protected $with = ["categorieable"];

    protected static function boot()
    {
        parent::boot();
    }

    public function categories_de_gouvernance()
    {
        return $this->hasMany(CategorieDeGouvernance::class, 'categorieDeGouvernanceId');
    }

    public function sousCategoriesDeGouvernance()
    {
        return $this->hasMany(CategorieDeGouvernance::class, 'categorieDeGouvernanceId');
    }

    public function categorieDeGouvernanceParent()
    {
        return $this->belongsTo(CategorieDeGouvernance::class, 'categorieDeGouvernanceId');
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
        return $this->belongsTo(FormulaireDeGouvernance::class, 'formulaireDeGouvernanceId');
    }

    public function formulaires_de_gouvernance()
    {
        return $this->belongsToMany(FormulaireDeGouvernance::class, 'questions_de_gouvernance', 'categorieDeGouvernanceId', 'formulaireDeGouvernanceId')->wherePivotNull('deleted_at')->withPivot(['id', 'type', 'indicateurDeGouvernanceId', 'programmeId']);
    }

    public function questions_de_gouvernance($formulaireDeGouvernanceId = null, $annee_exercice = null)
    {
        $questions_de_gouvernance = $this->hasMany(QuestionDeGouvernance::class, 'categorieDeGouvernanceId');

        if($formulaireDeGouvernanceId){

            $questions_de_gouvernance = $questions_de_gouvernance->where("formulaireDeGouvernanceId", $formulaireDeGouvernanceId);
        }

        if($annee_exercice){
            $questions_de_gouvernance = $questions_de_gouvernance->whereHas("formulaire_de_gouvernance", function($query) use ($annee_exercice){
                $query->where('annee_exercice', $annee_exercice);
            });
        }

        return $questions_de_gouvernance;
    }
}
