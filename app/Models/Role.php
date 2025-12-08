<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

use SaiAshirwadInformatia\SecureIds\Models\Traits\HasSecureIds;

class Role extends Model
{

    use HasSecureIds ;

    protected $table = 'roles';

    public $timestamps = true;

    protected $dates = ['deleted_at'];

    protected $fillable = array('nom', 'slug', 'description', 'roleable_type', 'roleable_id', 'programmeId');
    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'slug',
        'pivot',
        'updated_at','deleted_at'
    ];

    public function users()
    {
        return $this->belongsToMany(User::class, 'role_users', 'roleId', 'userId');
    }

    public function permissions()
    {
        return $this->belongsToMany(Permission::class, 'role_permissions', 'roleId', 'permissionId');
    }

    public function uniteDeGestionUsers()
    {
        return $this->hasMany(UniteDeGestionUser::class, 'roleId');
    }

    public function missionDeControleUsers()
    {
        return $this->hasMany(MissionDeControleUser::class, 'roleId');
    }

    public function roleable()
    {
        return $this->morphTo();
    }

    public function programme()
    {
        return $this->belongsTo(Programme::class, 'programmeId');
    }

    /**
     *
     *
     * @param  string  $value
     * @return void
     */
    public function setNomAttribute($value)
    {
        $this->attributes['nom'] = addslashes($value); // Escape value with backslashes
        $this->attributes['slug'] = str_replace(' ', '-', strtolower($value));
    }

    /**
    *
    * @param  string  $value
    * @return string
    */
    public function getNomAttribute($value){
        return ucfirst(str_replace('\\',' ',$value));
    }

}
