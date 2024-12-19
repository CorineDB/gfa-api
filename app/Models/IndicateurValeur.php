<?php

namespace App\Models;

use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use SaiAshirwadInformatia\SecureIds\Models\Traits\HasSecureIds;

class IndicateurValeur extends Model
{
    protected $table = 'indicateur_valeurs';
    public $timestamps = true;

    use HasSecureIds, HasFactory ;

    protected $dates = ['deleted_at'];

    protected $fillable = array('value', 'indicateur_valueable_type', 'indicateur_valueable_id', 'indicateurValueKeyMapId', 'programmeId');

    protected static function boot()
    {
        parent::boot();
    }

    public function indicateur_valueable()
    {
        return $this->morphTo();
    }

    public function programme()
    {
        return $this->belongsTo(Programme::class, 'programmeId');
    }
}