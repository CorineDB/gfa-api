<?php

namespace App\Models\enquetes_de_gouvernance;

use App\Models\Commentaire;
use App\Models\Fichier;
use App\Models\Organisation;
use App\Models\Programme;
use Exception;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphToMany;
use Illuminate\Support\Facades\DB;
use SaiAshirwadInformatia\SecureIds\Models\Traits\HasSecureIds;

class ActionAMener extends Model
{
    protected $table = 'actions_a_mener';
    public $timestamps = true;

    use HasSecureIds, HasFactory ;

    protected $dates = ['deleted_at'];

    protected $fillable = array("action", "statut", "has_upload_preuves", "est_valider", "validated_at", "start_at", "end_at", "actionable_id", "actionable_type","organisationId", 'evaluationId', 'programmeId');

    protected $casts = [
        "start_at" => "datetime",
        "end_at" => "datetime",
        "statut" => "integer",
        "est_valider" =>"boolean",
        "validated_at" => "datetime",
        "has_upload_preuves" => "boolean"
    ];

    protected static function boot()
    {
        parent::boot();

        static::deleting(function ($action_a_mener) {

            DB::beginTransaction();
            try {

                $action_a_mener->preuves_de_verification()->delete();

                DB::commit();
            } catch (\Throwable $th) {
                DB::rollBack();

                throw new Exception($th->getMessage(), 1);
            }
        });
    }

    public function actionable()
    {
        return $this->morphTo();
    }

    public function preuves_de_verification()
    {
        return $this->morphMany(Fichier::class, "fichiertable");
    }

    public function commentaires()
    {
        return $this->morphMany(Commentaire::class, 'commentable');
    }

    public function programme()
    {
        return $this->belongsTo(Programme::class, 'programmeId');
    }

    public function evaluation()
    {
        return $this->belongsTo(EvaluationDeGouvernance::class, 'evaluationId');
    }

    public function organisation()
    {
        return $this->belongsTo(Organisation::class, 'organisationId');
    }

    /**
     * Get all of the indicateurs that are assigned this site.
     */
    public function indicateurs_factuel_de_gouvernance(): MorphToMany
    {
        return $this->morphedByMany(IndicateurDeGouvernanceFactuel::class, 'actionable');
    }

    /**
     * Get all of the indicateurs that are assigned this site.
     */
    public function questions_operationnelle(): MorphToMany
    {
        return $this->morphedByMany(QuestionOperationnelle::class, 'actionable');
    }

    /**
     * Get all of the principes_de_gouvernance that are assigned this site.
     */
    public function principes_factuel_de_gouvernance(): MorphToMany
    {
        return $this->morphedByMany(PrincipeDeGouvernanceFactuel::class, 'actionable');
    }

    /**
     * Get all of the principes_de_gouvernance that are assigned this site.
     */
    public function principes_de_perception_de_gouvernance(): MorphToMany
    {
        return $this->morphedByMany(PrincipeDeGouvernancePerception::class, 'actionable');
    }
}
