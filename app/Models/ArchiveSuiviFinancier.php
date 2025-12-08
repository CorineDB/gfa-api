<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

use SaiAshirwadInformatia\SecureIds\Models\Traits\HasSecureIds;

class ArchiveSuiviFinancier extends Model
{
    use HasSecureIds, HasFactory ;

    protected $table = 'archive_suivi_financiers';
    public $timestamps = true;

    protected $dates = ['deleted_at'];
    protected $fillable = array('consommer', 'trimestre', 'parentId', 'activiteId', 'programmeId', 'commentaire', 'annee', 'suivi_financierable_type', 'suivi_financierable_id', 'ptabScopeId');

    public function activite()
    {
        return $this->belongsTo(ArchiveActivite::class, 'activiteId');
    }

    public function ptabScope()
    {
        return $this->belongsTo(PtabScope::class, 'ptabScopeId');
    }

    public function suivi_financierable()
    {
        return $this->morphTo();
    }

}
