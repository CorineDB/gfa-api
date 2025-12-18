<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

use SaiAshirwadInformatia\SecureIds\Models\Traits\HasSecureIds;

class BailleurSite extends Model
{
    use HasSecureIds, HasFactory ;

    protected $table = 'bailleur_sites';

    public $timestamps = true;

    protected $dates = ['deleted_at'];

    protected $fillable = ['bailleurId', 'programmeId', 'siteId'];

    public function programme()
    {
        return $this->belongsTo(Programme::class, 'programmeId');
    }

    public function bailleur()
    {
        return $this->belongsTo(Bailleur::class, 'bailleurId');
    }

    public function site()
    {
        return $this->belongsTo(Site::class, 'siteId');
    }
}
