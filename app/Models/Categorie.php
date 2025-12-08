<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\DB;
use SaiAshirwadInformatia\SecureIds\Models\Traits\HasSecureIds;
use Exception;

class Categorie extends Model
{
    use HasSecureIds, SoftDeletes, HasFactory;

    protected $table = 'categories';

    public $timestamps = true;

    protected $appends = ['code'];

    protected $dates = ['deleted_at'];

    protected $fillable = ['nom', 'type', 'indice', 'categorieId', 'programmeId'];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'updated_at', 'deleted_at'
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

    protected static function boot()
    {
        parent::boot();

        static::deleting(function ($categorie) {
            if (($categorie->indicateurs->count() > 0) || $categorie->categories->count() > 0 || $categorie->indicateurMods->count() > 0) {
                // Prevent deletion by throwing an exception
                throw new Exception("Impossible de supprimer cette catégorie. Veuillez d'abord supprimer tous les indicateurs, sous-catégories et modifications associées.");
            }
        });

        static::deleted(function ($categorie) {
            DB::beginTransaction();
            try {
                $categorie->update([
                    'nom' => time() . '::' . $categorie->nom
                ]);

                DB::commit();
            } catch (\Throwable $th) {
                DB::rollBack();
                throw new Exception($th->getMessage(), 1);
            }
        });
    }

    public function getCodeAttribute()
    {
        if ($this->categorieId !== null) {
            return $this->categorie->code . '.' . $this->indice;
        }

        return $this->indice;
    }

    public function programme()
    {
        return $this->belongsTo(Programme::class, 'programmeId');
    }

    public function categorie()
    {
        return $this->belongsTo(Categorie::class, 'categorieId');
    }

    public function categories()
    {
        return $this->hasMany(Categorie::class, 'categorieId');
    }

    /**
     * Liste des indicateurs de bailleur
     *
     * return List<Indicateur>
     */
    public function indicateurs()
    {
        return $this->hasMany(Indicateur::class, 'categorieId');
    }

    /**
     * Liste des indicateurs de mod
     *
     * return List<IndicateurMod>
     */
    public function indicateurMods()
    {
        return $this->hasMany(IndicateurMod::class, 'categorieId');
    }
}
