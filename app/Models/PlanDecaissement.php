<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

use SaiAshirwadInformatia\SecureIds\Models\Traits\HasSecureIds;

class PlanDecaissement extends Model
{
    use HasSecureIds, HasFactory ;

    protected $table = 'plan_de_decaissements';
    public $timestamps = true;

    protected $dates = ['deleted_at'];

    protected $fillable = ['trimestre', 'annee', 'pret', 'budgetNational', 'activiteId', 'programmeId'];

    public function activite()
    {
        return $this->belongsTo(Activite::class, 'activiteId');
    }
    
    public function programme()
    {
        return $this->belongsTo(Programme::class, 'programmeId');
    }

}
