<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

use SaiAshirwadInformatia\SecureIds\Models\Traits\HasSecureIds;

class MissionDeControleUser extends Model
{
    use HasSecureIds, HasFactory ;
    protected $table = 'mission_de_controle_users';
    public $timestamps = true;


    protected $dates = ['deleted_at'];

    public function role()
    {
        return $this->belongsTo(Role::class, 'roleId');
    }
}
