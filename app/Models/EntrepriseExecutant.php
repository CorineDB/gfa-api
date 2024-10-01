<?php

namespace App\Models;

use App\Http\Resources\user\UserResource;
use Exception;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\DB;
use SaiAshirwadInformatia\SecureIds\Models\Traits\HasSecureIds;

class EntrepriseExecutant extends Model
{

    use HasSecureIds, HasFactory ;

    protected $table = 'entreprise_executants';

    public $timestamps = true;

    protected $fillable = [];

    protected $dates = ['deleted_at'];

    protected $with = ['user','mods'];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'updated_at','deleted_at','pivot'
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

    protected static function boot() {
        parent::boot();

        static::deleted(function($entrepriseExecutant) {

            DB::beginTransaction();
            try {

                $entrepriseExecutant->user()->delete();

                $entrepriseExecutant->teamMembers->each(function ($teamMember) {
                    optional($teamMember->user)->update(['statut' => -1]);
                });

                $entrepriseExecutant->esuivis()->delete();
                $entrepriseExecutant->passations()->delete();

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

    public function mods()
    {
        return $this->belongsToMany(MOD::class,'entreprise_executant_mods', 'entrepriseExecutantId', 'modId');
    }

    public function modByProgramme($id)
    {
        return $this->belongsToMany(MOD::class,'entreprise_executant_mods', 'entrepriseExecutantId', 'modId')
                   ->where('programmeId', $id)
                   ->first();
    }

    public function programmeMods($programmeId)
    {
        return $this->belongsToMany(MOD::class,'entreprise_executant_mods', 'entrepriseExecutantId', 'modId')->withPivot("programmeId")->wherePivot("programmeId", $programmeId);
    }

    public function esuivis()
    {
        return $this->hasMany(ESuivi::class, 'entrepriseExecutantId');
    }

    public function passations()
    {
        return $this->hasMany(Passation::class, 'entrepriseExecutantId');
    }

    public function maitriseOeuvres()
    {
        return $this->belongsToMany(MaitriseOeuvre::class,'entreprise_executant_maitrise_oeuvres', 'entrepriseExecutantId', 'maitriseOeuvreId');
    }

    public function sites()
    {
        return $this->belongsToMany(Site::class, 'entreprise_executant_sites', 'entrepriseExecutantId', 'siteId');
    }

    public function site($siteId)
    {
        return $this->belongsToMany(Site::class, 'entreprise_executant_sites', 'entrepriseExecutantId', 'siteId')->wherePivot('siteId', $siteId)->first();
    }

    public function bailleurs()
    {
        //return $this->belongsToMany(Bailleur::class,'bailleur_entreprise_executants', 'entrepriseExecutantId', 'bailleurId');

        $bailleurs = [];
        $controle = 1;

        $sites = $this->sites()->where('programmeId', $this->user->programme->id)->get();

        foreach($sites as $site)
        {
            $ids = BailleurSite::where('programmeId', $this->user->programme->id)->where('siteId', $site->id)->get();

            foreach($ids as $id)
            {
                $bailleur = Bailleur::find($id->bailleurId);

               if($bailleur)
               {
                    foreach($bailleurs as $b)
                    {
                        if($b->id == $bailleur->id) $controle = 0;
                    }

                    if($controle) array_push($bailleurs, $bailleur);
                    $controle = 1;

               }
            }

        }

        return $bailleurs;
    }

    public function eActivites()
    {
        return $this->belongsToMany(EActivite::class, 'entreprise_executant_e_activites', 'entrepriseExecutantId', 'eActiviteId');
    }

    public function evaluations()
    {
        return $this->hasMany(ReponseCollecter::class, 'organisationId');
    }

    public function notes_resultat()
    {
        return $this->hasMany(EnqueteResultatNote::class, 'organisationId');
    }

    public function projet()
    {
        return $this->morphOne(Projet::class, 'projetable');//->where('programmeId', $this->user->programmeId)->first();
    }
}
