<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use SaiAshirwadInformatia\SecureIds\Models\Traits\HasSecureIds;

class Question extends Model
{
    use HasFactory, HasSecureIds;

    protected $fillable =['nom', 'inputType'];

    public function formulaires()
    {
        $this->belongsToMany(Formulaire::class, 'formulaire-questions', 'questionId', 'formulaireId');
    }
}
