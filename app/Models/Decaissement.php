<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

use SaiAshirwadInformatia\SecureIds\Models\Traits\HasSecureIds;

class Decaissement extends Model
{
    use HasSecureIds, HasFactory;

    protected $dates = ['deleted_at'];
    protected $fillable = array('projetId', 'montant', 'date', 'decaissementable_type', 'decaissementable_id', 'programmeId', 'userId', "methodeDePaiement", "beneficiaireId");

    public function decaissementable()
    {
        return $this->morphTo();
    }

    public function projet()
    {
        return $this->belongsTo(Projet::class, 'projetId');
    }

    public function auteur()
    {
        return $this->belongsTo(User::class, 'userId');
    }

    public function programme()
    {
        return $this->belongsTo(User::class, 'programmeId');
    }

    public function beneficiaire()
    {
        return $this->belongsTo(EntrepriseExecutant::class, 'beneficiaireId');
    }

    public function commentaires()
    {
        return $this->morphMany(Commentaire::class, 'commentable');
    }
}
