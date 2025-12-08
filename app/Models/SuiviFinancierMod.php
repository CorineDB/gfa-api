<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

use SaiAshirwadInformatia\SecureIds\Models\Traits\HasSecureIds;

class SuiviFinancierMod extends Model
{

    protected $table = 'suivi_financier_mods';
    public $timestamps = true;

    use HasSecureIds, HasFactory ;

    protected $dates = ['deleted_at'];
    protected $fillable = array('trimestre', 'annee', 'commentaire', 'decaissement', 'taux', 'maitriseDoeuvreId', 'siteId');

    public function site()
    {
        return $this->belongsTo(Site::class, 'siteId');
    }

    public function maitriseOeuvre()
    {
        return $this->belongsTo(MaitriseOeuvre::class, 'maitriseOeuvreId');
    }

    public function commentaires()
    {
        return $this->morphMany(Commentaire::class, 'commentable');
    }

}
