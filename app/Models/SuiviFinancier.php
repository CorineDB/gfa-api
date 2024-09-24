<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

use SaiAshirwadInformatia\SecureIds\Models\Traits\HasSecureIds;

class SuiviFinancier extends Model
{
    use HasSecureIds, HasFactory ;

    protected $table = 'suivi_financiers';
    public $timestamps = true;

    protected $dates = ['deleted_at'];
    protected $fillable = array('consommer', 'trimestre', 'activiteId', 'programmeId', 'commentaire', 'annee', 'suivi_financierable_type', 'suivi_financierable_id', 'dateDeSuivi');

    public function activite()
    {
        return $this->belongsTo(Activite::class, 'activiteId');
    }

    public function suivi_financierable()
    {
        return $this->morphTo();
    }

}
