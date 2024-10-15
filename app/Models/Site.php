<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphToMany;
use SaiAshirwadInformatia\SecureIds\Models\Traits\HasSecureIds;

class Site extends Model
{

    use HasSecureIds, HasFactory;

    protected $table = 'sites';

    public $timestamps = true;

    protected $dates = ['deleted_at'];

    protected $fillable = array('nom', 'longitude', 'latitude');

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
        return $this->belongsToMany(Bailleur::class, 'bailleur_sites', 'siteId', 'bailleurId')->withPivot('siteId', 'bailleurId','programmeId');
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
        return $this->morphedByMany(Site::class, 'siteable');
    }
 
    /**
     * Get all of the projets that are assigned this site.
     */
    public function projets(): MorphToMany
    {
        return $this->morphedByMany(Site::class, 'siteable');
    }
 
    /**
     * Get all of the activites that are assigned this site.
     */
    public function activites(): MorphToMany
    {
        return $this->morphedByMany(Site::class, 'siteable');
    }
}
