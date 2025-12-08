<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use SaiAshirwadInformatia\SecureIds\Models\Traits\HasSecureIds;

class PlanDecaissement extends Model
{
    use HasSecureIds, SoftDeletes, HasFactory;

    protected $table = 'plan_de_decaissements';
    public $timestamps = true;
    protected $dates = ['deleted_at'];
    protected $fillable = ['trimestre', 'annee', 'pret', 'budgetNational', 'activiteId', 'programmeId'];

    protected static function boot()
    {
        parent::boot();

        static::deleting(function ($planDecaissement) {
            // Vérifier s'il existe un suivi financier pour cette activité avec le même trimestre et la même année
            $suiviFinancierExists = \App\Models\SuiviFinancier::where('activiteId', $planDecaissement->activiteId)
                ->where('trimestre', $planDecaissement->trimestre)
                ->where('annee', $planDecaissement->annee)
                ->exists();

            if ($suiviFinancierExists) {
                throw new \Exception(
                    "Impossible de supprimer ce plan de décaissement car il a déjà fait l'objet d'un suivi financier pour le trimestre {$planDecaissement->trimestre} de l'année {$planDecaissement->annee}",
                    403
                );
            }
        });
    }

    public function activite()
    {
        return $this->belongsTo(Activite::class, 'activiteId');
    }

    public function programme()
    {
        return $this->belongsTo(Programme::class, 'programmeId');
    }
}
