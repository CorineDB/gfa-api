<?php

namespace App\Models\enquetes_de_gouvernance;

use App\Models\Organisation;
use App\Models\Programme;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use SaiAshirwadInformatia\SecureIds\Models\Traits\HasSecureIds;

class FicheDeSynthese extends Model
{
    protected $table = 'fiches_de_synthese';
    public $timestamps = true;

    use HasSecureIds, HasFactory ;

    protected $dates = ['deleted_at'];

    protected $fillable = array('type', 'indice_de_gouvernance', 'resultats', 'synthese', 'evaluatedAt', 'organisationId', 'evaluationDeGouvernanceId', 'formulaireDeGouvernance_id', 'formulaireDeGouvernance_type', 'programmeId');

    protected $casts = ['resultats' => 'array', 'synthese' => 'array', 'indice_de_gouvernance' => 'float', 'evaluatedAt' => 'datetime'];

    protected $default = ['resultats' => [], 'synthese' => []];

    protected static function boot()
    {
        parent::boot();
    }

    public function organisation()
    {
        return $this->belongsTo(Organisation::class, 'organisationId');
    }

    public function evaluation_de_gouvernance()
    {
        return $this->belongsTo(EvaluationDeGouvernance::class, 'evaluationDeGouvernanceId');
    }
/*
    public function formulaire_de_gouvernance()
    {
        return $this->belongsTo(FormulaireDeGouvernance::class, 'formulaireDeGouvernance_id');
    } */

    public function programme()
    {
        return $this->belongsTo(Programme::class, 'programmeId');
    }

    public function formulaireDeGouvernance()
    {
        return $this->morphTo();
    }

}
