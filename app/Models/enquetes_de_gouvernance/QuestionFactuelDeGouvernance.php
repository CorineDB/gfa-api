<?php

namespace App\Models\enquetes_de_gouvernance;

use App\Models\Programme;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use SaiAshirwadInformatia\SecureIds\Models\Traits\HasSecureIds;

class QuestionFactuelDeGouvernance extends Model
{
    protected $table = 'questions_factuel_de_gouvernance';
    public $timestamps = true;

    use HasSecureIds, HasFactory ;

    protected $dates = ['deleted_at'];

    protected $fillable = array("position", 'formulaireFactuelId', 'categorieFactuelDeGouvernanceId', 'indicateurFactuelDeGouvernanceId', 'programmeId');

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
        return $this->belongsTo(FormulaireFactuelDeGouvernance::class, 'formulaireFactuelId');
    }

    public function categorie_de_gouvernance()
    {
        return $this->belongsTo(CategorieFactuelDeGouvernance::class, 'categorieFactuelDeGouvernanceId');
    }

    public function indicateur_de_gouvernance()
    {
        return $this->belongsTo(IndicateurDeGouvernanceFactuel::class, 'indicateurFactuelDeGouvernanceId');
    }

    public function programme()
    {
        return $this->belongsTo(Programme::class, 'programmeId');
    }

    public function reponses()
    {
        return $this->hasMany(ReponseDeLaCollecteFactuel::class, 'questionId');
    }

    public function reponse($soumissionId)
    {
        return $this->hasOne(ReponseDeLaCollecteFactuel::class, 'questionId')->where("soumissionId", $soumissionId);
    }

}
