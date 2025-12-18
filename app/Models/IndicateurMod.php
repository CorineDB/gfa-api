<?php

namespace App\Models;

use Exception;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\DB;
use SaiAshirwadInformatia\SecureIds\Models\Traits\HasSecureIds;

class IndicateurMod extends Model
{

    use HasSecureIds, HasFactory ;

    protected $table = 'indicateur_mods';

    public $timestamps = true;

    protected $dates = ['deleted_at'];

    protected $fillable = ["nom", "description", "anneeDeBase", "valeurDeBase", "frequence", "source", "responsable", "definition", "modId", "categorieId", "uniteeMesureId", "programmeId"];

    protected static function boot() {
        parent::boot();

        static::deleting(function($indicateurMod) {

            DB::beginTransaction();
            try {

                $indicateurMod->suivis()->delete();

                DB::commit();
            } catch (\Throwable $th) {
                DB::rollBack();

                throw new Exception($th->getMessage(), 1);
            }

        });
    }

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'updated_at','deleted_at', "modId"
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        "created_at" => "datetime:Y-m-d",
        "updated_at" => "datetime:Y-m-d",
        "deleted_at" => "datetime:Y-m-d"
    ];

    /**
     * Unitée de mésure d'un indicateur
     *
     * return Unitee
     */
    public function unitee_mesure()
    {
        return $this->belongsTo(Unitee::class, 'uniteeMesureId');
    }

    public function mod()
    {
        return $this->belongsTo(MOD::class, 'modId');
    }

    public function categorie()
    {
        return $this->belongsTo(Categorie::class, 'categorieId');
    }

    public function suivis()
    {
        return $this->valeursCible()->with("suivisIndicateur");
    }

    public function valeursCible()
    {
        return $this->morphMany(ValeurCibleIndicateur::class, 'cibleable');
    }

    public function unitee()
    {
        return $this->belongsTo(Unitee::class, 'uniteeId');
    }

    public function programme()
    {
        return $this->belongsTo(Programme::class, 'programmeId');
    }

}
