<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use SaiAshirwadInformatia\SecureIds\Models\Traits\HasSecureIds;

class ActionAMener extends Model
{
    protected $table = 'actions_a_mener';
    public $timestamps = true;

    use HasSecureIds, HasFactory ;

    protected $dates = ['deleted_at'];

    protected $fillable = array("action", "statut", "est_valider", "validated_at", "start_at", "end_at", "actionable_id", "actionable_type", 'programmeId');

    protected $casts = [
        "start_at" => "datetime",
        "end_at" => "datetime",
        "statut" => "integer",
        "est_valider" =>"boolean",
        "validated_at" => "datetime",
    ];

    protected static function boot()
    {
        parent::boot();
    }

    public function actionable()
    {
        return $this->morphTo();
    }

    public function preuves_de_verification()
    {
        return $this->morphMany(Fichier::class, "fichiertable");
    }
}
