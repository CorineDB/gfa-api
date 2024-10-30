<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use SaiAshirwadInformatia\SecureIds\Models\Traits\HasSecureIds;

class QuestionDeGouvernance extends Model
{
    protected $table = 'questions_de_gouvernance';
    public $timestamps = true;

    use HasSecureIds, HasFactory ;

    protected $dates = ['deleted_at'];

    protected $fillable = array("position", 'type', 'formulaireDeGouvernanceId', 'categorieDeGouvernanceId', 'indicateurDeGouvernanceId', 'programmeId');

    protected $casts = [
        "position" => "integer"
    ];

    protected $with = ["indicateur_de_gouvernance"];

    protected static function boot()
    {
        parent::boot();
    }

    public function formulaire_de_gouvernance()
    {
        return $this->belongsTo(FormulaireDeGouvernance::class, 'formulaireDeGouvernanceId');
    }

    public function categorie_de_gouvernance()
    {
        return $this->belongsTo(CategorieDeGouvernance::class, 'categorieDeGouvernanceId');
    }

    public function indicateur_de_gouvernance()
    {
        return $this->belongsTo(IndicateurDeGouvernance::class, 'indicateurDeGouvernanceId');
    }

    public function programme()
    {
        return $this->belongsTo(Programme::class, 'programmeId');
    }

    public function reponses()
    {
        return $this->hasMany(ReponseDeLaCollecte::class, 'questionId');
    }

}
