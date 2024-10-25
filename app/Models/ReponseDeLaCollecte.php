<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use SaiAshirwadInformatia\SecureIds\Models\Traits\HasSecureIds;

class ReponseDeLaCollecte extends Model
{
    protected $table = 'reponses_de_la_collecte';
    public $timestamps = true;

    use HasSecureIds, HasFactory ;

    protected $dates = ['deleted_at'];

    protected $fillable = array("point", "type", 'sourceDeVerification', 'soumissionId', 'sourceDeVerificationId', 'questionId', 'optionDeReponseId', 'programmeId');

    protected $casts = [];

    protected static function boot()
    {
        parent::boot();
    }
    
    /**
     * Get the source de verification associated with the reponse de la collecte.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function source_de_verification()
    {
        return $this->belongsTo(SourceDeVerification::class, 'sourceDeVerificationId');
    }

    public function soumission()
    {
        return $this->belongsTo(Soumission::class, 'soumissionId');
    }

    public function option_de_reponse()
    {
        return $this->belongsTo(OptionDeReponse::class, 'optionDeReponseId');
    }

    public function question()
    {
        return $this->belongsTo(QuestionDeGouvernance::class, 'questionId');
    }

    public function programme()
    {
        return $this->belongsTo(Programme::class, 'programmeId');
    }

    public function preuves_de_verification()
    {
        return $this->morphMany(Fichier::class, "fichiertable");
    }

    public function recommandations()
    {
        return $this->morphMany(Recommandation::class, "recommandable");
    }

    public function actions_a_mener()
    {
        return $this->morphMany(ActionAMener::class, "actionable");
    }
}
