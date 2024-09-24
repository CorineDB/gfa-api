<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

use SaiAshirwadInformatia\SecureIds\Models\Traits\HasSecureIds;

class ArchiveDecaissement extends Model
{
    use HasSecureIds, HasFactory;


    protected $table = 'archive_decaissements';

    protected $dates = ['deleted_at'];
    protected $fillable = array('projetId', 'montant', 'date', 'morphable_type', 'morphable_id', 'userId');

    public function decaissementable()
    {
        return $this->morphTo();
    }

    public function projet()
    {
        return $this->belongsTo(ArchiveProjet::class, 'projetId');
    }

    public function auteur()
    {
        return $this->belongsTo(User::class, 'userId');
    }

    public function commentaires()
    {
        return $this->morphMany(Commentaire::class, 'commentable');
    }
}
