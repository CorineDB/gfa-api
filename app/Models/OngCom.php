<?php

namespace App\Models;

use Exception;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\DB;
use SaiAshirwadInformatia\SecureIds\Models\Traits\HasSecureIds;

class OngCom extends Model
{
    use HasSecureIds, HasFactory ;

    protected $table = 'ong_com';

    public $timestamps = true;

    protected $fillable = [];

    protected $with = ['user'];

    protected $hidden = ['updated_at', 'deleted_at'];

    protected $cast = ["created_at" => "datetime:Y-m-d", "deleted_at" => "datetime:Y-m-d", "updated_at" => "datetime:Y-m-d"];

    protected $dates = ['deleted_at'];

    protected static function boot() {
        parent::boot();

        static::deleting(function($ongCom) {

            DB::beginTransaction();

            try {

                $ongCom->user()->delete();

                $ongCom->teamMembers->each(function ($teamMember) {
                    optional($teamMember->user)->update(['statut' => -1]);
                });

                DB::commit();
            } catch (\Throwable $th) {
                DB::rollBack();

                throw new Exception($th->getMessage(), 1);
            }

        });
    }

    public function checkListComs()
    {
        return $this->hasMany(CheckListCom::class, 'ongComId');
    }

    public function user()
    {
        return $this->morphOne(User::class, 'profilable');
    }

    public function teamMembers()
    {
        return $this->morphMany(TeamMember::class, 'profilable');
    }

}
