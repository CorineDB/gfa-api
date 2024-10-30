<?php

namespace App\Models;

use App\Http\Resources\gouvernance\OptionsDeReponseResource;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use SaiAshirwadInformatia\SecureIds\Models\Traits\HasSecureIds;

class FormulaireDeGouvernance extends Model
{
    protected $table = 'formulaires_de_gouvernance';
    
    public $timestamps = true;

    use HasSecureIds, HasFactory ;

    protected $dates = ['deleted_at'];

    protected $fillable = array('libelle', 'description', 'type', 'lien', 'created_by', 'programmeId', 'annee_exercice');

    protected $casts = [];

    protected $with = [];

    protected static function boot()
    {
        parent::boot();
    }

    public function categories_de_gouvernance()
    {
        return $this->hasMany(CategorieDeGouvernance::class, 'formulaireDeGouvernanceId')->whereNull('categorieDeGouvernanceId');
    }

    public function categorie_de_gouvernance()
    {
        return $this->belongsToMany(CategorieDeGouvernance::class, 'questions_de_gouvernance', 'formulaireDeGouvernanceId', 'categorieDeGouvernanceId')->wherePivotNull('deleted_at')->withPivot(['id', 'type', 'indicateurDeGouvernanceId', 'programmeId']);
    }

    public function questions_de_gouvernance($annee_exercice = null)
    {
        return $this->hasMany(QuestionDeGouvernance::class, 'formulaireDeGouvernanceId');
    }

    public function options_de_reponse()
    {
        return $this->belongsToMany(OptionDeReponse::class,'formulaire_options_de_reponse', 'formulaireDeGouvernanceId', 'optionId')->wherePivotNull('deleted_at')->withPivot(["id", "point"]);
    }

    public function programme()
    {
        return $this->belongsTo(Programme::class, 'programmeId');
    }

    public function createdBy()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function evaluations_de_gouvernance()
    {
        return $this->belongsToMany(EvaluationDeGouvernance::class,'evaluation_formulaires_de_gouvernance', 'formulaireDeGouvernanceId', 'evaluationDeGouvernanceId')->wherePivotNull('deleted_at');
    }
}