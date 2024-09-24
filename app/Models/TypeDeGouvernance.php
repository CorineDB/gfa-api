<?php

namespace App\Models;

use Exception;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use SaiAshirwadInformatia\SecureIds\Models\Traits\HasSecureIds;

class TypeDeGouvernance extends Model
{
    protected $table = 'types_de_gouvernance';
    public $timestamps = true;

    use HasSecureIds, HasFactory ;

    protected $dates = ['deleted_at'];

    protected $fillable = array('nom', 'description', 'programmeId');

    protected static function boot()
    {
        parent::boot();

        static::deleted(function ($type_de_gouvernance) {

            DB::beginTransaction();
            try {

                $type_de_gouvernance->principes_de_gouvernance()->delete();

                DB::commit();
            } catch (\Throwable $th) {
                DB::rollBack();

                throw new Exception($th->getMessage(), 1);
            }
        });
    }

    public function principes_de_gouvernance()
    {
        return $this->hasMany(PrincipeDeGouvernance::class, 'typeId');
    }

    public function programme()
    {
        return $this->belongsTo(Programme::class, 'programmeId');
    }

}
