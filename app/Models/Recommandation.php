<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use SaiAshirwadInformatia\SecureIds\Models\Traits\HasSecureIds;

class Recommandation extends Model
{
    protected $table = 'recommandations';
    public $timestamps = true;

    use HasSecureIds, HasFactory ;

    protected $dates = ['deleted_at'];

    protected $fillable = array("recommandation", "recommandationable_id", "recommandationable_type", 'programmeId');

    protected $casts = [];

    protected static function boot()
    {
        parent::boot();
    }

    public function recommandationable()
    {
        return $this->morphTo();
    }

    public function programme()
    {
        return $this->belongsTo(Programme::class, 'programmeId');
    }
}
