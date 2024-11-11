<?php

namespace App\Models;

use Exception;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use SaiAshirwadInformatia\SecureIds\Models\Traits\HasSecureIds;

class ReponseDeLaCollecte extends Model
{
    protected $table = 'reponses_de_la_collecte';
    public $timestamps = true;

    use HasSecureIds, HasFactory ;

    protected $dates = ['deleted_at'];

    protected $fillable = array("point", "type", 'sourceDeVerification', 'soumissionId', 'sourceDeVerificationId', 'questionId', 'optionDeReponseId', 'programmeId');

    protected $casts = [
        "point" => 'float'
    ];

    protected static function boot()
    {
        parent::boot();

        static::deleted(function ($reponse_de_la_collecte) {

            DB::beginTransaction();
            try {
                $reponse_de_la_collecte->actions_a_mener()->delete();
                $reponse_de_la_collecte->recommandations()->delete();
                $reponse_de_la_collecte->preuves_de_verification()->delete();

                DB::commit();
            } catch (\Throwable $th) {
                DB::rollBack();

                throw new Exception($th->getMessage(), 1);
            }
        });
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
        return $this->morphMany(Recommandation::class, "recommandationable");
    }

    public function actions_a_mener()
    {
        return $this->morphMany(ActionAMener::class, "actionable");
    }
}
