<?php

namespace App\Models\enquetes_de_gouvernance;

use App\Models\Programme;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use SaiAshirwadInformatia\SecureIds\Models\Traits\HasSecureIds;

class QuestionDePerceptionDeGouvernance extends Model
{
    protected $table = 'questions_de_perception_de_gouvernance';
    public $timestamps = true;

    use HasSecureIds, HasFactory ;

    protected $dates = ['deleted_at'];

    protected $fillable = array("position", 'formulaireDePerceptionId', 'categorieDePerceptionDeGouvernanceId', 'questionOperationnelleId', 'programmeId');

    protected $casts = [
        "position" => "integer"
    ];

    protected $with = ["question_operationnelle"];

    protected static function boot()
    {
        parent::boot();
    }

    public function formulaire_de_gouvernance()
    {
        return $this->belongsTo(FormulaireDePerceptionDeGouvernance::class, 'formulaireDePerceptionId');
    }

    public function categorie_de_gouvernance()
    {
        return $this->belongsTo(CategorieDePerceptionDeGouvernance::class, 'categorieDePerceptionDeGouvernanceId');
    }

    public function question_operationnelle()
    {
        return $this->belongsTo(QuestionOperationnelle::class, 'questionOperationnelleId');
    }

    public function programme()
    {
        return $this->belongsTo(Programme::class, 'programmeId');
    }

    public function reponses()
    {
        return $this->hasMany(ReponseDeLaCollecteDePerception::class, 'questionId');
    }

    public function reponse($soumissionId)
    {
        return $this->hasOne(ReponseDeLaCollecteDePerception::class, 'questionId')->where("soumissionId", $soumissionId);
    }

}
