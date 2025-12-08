<?php

namespace App\Models;

use Exception;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\DB;
use SaiAshirwadInformatia\SecureIds\Models\Traits\HasSecureIds;

class MissionDeControle extends Model
{

    use HasSecureIds, HasFactory ;

    protected $table = 'mission_de_controles';

    protected $fillable = ['bailleurId'];

    public $timestamps = true;

    protected $dates = ['deleted_at'];

    protected $cast = ["created_at" => "datetime:Y-m-d", "updated_at" => "datetime:Y-m-d", "deleted_at" => "datetime:Y-m-d"];

    protected $hidden = ['pivot', 'updated_at', 'deleted_at'];

    protected $with = ['user'];

    protected static function boot() {
        parent::boot();

        static::deleting(function($maitriseDeControle) {

            DB::beginTransaction();
            try {

                $maitriseDeControle->user()->delete();
                $maitriseDeControle->passations()->delete();
                $maitriseDeControle->esuivis()->delete();

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

    public function users()
    {
        return $this->belongsToMany(User::class, 'mission_de_controle_users', 'missionDeControleId', 'userId');
    }

    public function roles()
    {
        return $this->morphMany(Role::class, 'roleable');
    }

    public function passations()
    {
        return $this->morphMany(Passation::class, 'passationable');
    }

    public function missionDeControles()
    {
        return $this->hasMany(MissionDeControle::class, 'missionDeControleId');
    }

    public function checkLists()
    {
        return $this->morphMany(CheckList::class, 'profilable');
    }

    public function esuivis()
    {
        return $this->morphMany(ESuivi::class, 'auteurable');
    }

    public function bailleur()
    {
        return $this->belongsTo(Bailleur::class, 'bailleurId');
    }

}
