<?php

namespace App\Models;

use Exception;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\DB;
use SaiAshirwadInformatia\SecureIds\Models\Traits\HasSecureIds;

class Indicateur extends Model
{

    use HasSecureIds, HasFactory ;

    protected $table = 'indicateurs';

    public $timestamps = true;

    protected $dates = ["deleted_at"];

    protected $fillable = ["nom", "description", "anneeDeBase", "valeurDeBase", "uniteeMesureId", "bailleurId", "categorieId", "programmeId", "hypothese", "sourceDeVerification", "kobo", "koboVersion", "valeurCibleTotal"];

    protected static function boot() {
        parent::boot();

        static::deleting(function($indicateur) {

            DB::beginTransaction();
            try {

                $indicateur->valeursCible()->delete();

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
        'updated_at','deleted_at', "bailleurId"
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        "created_at" => "datetime:Y-m-d",
        "updated_at" => "datetime:Y-m-d",
        "deleted_at" => "datetime:Y-m-d",
        //"anneeDeBase" => "datetime:Y-m-d"
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

    public function bailleur()
    {
        return $this->belongsTo(Bailleur::class, 'bailleurId');
    }

    public function categorie()
    {
        return $this->belongsTo(Categorie::class, 'categorieId');
    }

    public function unitee()
    {
        return $this->belongsTo(Unitee::class, 'uniteeId');
    }

    public function suivis()
    {
        return $this->valeursCible()->with("suivisIndicateur");
    }

    public function valeursCible()
    {
        return $this->morphMany(ValeurCibleIndicateur::class, 'cibleable');
    }

    public function programme()
    {
        return $this->belongsTo(Programme::class, 'programmeId');
    }

}
