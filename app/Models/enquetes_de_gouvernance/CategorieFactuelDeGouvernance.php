<?php

namespace App\Models\enquetes_de_gouvernance;

use App\Models\Programme;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use SaiAshirwadInformatia\SecureIds\Models\Traits\HasSecureIds;

class CategorieFactuelDeGouvernance extends Model
{
    protected $table = 'categories_factuel_de_gouvernance';
    public $timestamps = true;

    use HasSecureIds, HasFactory ;

    protected $dates = ['deleted_at'];

    protected $fillable = array('categorieable_id', 'categorieable_type', 'position', 'categorieFactuelDeGouvernanceId', 'formulaireFactuelId', 'programmeId');

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
        return $this->hasMany(CategorieFactuelDeGouvernance::class, 'categorieFactuelDeGouvernanceId');
    }

    public function sousCategoriesDeGouvernance()
    {
        return $this->hasMany(CategorieFactuelDeGouvernance::class, 'categorieFactuelDeGouvernanceId');
    }

    public function categorieDeGouvernanceParent()
    {
        return $this->belongsTo(CategorieFactuelDeGouvernance::class, 'categorieFactuelDeGouvernanceId');
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
        return $this->belongsTo(FormulaireFactuelDeGouvernance::class, 'formulaireFactuelId');
    }

    public function formulaires_de_gouvernance()
    {
        return $this->belongsToMany(FormulaireFactuelDeGouvernance::class, 'questions_factuel_de_gouvernance', 'categorieFactuelDeGouvernanceId', 'formulaireFactuelId')->wherePivotNull('deleted_at')->withPivot(['id', 'position', 'indicateurFactuelDeGouvernanceId', 'programmeId']);
    }

    public function questions_de_gouvernance($formulaireFactuelId = null, $annee_exercice = null)
    {
        $questions_de_gouvernance = $this->hasMany(QuestionFactuelDeGouvernance::class, 'categorieFactuelDeGouvernanceId')
                                        ->orderBy('position','asc');

        if($formulaireFactuelId){

            $questions_de_gouvernance = $questions_de_gouvernance->where("formulaireFactuelId", $formulaireFactuelId);
        }

        if($annee_exercice){
            $questions_de_gouvernance = $questions_de_gouvernance->whereHas("formulaire_de_gouvernance", function($query) use ($annee_exercice){
                $query->where('annee_exercice', $annee_exercice);
            });
        }

        return $questions_de_gouvernance;
    }
}
