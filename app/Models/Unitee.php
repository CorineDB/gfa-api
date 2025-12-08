<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use SaiAshirwadInformatia\SecureIds\Models\Traits\HasSecureIds;

class Unitee extends Model
{
    use HasFactory, SoftDeletes, HasSecureIds;

    protected $table = 'unitees';

    public $timestamps = true;

    protected $dates = ['deleted_at'];

    protected static function boot()
    {
        parent::boot();

        static::deleting(function ($uniteeDeMesure) {
            $uniteeDeMesure->update([
                'nom' => time() . '::' . $uniteeDeMesure->nom
            ]);
        });
    }

    /**
     * Les attributs qui sont assignés en masse
     *
     * return array
     */
    protected $fillable = [
        'nom',
        'type',
        'programmeId'
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'pivot', 'created_at', 'updated_at', 'deleted_at'
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'created_at' => 'datetime:Y-m-d',
        'updated_at' => 'datetime:Y-m-d',
        'deleted_at' => 'datetime:Y-m-d'
    ];

    /**
     * Liste des indicateurs d'un bailleur lié à une unitée de mésure
     *
     * return List<Indicateur>
     */
    public function programme()
    {
        return $this->belongsTo(Programme::class, 'programmeId');
    }

    /**
     * Liste des indicateurs d'un bailleur lié à une unitée de mésure
     *
     * return List<Indicateur>
     */
    public function indicateurs()
    {
        return $this->hasMany(Indicateur::class, 'uniteeMesureId');
    }

    /**
     * Liste des indicateurs d'un MOD lié à une unitée de mésure
     *
     * return List<IndicateurMod>
     */
    public function modIndicateurs()
    {
        return $this->hasMany(IndicateurMod::class, 'uniteeMesureId');
    }

    /*
     * public function indicateurs()
     * {
     *     return $this->hasMany(Indicateur::class, 'uniteeId');
     * }
     *
     * public function indicateurMods()
     * {
     *     return $this->hasMany(IndicateurMod::class, 'uniteeId');
     * }
     */

    public function checkList()
    {
        return $this->hasMany(Unitee::class, 'uniteeId');
    }

    public function checkListCom()
    {
        return $this->hasMany(CheckListCom::class, 'uniteeId');
    }
}
