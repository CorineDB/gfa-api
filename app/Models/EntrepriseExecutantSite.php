<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class EntrepriseExecutantSite extends Model
{
    use HasFactory, HasFactory;

    protected $table = 'entreprise_executant_sites';

    public $timestamps = true;

    protected $dates = ['deleted_at'];

    protected $fillable = ['entrepriseExecutantId', 'programmeId', 'siteId'];

    public function programme()
    {
        return $this->belongsTo(Programme::class, 'programmeId');
    }

    public function entrepriseExecutant()
    {
        return $this->belongsTo(EntrepriseExecutant::class, 'entrepriseExecutantId');
    }

    public function site()
    {
        return $this->belongsTo(Site::class, 'siteId');
    }
}
