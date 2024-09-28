<?php

namespace App\Models;

use Exception;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use SaiAshirwadInformatia\SecureIds\Models\Traits\HasSecureIds;


class CritereDeGouvernance extends Model
{
    protected $table = 'criteres_de_gouvernance';
    public $timestamps = true;

    use HasSecureIds, HasFactory ;

    protected $dates = ['deleted_at'];

    protected $fillable = array('nom', 'description', 'principeDeGouvernanceId');

    protected static function boot()
    {
        parent::boot();

        static::deleted(function ($critere_de_gouvernance) {

            DB::beginTransaction();
            try {

                $critere_de_gouvernance->update([
                    'nom' => time() . '::' . $critere_de_gouvernance->nom
                ]);

                $critere_de_gouvernance->indicateurs_de_gouvernance()->delete();

                DB::commit();
            } catch (\Throwable $th) {
                DB::rollBack();

                throw new Exception($th->getMessage(), 1);
            }
        });
    }

    public function principe_de_gouvernance()
    {
        return $this->belongsTo(PrincipeDeGouvernance::class, 'principeDeGouvernanceId');
    }

    public function indicateurs_de_gouvernance()
    {
        return $this->morphMany(IndicateurDeGouvernance::class, 'principeable');
    }
}