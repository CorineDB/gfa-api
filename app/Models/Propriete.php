<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

use SaiAshirwadInformatia\SecureIds\Models\Traits\HasSecureIds;

class Propriete extends Model
{

    protected $table = 'proprietes';
    public $timestamps = true;

    use HasSecureIds, HasFactory ;

    protected $dates = ['deleted_at'];

    protected $fillable = array('nom', 'longitude', 'latitude', 'montant', 'dateDePaiement', 'sinistreId');

    public function sinistre()
    {
        return $this->belongsTo(Sinistre::class, 'sinistreId');
    }

    public function payes()
    {
        return $this->hasMany(Paye::class, 'proprieteId')->first();
    }

    public function nouvellePropriete()
    {
        return $this->hasOne(NouvellePropriete::class, 'proprieteId');
    }

}
