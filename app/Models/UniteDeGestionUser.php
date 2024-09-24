<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;


class UniteDeGestionUser extends Model
{
    use HasFactory ;
    protected $table = 'unitee_de_gestion_users';
    public $timestamps = true;
    protected $dates = ['deleted_at'];


    public function role()
    {
        return $this->belongsTo(Role::class, 'roleId');
    }
}
