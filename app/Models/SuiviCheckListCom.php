<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

use SaiAshirwadInformatia\SecureIds\Models\Traits\HasSecureIds;

class SuiviCheckListCom extends Model
{
    use HasSecureIds, HasFactory ;

    protected $table = 'com_suivis';

    public $timestamps = true;

    protected $dates = ['deleted_at'];

    protected $fillable = array('valeur', 'mois', 'annee', 'responsable_enquete', 'checkListComId');

    public function checkListCom()
    {
        return $this->belongsTo(CheckListCom::class, 'checkListComId');
    }

    public function commentaires()
    {
        return $this->morphMany(Commentaire::class, 'commentable');
    }
}
