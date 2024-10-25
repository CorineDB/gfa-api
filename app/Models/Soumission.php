<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use SaiAshirwadInformatia\SecureIds\Models\Traits\HasSecureIds;

class Soumission extends Model
{
    protected $table = 'soumissions';
    public $timestamps = true;

    use HasSecureIds, HasFactory ;

    protected $dates = ['deleted_at'];

    protected $fillable = array('type', 'commentaire', 'submitted_at', 'statut', 'comite_members', 'submittedBy', 'evaluationId', 'formulaireDeGouvernanceId', 'organisationId', 'programmeId');

    protected $casts = [
        "comite_members" => "json",
        "statut" => "boolean",
        "submitted_at" => "datetime"
    ];

    protected $with = [];

    protected static function boot()
    {
        parent::boot();
    }
    
    public function evaluation_de_gouvernance()
    {
        return $this->belongsTo(EvaluationDeGouvernance::class, 'evaluationId');
    }

    public function formulaireDeGouvernance()
    {
        return $this->belongsTo(FormulaireDeGouvernance::class, 'formulaireDeGouvernanceId');
    }

    public function authoredBy()
    {
        return $this->belongsTo(User::class, 'submittedBy');
    }

    public function organisation()
    {
        return $this->belongsTo(Organisation::class, 'organisationId');
    }

    public function programme()
    {
        return $this->belongsTo(Programme::class, 'programmeId');
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
