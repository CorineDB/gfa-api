<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\MorphToMany;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\DB;
use SaiAshirwadInformatia\SecureIds\Models\Traits\HasSecureIds;
use Exception;

class Site extends Model
{
    use HasSecureIds, SoftDeletes, HasFactory;

    protected $table = 'sites';

    public $timestamps = true;

    protected $dates = ['deleted_at'];

    protected $fillable = array('nom', 'quartier', 'arrondissement', 'commune', 'departement', 'longitude', 'latitude', 'programmeId');

    protected static function boot()
    {
        parent::boot();

        static::deleting(function ($site) {
            if (($site->indicateurs->count() > 0) || $site->projets->count() > 0 || $site->activites->count() > 0) {
                // Prevent deletion by throwing an exception
                throw new Exception("Impossible de supprimer ce site car il est lié à un ou plusieurs indicateurs, projets ou activités. Veuillez d'abord supprimer ou dissocier ces éléments.");
            }
        });

        static::deleted(function ($site) {
            DB::beginTransaction();
            try {
                $site->update([
                    'nom' => time() . '::' . $site->nom
                ]);

                DB::commit();
            } catch (\Throwable $th) {
                DB::rollBack();

                throw new Exception($th->getMessage(), 1);
            }
        });
    }

    public function passation()
    {
        return $this->hasOne(Passation::class, 'siteId');
    }

    public function eActiviteMods()
    {
        return $this->hasMany(EActiviteMod::class, 'siteId');
    }

    public function suiviFinancierMods()
    {
        return $this->hasMany(SuiviFinancierMod::class, 'siteId');
    }

    public function esuivis()
    {
        return $this->hasMany(ESuivi::class, 'siteId');
    }

    public function bailleurs()
    {
        return $this->belongsToMany(Bailleur::class, 'bailleur_sites', 'siteId', 'bailleurId');
    }

    public function entreprisesExecutant()
    {
        return $this->belongsToMany(EntrepriseExecutant::class, 'entreprise_executant_sites', 'siteId', 'entrepriseExecutantId');
    }

    public function sync_bailleurs()
    {
        return $this->belongsToMany(Bailleur::class, 'bailleur_sites', 'siteId', 'bailleurId')->withPivot('siteId', 'bailleurId', 'programmeId');
    }

    public function bailleurs_site()
    {
        return $this->hasMany(BailleurSite::class, 'siteId');
    }

    public function entreprises()
    {
        return $this->belongsToMany(EntrepriseExecutant::class, 'entreprise_executant_sites', 'siteId', 'entrepriseExecutantId');
    }

    public function sinistres()
    {
        return $this->hasMany(Sinistre::class, 'siteId');
    }

    /**
     * Get all of the indicateurs that are assigned this site.
     */
    public function indicateurs(): MorphToMany
    {
        return $this->morphedByMany(Indicateur::class, 'siteable');
    }

    /**
     * Get all of the projets that are assigned this site.
     */
    public function projets(): MorphToMany
    {
        return $this->morphedByMany(Projet::class, 'siteable');
    }

    /**
     * Get all of the activites that are assigned this site.
     */
    public function activites(): MorphToMany
    {
        return $this->morphedByMany(Activite::class, 'siteable');
    }
}
