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

    /**
     * The attributes that should be appended to the model's array form.
     *
     * @var array
     */
    protected $appends = [];

    protected static function boot()
    {
        parent::boot();

        static::deleted(function ($indicateur_de_gouvernance) {

            DB::beginTransaction();

            try {

                $indicateur_de_gouvernance->update([
                    'nom' => time() . '::' . $indicateur_de_gouvernance->nom
                ]);

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
        return $this->belongsToMany(OptionDeReponse::class,'indicateur_options_de_reponse', 'indicateurId', 'optionId');
    }

    public function principeable()
    {
        return $this->morphTo();
    }

    public function observations()
    {
        return $this->hasMany(ReponseCollecter::class, 'indicateurDeGouvernanceId');
    }
}