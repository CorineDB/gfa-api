<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

use SaiAshirwadInformatia\SecureIds\Models\Traits\HasSecureIds;

class Code extends Model
{
    use HasSecureIds, HasFactory ;

    protected $table = 'codes';
    protected $fillable = array('codePta', 'programmeId', 'bailleurId');

    public function programme()
    {
        return $this->belongsTo(Programme::class, 'programmeId');
    }

    public function bailleur()
    {
        return $this->belongsTo(Bailleur::class, 'bailleurId');
    }
}
