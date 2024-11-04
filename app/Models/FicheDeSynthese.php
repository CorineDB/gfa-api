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

    protected $fillable = array('type', 'synthese', 'evaluatedAt', 'soumissionId', 'programmeId');


    protected $casts = ['synthese' => 'array', 'evaluatedAt' => 'datetime'];

    protected static function boot()
    {
        parent::boot();
    }

    public function soumission()
    {
        return $this->belongsTo(Soumission::class, 'soumissionId');
    }

    public function programme()
    {
        return $this->belongsTo(Programme::class, 'programmeId');
    }

}
