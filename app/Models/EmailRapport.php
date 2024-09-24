<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use SaiAshirwadInformatia\SecureIds\Models\Traits\HasSecureIds;

class EmailRapport extends Model
{
    use HasFactory, HasSecureIds;

    protected $fillable = array('objet', 'rapport', 'destinataires', 'userId');

    public function auteur()
    {
        return $this->belongsTo(User::class, 'userId');
    }
}
