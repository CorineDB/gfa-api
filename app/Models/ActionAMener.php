<?php

namespace App\Models;

use Exception;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
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

        static::deleted(function ($action_a_mener) {

            DB::beginTransaction();
            try {

                $action_a_mener->preuves_de_verification()->delete();

                DB::commit();
            } catch (\Throwable $th) {
                DB::rollBack();

                throw new Exception($th->getMessage(), 1);
            }
        });
    }

    public function actionable()
    {
        return $this->morphTo();
    }

    public function preuves_de_verification()
    {
        return $this->morphMany(Fichier::class, "fichiertable");
    }

    public function commentaires()
    {
        return $this->morphMany(Commentaire::class, 'commentable');
    }

    public function programme()
    {
        return $this->belongsTo(Programme::class, 'programmeId');
    }
}
