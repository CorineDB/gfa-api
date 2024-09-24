<?php

namespace App\Models;

use Exception;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use SaiAshirwadInformatia\SecureIds\Models\Traits\HasSecureIds;

class Outcome extends Model
{
    protected $table = 'outcomes';
    public $timestamps = true;

    use HasSecureIds, HasFactory ;

    protected $dates = ['deleted_at'];

    protected $fillable = array('nom', 'position', 'poids', 'description', 'programmeId');

    protected static function boot()
    {
        parent::boot();

        static::deleted(function ($outcome) {

            DB::beginTransaction();
            try {

                $outcome->outputs()->delete();


                DB::commit();
            } catch (\Throwable $th) {
                DB::rollBack();

                throw new Exception($th->getMessage(), 1);
            }
        });
    }

    public function outputs()
    {
        return $this->hasMany(Output::class, 'outcomeId')->orderBy('position', 'asc');
    }

    public function programme()
    {
        return $this->belongsTo(Programme::class, 'programmeId');
    }
}
