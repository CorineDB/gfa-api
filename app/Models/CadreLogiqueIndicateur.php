<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use SaiAshirwadInformatia\SecureIds\Models\Traits\HasSecureIds;

class CadreLogiqueIndicateur extends Model
{
    use HasSecureIds, HasFactory ;

    protected $table='cadre_logique_indicateurs';

    protected $fillable = ['nom', 'sourceDeVerification', 'hypothese', 'indicatable_id', 'indicatable_type'];

    public function indicatable()
    {
        return $this->morphTo();
    }
}
