<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use SaiAshirwadInformatia\SecureIds\Models\Traits\HasSecureIds;

class AlerteConfig extends Model
{
    use HasFactory, HasSecureIds;

    protected $fillable = ['module', 'nombreDeJourAvant', 'frequence', 'debutSuivi', 'frequenceRapport'];
}
