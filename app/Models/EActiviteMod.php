<?php

namespace App\Models;

use Exception;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\DB;
use SaiAshirwadInformatia\SecureIds\Models\Traits\HasSecureIds;

class EActiviteMod extends Model
{

    protected $table = 'e_activite_mods';
    public $timestamps = true;

    use HasSecureIds ;

    protected $dates = ['deleted_at'];

    protected $fillable = array('description', 'modId', 'siteId', 'bailleurId', 'programmeId');

    protected static function boot() {
        parent::boot();

        static::deleted(function($commentaire) {

            DB::beginTransaction();
            try {

                $commentaire->esuiviActiviteMods()->delete();

                DB::commit();
            } catch (\Throwable $th) {
                DB::rollBack();

                throw new Exception($th->getMessage(), 1);

            }
        });
    }

    public function mod()
    {
        return $this->belongsTo(MOD::class, 'eActiviteModId');
    }

    public function site()
    {
        return $this->belongsTo(Site::class, 'siteId');
    }

    public function bailleur()
    {
        return $this->belongsTo(Bailleur::class, 'bailleurId');
    }

    public function programme()
    {
        return $this->belongsTo(Programme::class, 'eActiviteId');
    }

    public function esuiviActiviteMods()
    {
        return $this->hasMany(ESuiviActiviteMods::class, 'eSuiviActiviteModId');
    }

}
