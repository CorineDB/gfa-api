<?php

namespace App\Models;

use Exception;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use SaiAshirwadInformatia\SecureIds\Models\Traits\HasSecureIds;

class OptionsDeReponse extends Model
{
    protected $table = 'options_de_reponse';
    public $timestamps = true;

    use HasSecureIds, HasFactory ;

    protected $dates = ['deleted_at'];

    protected $fillable = array('nom', 'description', 'type', 'can_have_multiple_reponse', 'principeable_id', 'principeable_type');

    protected static function boot()
    {
        parent::boot();

        static::deleted(function ($option_de_reponse) {

            DB::beginTransaction();
            try {

                $option_de_reponse->indicateurs_de_reponse()->delete();

                DB::commit();
            } catch (\Throwable $th) {
                DB::rollBack();

                throw new Exception($th->getMessage(), 1);
            }
        });
    }

    public function indicateurs_de_gouvernance()
    {
        return $this->belongsToMany(IndicateurDeGouvernance::class, 'optionId');
    }
}
