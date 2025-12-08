<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use SaiAshirwadInformatia\SecureIds\Models\Traits\HasSecureIds;

class EmailRapport extends Model
{
    use HasFactory, HasSecureIds;

    protected $fillable = array('objet', 'rapport', 'destinataires', 'userId', 'programmeId');

    public function auteur()
    {
        return $this->belongsTo(User::class, 'userId');
    }
    public function programme()
    {
        return $this->belongsTo(Programme::class, 'programmeId');
    }

    public function preuve()
    {
        return $this->morphOne(Fichier::class, 'fichiertable');
    }
}
