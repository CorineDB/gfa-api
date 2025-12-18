<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use SaiAshirwadInformatia\SecureIds\Models\Traits\HasSecureIds;

class Audit extends Model
{
    use HasFactory, HasSecureIds;

    protected $fillable = [
        'annee',
        'entreprise',
        'entrepriseContact',
        'dateDeTransmission',
        'etat',
        'statut',
        'projetId', 'programmeId',
        'categorie'
    ];

    public function projet()
    {
        return $this->belongsTo(Projet::class, 'projetId');
    }

    public function programme()
    {
        return $this->belongsTo(Programme::class, 'programmeId');
    }

    public function fichiers()
    {
        return $this->morphMany(Fichier::class, 'fichiertable');
    }
}
