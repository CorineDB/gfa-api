<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use SaiAshirwadInformatia\SecureIds\Models\Traits\HasSecureIds;

class Rappel extends Model
{
    use HasFactory, HasSecureIds;

    protected $fillable = array('nom', 'description', 'frequence', 'dateAvant', 'userId', 'programmeId', 'statut');

    public function user()
    {
        return $this->belongsTo(User::class, 'userId');
    }

    public function programme()
    {
        return $this->belongsTo(Programme::class, 'programmeId');
    }

    public function statuts()
    {
        return $this->morphMany(Statut::class, 'statuttable');
    }
}
