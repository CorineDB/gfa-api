<?php

namespace App\Models;

use App\Traits\Eloquents\HasPermissionTrait;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

use Exception;
use Illuminate\Notifications\Notifiable;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Support\Facades\DB;
use Laravel\Sanctum\HasApiTokens;
use SaiAshirwadInformatia\SecureIds\Models\Traits\HasSecureIds;
use Illuminate\Support\Str;
use Laravel\Sanctum\NewAccessToken;


class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable, HasPermissionTrait, HasSecureIds ;

    protected $table = 'users';

    public $timestamps = true;

    protected $dates = ['deleted_at'];

    protected $fillable = [
        'nom',
        'email',
        'password',
        'contact',
        'prenom',
        'poste',
        'type',
        'code',
        'profilable_type',
        'profilable_id',
        'programmeId',
        'statut',
        'token',
        'first_connexion',
        'account_verification_request_sent_at',
        'password_update_at',
        'last_password_remember',
        'link_is_valide',
        'photo'
    ];

    protected $with = ['roles'];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'profilable_type',
        'profilable_id',
        'programmeId',
        'password',
        'token',
        'remember_token',
        'updated_at','deleted_at'
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'emailVerifiedAt' => 'datetime',
        "created_at" => "datetime:Y-m-d"
    ];

    protected static function boot() {
        parent::boot();

        static::deleting(function($user) {

            DB::beginTransaction();
            try {

                $user->update([
                    'email' => time() . '::' . $user->email,
                    'nom' => time() . '::' . $user->nom,
                    'contact' => time() . '::' . $user->contact
                ]);

                $user->roles()->detach();

                DB::commit();
            } catch (\Throwable $th) {
                DB::rollBack();

                throw new Exception($th->getMessage(), 1);
            }

        });
    }


    /**
     * The channels the user receives notification broadcasts on.
     */
    public function receivesBroadcastNotificationsOn(): string
    {
        return 'notification.'.$this->id;
    }

    public function programme()
    {
        return $this->belongsTo(Programme::class, 'programmeId');
    }

    public function profilable()
    {
        return $this->morphTo();
    }

    public function mod()
    {
        return $this->morphTo()->where("profilable_type", get_class(new MOD()));
    }

    public function bailleur()
    {
        return $this->morphTo()->where("profilable_type", get_class(new Bailleur()))->get();
    }

    public function ongCom()
    {
        return $this->morphTo()->where("profilable_type", get_class(new OngCom()));
    }

    public function organisation()
    {
        return $this->profilable_type === Organisation::class ? $this->profilable : null;
        return $this->morphTo()->where("profilable_type", get_class(new Organisation()));
    }

    public function entrepriseExecutant()
    {
        return $this->morphTo()->where("profilable_type", get_class(new EntrepriseExecutant()));
    }

    public function missionDeControle()
    {
        return $this->profilable();
        return $this->morphTo()->where("profilable_type", get_class(new MissionDeControle()));
    }

    public function uniteeDeGestion()
    {
        return $this->morphTo()->where("profilable_type", get_class(new UniteeDeGestion()));
    }

    public function role()
    {
        return $this->belongsToMany(Role::class, 'role_users', 'userId', 'roleId');
    }

    public function roles()
    {
        return $this->belongsToMany(Role::class, 'role_users', 'userId', 'roleId');
    }

    public function permissions()
    {
        return $this->roles()->get()->last()->permissions();
    }

    public function uniteeDeGestions()
    {
        return $this->belongsToMany(UniteeDeGestion::class, 'unitee_de_gestion_users', 'userId', 'uniteDeGestionId');
    }

    public function missionDeControles()
    {
        return $this->belongsToMany(MissionDeControle::class, 'mission_de_controle_users', 'userId', 'missionDeControleId');
    }

    public function team()
    {
        return $this->hasOne(TeamMember::class, 'userId');
    }

    public function scopeInstitutions($query)
    {
        return $query->where('type','institution');
    }

    public function activites()
    {
        return $this->hasMany(Activite::class, 'userId');
    }

    public function anos()
    {
        return $this->hasMany(Ano::class, 'auteurId');
    }


    /**
     * Create a new personal access token for the user.
     *
     * @param  string  $name
     * @param  array  $abilities
     * @return \Laravel\Sanctum\NewAccessToken
     */
    public function createToken(string $name, array $abilities = ['*'])
    {
        $token = $this->tokens()->create([
            'name' => $name,
            'token' => hash('sha256', $plainTextToken = Str::random(256)),
            'abilities' => $abilities,
        ]);

        return new NewAccessToken($token, $token->getKey().'|'.$plainTextToken);
    }

    public function commentaires()
    {
        return $this->morphMany(Commentaire::class, 'commentable');
    }

    public function activiteStructures()
    {
        return $this->belongsToMany(Activite::class,'activite_users', 'userId', 'activiteId');
    }

    public function logo()
    {
        return $this->morphOne(Fichier::class, 'fichiertable')->where('description', 'logo');
    }

    public function photo()
    {
        return $this->morphOne(Fichier::class, 'fichiertable')->where('description', 'photo');
    }

    public function fichiers()
    {
        return $this->morphMany(Fichier::class, 'fichiertable')->where('description', 'fichier');
    }

    public function myFichiers()
    {
        return $this->hasMany(Fichier::class, 'auteurId');
    }

    public function sharedFichiers()
    {
        return $this->hasMany(Fichier::class, 'sharedId');
    }

    public function formulaires()
    {
        return $this->hasMany(Formulaire::class, 'auteurId');
    }

    public function rapports()
    {
        return $this->hasMany(TemplateRapport::class, 'userId');
    }

    public function emailRapports()
    {
        return $this->hasMany(EmailRapport::class, 'userId');
    }
}

