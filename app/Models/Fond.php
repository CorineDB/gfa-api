<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use SaiAshirwadInformatia\SecureIds\Models\Traits\HasSecureIds;

class Fond extends Model
{
    protected $table = 'fonds';
    public $timestamps = true;

    use HasSecureIds, HasFactory ;

    protected $dates = ['deleted_at'];

    protected $fillable = array('nom_du_fond', 'fondDisponible', 'programmeId');

    protected $casts = ['fondDisponible' => 'integer'];

    protected static function boot()
    {
        parent::boot();
    }

    public function organisations()
    {
        return $this->belongsToMany(Organisation::class,'fond_organisations', 'fondId', 'organisationId')->wherePivotNull('deleted_at')->withPivot(["id", "budgetAllouer"]);
    }
}
