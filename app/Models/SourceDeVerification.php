<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use SaiAshirwadInformatia\SecureIds\Models\Traits\HasSecureIds;

class SourceDeVerification extends Model
{
    protected $table = 'sources_de_verification';
    public $timestamps = true;

    use HasSecureIds, HasFactory ;

    protected $dates = ['deleted_at'];

    protected $fillable = array("intitule", "description", 'programmeId');

    protected $casts = [];

    protected static function boot()
    {
        parent::boot();
    }

    public function reponses()
    {
        return $this->hasMany(ReponseDeLaCollecte::class, 'sourceDeVerificationId');
    }

    public function programme()
    {
        return $this->belongsTo(Programme::class, 'programmeId');
    }
}
