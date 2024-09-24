<?php

namespace App\Models;

use Exception;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use SaiAshirwadInformatia\SecureIds\Models\Traits\HasSecureIds;

class Output extends Model
{

    protected $table = 'outputs';
    public $timestamps = true;

    use HasSecureIds, HasFactory ;

    protected $dates = ['deleted_at'];

    protected $fillable = array('nom', 'position', 'poids', 'description', 'programmeId');

    protected static function boot()
    {
        parent::boot();

        static::deleted(function ($output) {

            DB::beginTransaction();
            try {

                $output->activites()->delete();


                DB::commit();
            } catch (\Throwable $th) {
                DB::rollBack();

                throw new Exception($th->getMessage(), 1);
            }
        });
    }

    public function outcome()
    {
        return $this->belongsTo(Outcome::class, 'outcomeId');
    }

    public function activites()
    {
        return $this->hasMany(Activite::class, 'composanteId')->orderBy('position', 'asc');
    }
}
