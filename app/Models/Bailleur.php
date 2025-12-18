<?php

namespace App\Models;

use Exception;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use SaiAshirwadInformatia\SecureIds\Models\Traits\HasSecureIds;

class Bailleur extends Model
{

    use HasSecureIds, HasFactory;

    protected $table = 'bailleurs';

    protected $fillable = ['sigle', 'pays'];

    protected $dates = ['deleted_at'];

    protected $with = ['user'];

    protected $cast = [
        "created_at" => "datetime:Y-m-d",
        'updated_at' => 'datetime:Y-m-d H:i:s',
        'deleted_at' => 'datetime:Y-m-d H:i:s'
    ];

    protected $hidden = ['updated_at', 'deleted_at'];

    protected static function boot() {
        parent::boot();

        static::deleting(function($bailleur) {

            DB::beginTransaction();
            try {

                $bailleur->update([
                    'sigle' => time() . '::' . $bailleur->sigle
                ]);

                $bailleur->user()->delete();

                $bailleur->teamMembers->each(function ($teamMember) {
                    optional($teamMember->user)->update(['statut' => -1]);
                });

                $bailleur->projets()->delete();

                $bailleur->anos()->delete();

                $bailleur->codes()->delete();

                $bailleur->indicateurs()->delete();

                $bailleur->suivis()->delete();

                $bailleur->decaissements()->delete();

                $bailleur->suiviFinanciers()->delete();

                //$bailleur->sinistres()->delete();

                $bailleur->eActiviteMods()->delete();

                $bailleur->maitriseOeuvres()->delete();

                DB::commit();
            } catch (\Throwable $th) {
                DB::rollBack();

                throw new Exception($th->getMessage(), 1);
            }

        });
    }

    public function user()
    {
        return $this->morphOne(User::class, 'profilable');
    }

    public function teamMembers()
    {
        return $this->morphMany(TeamMember::class, 'profilable');
    }

    public function sites()
    {
        return $this->belongsToMany(Site::class, 'bailleur_sites', 'bailleurId', 'siteId');
    }

    public function bailleurSites()
    {
        return $this->hasMany(BailleurSite::class, 'bailleurId');
    }

    public function bailleurProgrammeSites($programmeId)
    {
        return $this->hasMany(BailleurSite::class, 'bailleurId')->where('programmeId', $programmeId);
    }

    public function projets($programmeId = null)
    {

        if(!$programmeId)
        {
            return $this->hasOne(Projet::class, 'bailleurId');
        }

        return $this->hasMany(Projet::class, 'bailleurId')->where('programmeId', $programmeId)->first();
    }

    public function codes()
    {
        return $this->hasMany(Code::class, 'bailleurId');
    }

    public function scopeCodes($programmeId)
    {
        return $this->hasMany(Code::class, 'bailleurId')->where('programmeId', $programmeId)->first();
    }

    public function anos()
    {
        return $this->hasMany(Ano::class, 'bailleurId');
    }

    public function indicateurs()
    {
        return $this->hasMany(Indicateur::class, 'bailleurId');
    }

    public function suivis()
    {
        return $this->hasMany(Indicateur::class, 'bailleurId')->with("valeursCible");
    }

    public function maitriseOeuvres()
    {
        return $this->hasMany(MaitriseOeuvre::class, 'bailleurId');
    }

    public function entrepriseExecutants()
    {
        //return $this->belongsToMany(EntrepriseExecutant::class,'bailleur_entreprise_executants', 'bailleurId', 'entrepriseExecutantId');

        $entreprisesExecutant = [];
        $controle = 1;

        $sites = $this->sites()->where('programmeId', $this->user->programme->id)->get();

        foreach($sites as $site)
        {
            $ids = EntrepriseExecutantSite::where('programmeId', $this->user->programme->id)->where('siteId', $site->id)->get();

            foreach($ids as $id)
            {
                $entreprise = EntrepriseExecutant::find($id->entrepriseExecutantId);

               if($entreprise)
               {
                    foreach($entreprisesExecutant as $e)
                    {
                        if($e->id == $entreprise->id) $controle = 0;
                    }

                    if($controle) array_push($entreprisesExecutant, $entreprise);
                    $controle = 1;

               }
            }

        }



        return $entreprisesExecutant;
    }

    public function missionDeControle()
    {
        return $this->hasOne(MissionDeControle::class, 'bailleurId');
    }

    public function decaissements()
    {
        return $this->morphMany(Decaissement::class, 'decaissementable');
    }

    public function suiviFinanciers()
    {
        return $this->morphMany(SuiviFinancier::class, 'suivi_financierable');
    }

    public function eActiviteMods()
    {
        return $this->hasMany(EActiviteMod::class, 'bailleurId');
    }

    /*public function sinistres()
    {
        return $this->hasMany(Sinistre::class, 'bailleurId');
    }*/

    public function tepGlobal()
    {
        return 0;
    }

}
