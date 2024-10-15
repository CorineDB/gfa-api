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

    protected $fillable = array('nom', 'description', 'typeDeGouvernanceId');

    protected static function boot()
    {
        parent::boot();

        static::deleted(function ($principe_de_gouvernance) {

            DB::beginTransaction();
            try {

                $principe_de_gouvernance->update([
                    'nom' => time() . '::' . $principe_de_gouvernance->nom
                ]);

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
        return $this->hasMany(CritereDeGouvernance::class, 'principeDeGouvernanceId');
    }
    
    /**
     * Charger la liste des indicateurs de tous les criteres de gouvernance
     */
    public function indicateurs_criteres_de_gouvernance()
    {
        return $this->hasManyThrough(
            IndicateurDeGouvernance::class,    // Final Model
            CritereDeGouvernance::class,       // Intermediate Model
            'principeDeGouvernanceId',      // Foreign key on the criteres_de_gouvernance table
            'principeable_id',       // Foreign key on the indicateurs_de_gouvernance table
            'id',                              // Local key on the principe_de_gouvernance table
            'id'                               // Local key on the criteres_de_gouvernance table
        )->where('principeable_type', CritereDeGouvernance::class);
    }
    public function indicateurs_criteres_de_gouvernance_count(){
        $this->criteres_de_gouvernance->each->count();
    }

    public function type_de_gouvernance()
    {
        return $this->belongsTo(TypeDeGouvernance::class, 'typeDeGouvernanceId');
    }

    public function indicateurs_de_gouvernance()
    {
        return $this->morphMany(IndicateurDeGouvernance::class, 'principeable');
    }

}
