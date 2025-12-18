<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use SaiAshirwadInformatia\SecureIds\Models\Traits\HasSecureIds;

class Reponse extends Model
{
    use HasFactory, HasSecureIds;

    protected $fillable = array('valeur', 'userId', 'questionId', 'formulaireId', 'date', 'shared');

    public function user()
    {
        return $this->belongsTo(User::class, 'userId');
    }

    public function question()
    {
        return $this->belongsTo(Question::class, 'questionId');
    }

    public function formulaire()
    {
        return $this->belongsTo(Formulaire::class, 'formulaireId');
    }
}
