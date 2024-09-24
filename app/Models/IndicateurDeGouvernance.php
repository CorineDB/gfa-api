<?php

namespace App\Models;

use Exception;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use SaiAshirwadInformatia\SecureIds\Models\Traits\HasSecureIds;

class IndicateurDeGouvernance extends Model
{
    protected $table = 'indicateurs_de_gouvernance';
    public $timestamps = true;

    use HasSecureIds, HasFactory ;

    protected $dates = ['deleted_at'];

    protected $fillable = array('nom', 'description', 'type', 'can_have_multiple_reponse', 'principeable_id', 'principeable_type');

    protected static function boot()
    {
        parent::boot();

        static::deleted(function ($indicateur_de_gouvernance) {

            DB::beginTransaction();
            try {

                $indicateur_de_gouvernance->options_de_reponse()->delete();

                DB::commit();
            } catch (\Throwable $th) {
                DB::rollBack();

                throw new Exception($th->getMessage(), 1);
            }
        });
    }

    public function options_de_reponse()
    {
        return $this->belongsToMany(OptionsDeReponse::class, 'indicateurId');
    }

    public function principeable()
    {
        return $this->morphTo();
    }
}