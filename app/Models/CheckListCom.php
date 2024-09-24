<?php

namespace App\Models;

use Exception;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\DB;
use SaiAshirwadInformatia\SecureIds\Models\Traits\HasSecureIds;

class CheckListCom extends Model
{

    protected $table = 'check_list_com';
    public $timestamps = true;

    use HasSecureIds ;

    protected $dates = ['deleted_at'];

    protected $fillable = ['nom', 'code', 'uniteId', 'ongComId'];

    protected static function boot() {
        parent::boot();

        static::deleted(function($checkList) {
            DB::beginTransaction();
            try {

                $checkList->fichiers()->delete();
                $checkList->suivisCheckListCom()->delete();

                DB::commit();
            } catch (\Throwable $th) {
                DB::rollBack();

                throw new Exception($th->getMessage(), 1);

            }
        });
    }

    public function ongComs()
    {
        return $this->belongsTo(OngCom::class, 'ongComId');
    }

    public function suivisCheckListCom()
    {
        return $this->hasMany(SuiviCheckListCom::class, 'checkListComId');
    }

    public function unitee()
    {
        return $this->belongsTo(Unitee::class, 'uniteeId');
    }

    public function fichiers()
    {
        return $this->morphMany(Fichier::class, 'fichiertable');
    }

}
