<?php

namespace App\Models;

use Exception;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use SaiAshirwadInformatia\SecureIds\Models\Traits\HasSecureIds;

class EvaluationDeGouvernance extends Model
{
    protected $table = 'evaluations_de_gouvernance';
    public $timestamps = true;

    use HasSecureIds, HasFactory ;

    protected $dates = ['deleted_at'];

    protected $fillable = array('intitule', 'objectif_attendu', 'annee_exercice', 'description', 'debut', 'fin', 'statut', 'programmeId');

    protected $casts = ['statut'  => 'integer', 'debut'  => 'datetime', 'fin'  => 'datetime', 'annee_exercice' => 'integer', 'objectif_attendu' => 'integer'];

    protected static function boot()
    {
        parent::boot();

        static::deleted(function ($evaluation_de_gouvernance) {

            DB::beginTransaction();
            try {

                $evaluation_de_gouvernance->organisations()->delete();
                $evaluation_de_gouvernance->soumissions()->delete();
                $evaluation_de_gouvernance->recommandations()->delete();
                $evaluation_de_gouvernance->actions_a_mener()->delete();
                $evaluation_de_gouvernance->formulaires_de_gouvernance()->delete();

                DB::commit();
            } catch (\Throwable $th) {
                DB::rollBack();

                throw new Exception($th->getMessage(), 1);
            }
        });
    }

    public function soumissions()
    {
        return $this->hasMany(Soumission::class, 'evaluationId');
    }

    public function programme()
    {
        return $this->belongsTo(Programme::class, 'programmeId');
    }

    public function organisations()
    {
        return $this->belongsToMany(Organisation::class,'evaluation_organisations', 'evaluationDeGouvernanceId', 'organisationId')->wherePivotNull('deleted_at')->withPivot(['id', 'nbreParticipants'])->whereHas('user.profilable');
    }

    public function formulaires_de_gouvernance()
    {
        return $this->belongsToMany(FormulaireDeGouvernance::class,'evaluation_formulaires_de_gouvernance', 'evaluationDeGouvernanceId', 'formulaireDeGouvernanceId')->wherePivotNull('deleted_at');
    }

    public function recommandations()
    {
        return $this->morphMany(Recommandation::class, "recommandationable");
    }

    public function actions_a_mener()
    {
        return $this->morphMany(ActionAMener::class, "actionable");
    }

    public function fiches_de_synthese()
    {
        return $this->hasManyThrough(
            FicheDeSynthese::class,
            Soumission::class,
            'evaluationId',
            'soumissionId',
            'id',
            'id'
        );
    }
}
