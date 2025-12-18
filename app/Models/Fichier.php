<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

use SaiAshirwadInformatia\SecureIds\Models\Traits\HasSecureIds;

class Fichier extends Model
{

    protected $table = 'fichiers';
    public $timestamps = true;

    use HasSecureIds ;

    protected $dates = ['deleted_at'];
    protected $fillable = array('nom', 'chemin', 'description', 'sharedId', 'fichiertable_type', 'fichiertable_id', 'auteurId');

    public function fichiertable()
    {
        return $this->morphTo();
    }

    public function auteur()
    {
        return $this->belongsTo(User::class, 'auteurId');
    }

}
