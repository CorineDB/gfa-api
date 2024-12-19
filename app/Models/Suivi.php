<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

use SaiAshirwadInformatia\SecureIds\Models\Traits\HasSecureIds;

class Suivi extends Model
{
    use HasSecureIds, HasFactory ;

    protected $table = 'suivis';
    public $timestamps = true;

    protected $dates = ['deleted_at'];

    protected $fillable = array('poidsActuel', 'commentaire', 'suivitable_id', 'suivitable_type');

    protected static function boot()
    {
        parent::boot();

        static::created(function ($suivi) {

            $morphisme =  $suivi->suivitable;

            if ($morphisme instanceof Tache) {

                $activite = $morphisme->activite;

                //if (in_array([0, 1], $activite->statut)) {

                    $totalPoids = $activite->taches->sum("poids");

                    $totalPoidsActuel = $activite->taches->load('suivis')->pluck('suivis')->map(function ($suivi) {
                        $lastPoid = $suivi->last();
                        if ($lastPoid) {
                            return $lastPoid->poidsActuel;
                        }
                        return 0;
                    })->sum();

                    $poidsActuel = ($activite->poids * $totalPoidsActuel) / $totalPoids;

                    $activite->suivis()->create(["poidsActuel" => $poidsActuel]);

                    $activite = $activite->fresh();

                    if ($activite->poids === $activite->poidsActuel)
                        $activite->statuts()->create(['etat' => 2]);
                //}

            } elseif ($morphisme instanceof Activite) {

                $composante = $morphisme->composante;

                //if (in_array([0, 1], $composante->statut)) {

                    $totalPoids = 0;

                    $totalPoidsActuel = 0;

                    if ($composante->composanteId !== null) {

                        foreach ($composante->sousComposantes as $sousComposante) {
                            foreach ($sousComposante->activites as $activite) {

                                $totalPoids += $activite->taches->sum("poids");

                                $totalPoidsActuel += $activite->taches->load('suivis')->pluck('suivis')->map(function ($suivi) {
                                    $lastPoid = $suivi->last();
                                    if ($lastPoid) {
                                        return $lastPoid->poidsActuel;
                                    }
                                    return 0;
                                })->sum();
                            }
                        }
                    }

                    foreach ($composante->activites as $activite) {

                        $totalPoids += $activite->taches->sum("poids");


                        $totalPoidsActuel += $activite->taches->load('suivis')->pluck('suivis')->map(function ($suivi) {
                            $lastPoid = $suivi->last();
                            if ($lastPoid) {
                                return $lastPoid->poidsActuel;
                            }
                            return 0;
                        })->sum();
                    }

                    $poidsActuel = ($composante->poids * $totalPoidsActuel) / $totalPoids;

                    $suivi = $composante->suivis()->create(["poidsActuel" => $poidsActuel]);

                    $composante = $composante->fresh();

                    if ($composante->poids === $composante->poidsActuel)
                        $composante->statuts()->create(['etat' => 2]);
                //}

            }
        });
    }

    public function suivitable()
    {
        return $this->morphTo();
    }
}
