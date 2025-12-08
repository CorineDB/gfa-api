<?php

namespace App\Models;

use Exception;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\DB;
use SaiAshirwadInformatia\SecureIds\Models\Traits\HasSecureIds;

class ESuiviActiviteMod extends Model
{

    protected $table = 'e_suivi_activite_mods';
    public $timestamps = true;

    use HasSecureIds, HasFactory ;

    protected $dates = ['deleted_at'];

    protected $fillable = array('description', 'commentaire', 'niveauDeMiseEnOeuvre', 'eActiviteModId');

    public function eActiviteMods()
    {
        return $this->belongsTo(EActiviteMod::class, 'eSuiviActiviteModId');
    }

}
