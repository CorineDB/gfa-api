<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use SaiAshirwadInformatia\SecureIds\Models\Traits\HasSecureIds;

class Statut extends Model
{

    protected $table = 'statuts';
    public $timestamps = true;

    use HasSecureIds;

    protected $dates = ['deleted_at'];
    protected $fillable = array('etat', 'statuttable_type', 'statuttable_id');

    public function statuttable()
    {
        return $this->morphTo();
    }

}
