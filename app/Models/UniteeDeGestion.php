<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

use SaiAshirwadInformatia\SecureIds\Models\Traits\HasSecureIds;

class UniteeDeGestion extends Model
{
    use HasSecureIds ;

    protected $table = 'unitee_de_gestions';

    protected $dates = ['deleted_at'];

    protected $fillable = [
        'nom', 'programmeId'
    ];


    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'created_at' => 'datetime:Y-m-d',
        'updated_at' => 'datetime:Y-m-d H:i:s',
        'deleted_at' => 'datetime:Y-m-d H:i:s',
    ];

    protected $hidden = ['programmeId','updated_at', 'deleted_at'];

    protected static function boot() {
        parent::boot();

        static::deleting(function($uniteeDeGestion) {
            $uniteeDeGestion->update([
                'nom' => time() . '::' . $uniteeDeGestion->nom
            ]);

            $uniteeDeGestion->user()->delete();
        });
    }

    protected $with = ['user'];

    public function roles()
    {
        return $this->morphMany(Role::class, 'roleable');
    }

    public function users()
    {
        return $this->belongsToMany(User::class, 'unitee_de_gestion_users', 'uniteDeGestionId', 'userId');
    }

    public function user()
    {
        return $this->morphOne(User::class, 'profilable');
    }

    public function programme()
    {
        return $this->belongsTo(Programme::class, 'programmeId');
    }

    public function checkLists()
    {
        return $this->morphMany(CheckList::class, 'profilable');
    }

    public function esuivis()
    {
        return $this->morphMany(ESuivi::class, 'auteurable');
    }

    public function projet()
    {
        return $this->morphOne(Projet::class, 'projetable');//->where('programmeId', $this->user->programmeId)->first();
    }

    public function indicateurs()
    {
        return $this->belongsToMany(Indicateur::class, 'indicateur_responsables', 'responsableable_id', 'indicateurId')->wherePivotNull('deleted_at');
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

    public function suivis_indicateurs()
    {
        return $this->morphMany(SuiviIndicateur::class, 'suivi_indicateurable');
    }

    public function survey_forms()
    {
        return $this->morphMany(SurveyForm::class, 'created_by');
    }

    public function surveys()
    {
        return $this->morphMany(Survey::class, 'surveyable');
    }

}
