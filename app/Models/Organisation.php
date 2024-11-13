<?php

namespace App\Models;

use App\Http\Resources\user\UserResource;
use Exception;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\DB;
use SaiAshirwadInformatia\SecureIds\Models\Traits\HasSecureIds;

class Organisation extends Model
{

    use HasSecureIds, HasFactory ;

    protected $table = 'organisations';

    public $timestamps = true;

    protected $fillable = ["sigle", "code", "nom_point_focal", "prenom_point_focal", "contact_point_focal", 'type', 'pays', 'departement', 'commune', 'arrondissement', 'quartier', 'secteurActivite', 'longitude', 'latitude'];

    protected $dates = ['deleted_at'];

    protected $with = ['user'];

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
        "code" => "integer",
        "longitude" => "float",
        "latitude" => "float",
        "created_at" => "datetime:Y-m-d",
        "updated_at" => "datetime:Y-m-d",
        "deleted_at" => "datetime:Y-m-d"
    ];

    protected static function boot() {
        parent::boot();


        static::deleting(function ($organisation) {

            DB::beginTransaction();
            try {

                if(!$organisation->projet){
                    $organisation->delete();
                    DB::commit();
                }
            } catch (\Throwable $th) {
                DB::rollBack();

                throw new Exception($th->getMessage(), 1);
            }
        });

        static::deleted(function($organisation) {

            DB::beginTransaction();
            try {

                $organisation->user()->delete();

                $organisation->teamMembers->each(function ($teamMember) {
                    optional($teamMember->user)->update(['statut' => -1]);
                });

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

    /**
     * Get organisations by programme ID.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param int $programmeId
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeByProgramme($query, $programmeId = null)
    {
        return $query->whereHas('user', function ($q) use ($programmeId) {
            if(!$programmeId){
                $programmeId = auth()->user()->programmeId;
            }

            $q->where('programmeId', $programmeId)->where('type', "organisation");
        });
    }
    
    /**
     * Charger la liste des outcomes d'un projet
     */
    public function outcomes()
    {
        return $this->hasManyThrough(
            Composante::class,    // Final Model
            Projet::class,       // Intermediate Model
            'projetable_id',                  // Foreign key on the types_de_gouvernance table
            'projetId',          // Foreign key on the principes_de_gouvernance table
            'id',                              // Local key on the principes_de_gouvernance table
            'id'                               // Local key on the types_de_gouvernance table
        )->whereNull("composanteId");
    }

    public function fonds()
    {
        return $this->belongsToMany(Fond::class,'fond_organisations', 'organisationId', 'fondId')->wherePivotNull('deleted_at')->withPivot(["id", "budgetAllouer"]);
    }

    public function evaluations_de_gouvernance(?int $organisationId = null, ?string $token = null)
    {
        // Start with the base relationship
        $evaluations_de_gouvernance = $this->belongsToMany(EvaluationDeGouvernance::class,'evaluation_organisations', 'organisationId', 'evaluationDeGouvernanceId')->wherePivotNull('deleted_at')->withPivot(["id", "nbreParticipants", 'participants', 'token']);

        if ($organisationId) {
            $evaluations_de_gouvernance = $evaluations_de_gouvernance->wherePivot("organisationId", $organisationId);
        }

        if ($token) {
            $evaluations_de_gouvernance = $evaluations_de_gouvernance->wherePivot("token", $token);
        }
        
        return $evaluations_de_gouvernance;
    }

    public function soumissions()
    {
        return $this->hasMany(Soumission::class, 'organisationId');
    }

    public function sousmissions_factuel()
    {
        return $this->hasMany(Soumission::class, 'organisationId')->where("type", "factuel");
    }

    public function sousmissions_de_perception()
    {
        return $this->hasMany(Soumission::class, 'organisationId')->where("type", "perception");
    }

    public function fiches_de_synthese($evaluationDeGouvernanceId = null, $type = null)
    {
        $fiches_de_synthese = $this->hasMany(FicheDeSynthese::class, 'organisationId');

        if($type){
            $fiches_de_synthese = $fiches_de_synthese->where("type", $type);
        }

        if($evaluationDeGouvernanceId){
            $fiches_de_synthese = $fiches_de_synthese->where("evaluationDeGouvernanceId", $evaluationDeGouvernanceId);
        }

        return $fiches_de_synthese;
    }

    public function profiles(?int $evaluationDeGouvernanceId = null, ?int $evaluationOrganisationId = null): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        // Start with the base relationship
        $profiles = $this->hasMany(ProfileDeGouvernance::class, 'organisationId');

        if ($evaluationDeGouvernanceId) {
            $profiles = $profiles->where("evaluationDeGouvernanceId", $evaluationDeGouvernanceId);
        }

        if ($evaluationOrganisationId) {
            $profiles = $profiles->where("evaluationOrganisationId", $evaluationOrganisationId);
        }

        // Get the results and apply grouping on the collection level
        return $profiles;
    }
}