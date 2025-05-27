<?php

namespace App\Models\enquetes_de_gouvernance;

use App\Models\Organisation;
use App\Models\Programme;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use SaiAshirwadInformatia\SecureIds\Models\Traits\HasSecureIds;

class ProfileDeGouvernance extends Model
{
    protected $table = 'profiles_de_gouvernance';
    public $timestamps = true;

    use HasSecureIds, HasFactory ;

    protected $dates = ['deleted_at'];

    protected $fillable = array('resultat_synthetique', 'organisationId', 'evaluationDeGouvernanceId', 'evaluationOrganisationId', 'programmeId');

    protected $casts = ['resultat_synthetique' => 'array'];

    protected $default = ['resultat_synthetique' => []];

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

    public function programme()
    {
        return $this->belongsTo(Programme::class, 'programmeId');
    }

}
