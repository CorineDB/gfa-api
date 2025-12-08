<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

use SaiAshirwadInformatia\SecureIds\Models\Traits\HasSecureIds;

class ArchivePlanDecaissement extends Model
{
    use HasSecureIds, HasFactory ;

    protected $table = 'archive_plan_de_decaissements';
    public $timestamps = true;

    protected $dates = ['deleted_at'];

    protected $fillable = ['trimestre', 'annee', 'pret', 'budgetNational', 'parentId', 'activiteId', 'ptabScopeId'];

    public function activite()
    {
        return $this->belongsTo(ArchiveActivite::class, 'activiteId');
    }

    public function ptabScope()
    {
        return $this->belongsTo(PtabScope::class, 'ptabScopeId');
    }

}
