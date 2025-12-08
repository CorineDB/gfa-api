<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

use SaiAshirwadInformatia\SecureIds\Models\Traits\HasSecureIds;

class Paye extends Model
{

    protected $table = 'payes';
    public $timestamps = true;

    use HasSecureIds, HasFactory ;

    protected $dates = ['deleted_at'];
    protected $fillable = array('montant', 'proprieteId', 'modeDePaiement');

    public function propriete()
    {
        return $this->belongsTo(Propriete::class, 'proprieteId');
    }

}
