<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

use SaiAshirwadInformatia\SecureIds\Models\Traits\HasSecureIds;

class Sinistre extends Model
{

    protected $table = 'sinistres';
    public $timestamps = true;

    use HasSecureIds, HasFactory ;

    protected $dates = ['deleted_at'];
    protected $fillable = array('nom', 'prenoms', 'contact', 'siteId', 'programmeId', 'rue', 'sexe', 'referencePieceIdentite', 'statut', 'modeDePaiement', 'longitude', 'latitude', 'montant', 'payer', 'dateDePaiement');

    public function proprietes()
    {
        return $this->hasMany(Propriete::class, 'sinistreId')->first();
    }

    public function site()
    {
        return $this->belongsTo(Site::class, 'siteId');
    }

    public function projet()
    {
        return $this->belongsTo(Projet::class, 'projetId');
    }

    public function programme()
    {
        return $this->belongsTo(Programme::class, 'programmeId');
    }


}
