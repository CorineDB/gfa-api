<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use SaiAshirwadInformatia\SecureIds\Models\Traits\HasSecureIds;

class PtabScope extends Model
{
    use HasFactory, HasSecureIds ;

    protected $table = "ptab_scopes";

    /* Les attributs qui sont assignés en masse */
    protected $fillable = [
        'nom',
        'slug',
        'programmeId'
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'updated_at', 'deleted_at'
    ];

    /**
     * Liste des taches d'un scope
     *
     */
    public function archivesTache()
    {
        return $this->hasMany(ArchiveTache::class, 'scopeId');
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

    public function programme()
    {
        return $this->belongsTo(Programme::class, 'programmeId');
    }
}
