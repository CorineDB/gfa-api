<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\DB;
use SaiAshirwadInformatia\SecureIds\Models\Traits\HasSecureIds;
use Exception;

class Suivi extends Model
{
    use HasSecureIds, SoftDeletes, HasFactory;

    protected $table = 'suivis';
    public $timestamps = true;
    protected $dates = ['deleted_at'];
    protected $fillable = array('poidsActuel', 'commentaire', 'suivitable_id', 'suivitable_type', 'programmeId');

    protected static function boot()
    {
        parent::boot();

        static::created(function ($suivi) {
            DB::beginTransaction();
            try {
                $morphisme = $suivi->suivitable;

                if ($morphisme instanceof Tache) {
                    $activite = $morphisme->activite;

                    // if (in_array([0, 1], $activite->statut)) {

                    /* $totalPoids = $activite->taches->sum("poids");

                    $totalPoidsActuel = $activite->taches->load('suivis')->pluck('suivis')->map(function ($suivi) {
                        $lastPoid = $suivi->last();
                        if ($lastPoid) {
                            return $lastPoid->poidsActuel;
                        }
                        return 0;
                    })->sum();

                    $poidsActuel = ($activite->poids * $totalPoidsActuel) / $totalPoids; */

                    $totalPoidsActuel = $activite->taches->map(function ($tache) {
                        $lastPoid = $tache->suivi();  // Assuming suivi() returns a numeric value
                        if ($lastPoid) {
                            return $lastPoid->poidsActuel;
                        }
                        return 0;
                    })->sum();

                    $poidsActuel = $activite->taches->count() > 0 ? $totalPoidsActuel / $activite->taches->count() : 0;

                    $activite->suivis()->create(['poidsActuel' => $poidsActuel]);

                    $activite = $activite->fresh();

                    /*
                     * if ($activite->poids === $activite->poidsActuel)
                     *     $activite->statuts()->create(['etat' => 2]);
                     */

                    if ($activite->poidsActuel === 100) {
                        $activite->statut = 2;
                        $activite->save();
                        $activite->statuts()->create(['etat' => 2]);
                    }
                    // }
                } elseif ($morphisme instanceof Activite) {
                    $composante = $morphisme->composante;

                    // if (in_array([0, 1], $composante->statut)) {

                    $totalPoids = 0;

                    $totalPoidsActuel = 0;

                    $tacheCount = 0;

                    if ($composante->composanteId !== null) {
                        foreach ($composante->sousComposantes as $sousComposante) {
                            foreach ($sousComposante->activites as $activite) {
                                /* $totalPoids += $activite->taches->sum("poids");

                                $totalPoidsActuel += $activite->taches->load('suivis')->pluck('suivis')->map(function ($suivi) {
                                    $lastPoid = $suivi->last();
                                    if ($lastPoid) {
                                        return $lastPoid->poidsActuel;
                                    }
                                    return 0;
                                })->sum(); */

                                $totalPoidsActuel += $activite->taches->map(function ($tache) {
                                    $lastPoid = $tache->suivi();  // Assuming suivi() returns a numeric value
                                    if ($lastPoid) {
                                        return $lastPoid->poidsActuel;
                                    }
                                    return 0;
                                })->sum();

                                $tacheCount += $activite->taches->count();
                            }
                        }
                    }

                    foreach ($composante->activites as $activite) {
                        /*$totalPoids += $activite->taches->sum("poids");

                        $totalPoidsActuel += $activite->taches->load('suivis')->pluck('suivis')->map(function ($suivi) {
                            $lastPoid = $suivi->last();
                            if ($lastPoid) {
                                return $lastPoid->poidsActuel;
                            }
                            return 0;
                        })->sum();*/

                        $totalPoidsActuel += $activite->taches->map(function ($tache) {
                            $lastPoid = $tache->suivi();  // Assuming suivi() returns a numeric value
                            if ($lastPoid) {
                                return $lastPoid->poidsActuel;
                            }
                            return 0;
                        })->sum();

                        $tacheCount += $activite->taches->count();
                    }

                    // $poidsActuel = ($composante->poids * $totalPoidsActuel) / $totalPoids;

                    $poidsActuel = $tacheCount > 0 ? $totalPoidsActuel / $tacheCount : 0;

                    $suivi = $composante->suivis()->create(['poidsActuel' => $poidsActuel]);

                    $composante = $composante->fresh();

                    /* if ($composante->poids === $composante->poidsActuel){
                        $composante->statuts()->create(['etat' => 2]);
                    } */

                    if ($composante->poidsActuel === 100) {
                        $composante->statut = 2;
                        $composante->save();
                        $composante->statuts()->create(['etat' => 2]);
                    }
                    // }
                }

                DB::commit();
            } catch (\Throwable $th) {
                DB::rollBack();

                throw new Exception($th->getMessage(), 1);
            }
        });
    }

    public function suivitable()
    {
        return $this->morphTo();
    }

    public function programme()
    {
        return $this->belongsTo(Programme::class, 'programmeId');
    }
}
