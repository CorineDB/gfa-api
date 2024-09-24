<?php

namespace App\Models;

use Exception;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use SaiAshirwadInformatia\SecureIds\Models\Traits\HasSecureIds;

class PrincipeDeGouvernance extends Model
{
    protected $table = 'principes_de_gouvernance';
    public $timestamps = true;

    use HasSecureIds, HasFactory ;

    protected $dates = ['deleted_at'];

    protected $fillable = array('nom', 'description', 'typeId');

    protected static function boot()
    {
        parent::boot();

        static::deleted(function ($principe_de_gouvernance) {

            DB::beginTransaction();
            try {

                $principe_de_gouvernance->criteres_de_gouvernance()->delete();

                DB::commit();
            } catch (\Throwable $th) {
                DB::rollBack();

                throw new Exception($th->getMessage(), 1);
            }
        });
    }

    public function criteres_de_gouvernance()
    {
        return $this->hasMany(CritereDeGouvernance::class, 'principeId');
    }

    public function type_de_gouvernance()
    {
        return $this->belongsTo(TypeDeGouvernance::class, 'typeId');
    }

    public function indicateurs_de_gouvernance()
    {
        return $this->morphMany(IndicateurDeGouvernance::class, 'principeable');
    }

}
