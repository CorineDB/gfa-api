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

    protected $fillable = ["sigle", "code"];

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
        "created_at" => "datetime:Y-m-d",
        "updated_at" => "datetime:Y-m-d",
        "deleted_at" => "datetime:Y-m-d"
    ];

    protected static function boot() {
        parent::boot();

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

    public function indicateurs()
    {
        return $this->hasMany(Indicateur::class, 'indicateurable');
    }

}