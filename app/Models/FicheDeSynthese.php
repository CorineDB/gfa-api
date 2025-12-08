<?php

namespace App\Models;

use Exception;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use SaiAshirwadInformatia\SecureIds\Models\Traits\HasSecureIds;

class FicheDeSynthese extends Model
{
    protected $table = 'fiches_de_synthese';
    public $timestamps = true;

    use HasSecureIds, HasFactory ;

    protected $dates = ['deleted_at'];

    protected $fillable = array('type', 'indice_de_gouvernance', 'resultats', 'synthese', 'evaluatedAt', 'organisationId', 'evaluationDeGouvernanceId', 'formulaireDeGouvernanceId', 'programmeId');

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

    public function formulaire_de_gouvernance()
    {
        return $this->belongsTo(FormulaireDeGouvernance::class, 'formulaireDeGouvernanceId');
    }

    public function programme()
    {
        return $this->belongsTo(Programme::class, 'programmeId');
    }

}
