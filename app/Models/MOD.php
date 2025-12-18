<?php

namespace App\Models;

use Exception;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\DB;
use SaiAshirwadInformatia\SecureIds\Models\Traits\HasSecureIds;

class MOD extends Model
{

    use HasSecureIds, HasFactory ;

    protected $table = 'mods';

    public $timestamps = true;

    protected $fillable = [];

    protected $dates = ['deleted_at'];

    protected $with = ['user'];

    protected $hidden = ['updated_at', 'deleted_at'];

    protected $cast = ["created_at" => "datetime:Y-m-d"];


    protected static function boot() {
        parent::boot();

        static::deleting(function($mod) {

            DB::beginTransaction();

            try {

                $mod->user()->delete();

                $mod->teamMembers->each(function ($teamMember) {
                    optional($teamMember->user)->update(['statut' => -1]);
                });

                $mod->indicateurs()->delete();
                $mod->esuivis()->delete();
                $mod->eActiviteMods()->delete();

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

    public function entrepriseExecutants()
    {
        return $this->belongsToMany(EntrepriseExecutant::class, 'entreprise_executant_mods', 'modId', 'entrepriseExecutantId');
    }

    public function indicateurs()
    {
        return $this->hasMany(IndicateurMod::class, 'modId');
    }

    public function passations()
    {
        return $this->morphMany(Passation::class, 'passationable');
    }

    public function eActiviteMods()
    {
        return $this->hasMany(EActiviteMod::class, 'modId');
    }

    public function checkLists()
    {
        return $this->morphMany(CheckList::class, 'profilable');
    }

    public function esuivis()
    {
        return $this->morphMany(ESuivi::class, 'auteurable');
    }

}
