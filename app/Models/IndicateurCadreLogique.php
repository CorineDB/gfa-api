<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use SaiAshirwadInformatia\SecureIds\Models\Traits\HasSecureIds;

class IndicateurCadreLogique extends Model
{
    use HasSecureIds, HasFactory ;

    protected $table = "cadre_logique_indicateurs";

    protected $fillable = [
        'nom',
        'sourceDeVerification',
        'hypothese',
        'indicatable_type',
        'indicatable_id'
    ];

    public function indicatable()
    {
        return $this->morphTo();
    }
}
