<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

use SaiAshirwadInformatia\SecureIds\Models\Traits\HasSecureIds;

class Passation extends Model
{

    protected $table = 'passations';
    public $timestamps = true;

    use HasSecureIds ;

    protected $dates = ['deleted_at'];
    protected $fillable = array('montant', 'dateDeSignature', 'dateDobtention', 'dateDeDemarrage', 'datePrevisionnel', 'dateDobtentionAvance', 'montantAvance', 'ordreDeService', 'responsableSociologue', 'estimation', 'travaux', 'entrepriseExecutantId', 'passationable_type', 'siteId', 'passationable_id', 'programmeId');

    public function site()
    {
        return $this->belongsTo(Site::class, 'siteId');
    }

    public function programme()
    {
        return $this->belongsTo(Programme::class, 'programmeId');
    }

    public function entrepriseExecutant()
    {
        return $this->belongsTo(EntrepriseExecutant::class, 'entrepriseExecutantId');
    }

    public function passationable()
    {
        return $this->morphTo();
    }

}
