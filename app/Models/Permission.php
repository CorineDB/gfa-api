<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

use SaiAshirwadInformatia\SecureIds\Models\Traits\HasSecureIds;

class Permission extends Model
{
    use HasFactory, HasSecureIds;

    protected $table = "permissions";

    /* Les attributs qui sont assignés en masse */
    protected $fillable = [
        'nom',
        'slug',
        'description'
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        /* 'slug',  */'pivot', 'created_at', 'updated_at', 'deleted_at'
    ];

    /* Les roles ayant cette permission */
    public function roles()
    {
        return $this->belongsToMany(Role::class, 'role_permissions', 'permissionId', 'roleId')
                    ->withTimestamps();
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
