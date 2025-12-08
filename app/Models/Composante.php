<?php

namespace App\Models;

use App\Traits\Helpers\Pta;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\DB;
use SaiAshirwadInformatia\SecureIds\Models\Traits\HasSecureIds;
use Exception;

class Composante extends Model
{
    protected $table = 'composantes';
    public $timestamps = true;

    use Pta;
    use HasSecureIds, SoftDeletes, HasFactory;

    protected $dates = ['deleted_at'];

    protected $fillable = array('nom', 'position', 'poids', 'pret', 'budgetNational', 'description', 'projetId', 'programmeId', 'composanteId', 'statut');

    protected static function boot()
    {
        parent::boot();

        static::deleting(function ($composante) {
            // Validation 1: Vérifier si la composante ou ses activités ont des suivis financiers
            $suiviFinancierExists = false;

            // Vérifier les activités directes
            foreach ($composante->activites as $activite) {
                if (\App\Models\SuiviFinancier::where('activiteId', $activite->id)->exists()) {
                    $suiviFinancierExists = true;
                    break;
                }
            }

            // Vérifier les sous-composantes et leurs activités
            if (!$suiviFinancierExists) {
                foreach ($composante->sousComposantes as $sousComposante) {
                    foreach ($sousComposante->activites as $activite) {
                        if (\App\Models\SuiviFinancier::where('activiteId', $activite->id)->exists()) {
                            $suiviFinancierExists = true;
                            break 2;
                        }
                    }
                }
            }

            if ($suiviFinancierExists) {
                throw new \Exception(
                    "Impossible de supprimer cette composante car elle ou ses activités ont déjà fait l'objet d'un suivi financier",
                    403
                );
            }

            // Validation 2: Vérifier si la composante ou ses enfants ont des suivis physiques
            $suiviExists = \App\Models\Suivi::where('suivitable_type', \App\Models\Composante::class)
                ->where('suivitable_id', $composante->id)
                ->exists();

            if (!$suiviExists) {
                // Vérifier les activités et leurs tâches
                foreach ($composante->activites as $activite) {
                    if (\App\Models\Suivi::where('suivitable_type', \App\Models\Activite::class)
                            ->where('suivitable_id', $activite->id)
                            ->exists()) {
                        $suiviExists = true;
                        break;
                    }

                    foreach ($activite->taches as $tache) {
                        if (\App\Models\Suivi::where('suivitable_type', \App\Models\Tache::class)
                                ->where('suivitable_id', $tache->id)
                                ->exists()) {
                            $suiviExists = true;
                            break 2;
                        }
                    }
                }
            }

            if ($suiviExists) {
                throw new \Exception(
                    "Impossible de supprimer cette composante car elle ou ses activités/tâches ont déjà fait l'objet d'un suivi physique (TEP)",
                    403
                );
            }
        });

        static::deleted(function ($composante) {
            DB::beginTransaction();
            try {
                if (optional($composante->statuts->last())->etat !== -2) {
                    if ($composante->composante) {
                        $composante->rangement($composante->composante->sousComposantes->where('position', '>', $composante->position));
                    } else if ($composante->projet) {
                        $composante->rangement($composante->projet->composantes->where('position', '>', $composante->position));
                    }
                }

                $composante->statuts()->delete();

                $composante->sousComposantes()->delete();

                $composante->activites()->delete();

                $composante->suivis()->delete();

                $composante->fichiers()->delete();

                $composante->commentaires()->delete();

                DB::commit();
            } catch (\Throwable $th) {
                DB::rollBack();

                throw new Exception($th->getMessage(), 1);
            }
        });
    }

    public function projet()
    {
        return $this->belongsTo(Projet::class, 'projetId');
    }

    public function programme()
    {
        return $this->belongsTo(Programme::class, 'programmeId');
    }

    public function statuts()
    {
        return $this->morphMany(Statut::class, 'statuttable');
    }

    public function getStatusAttribute()
    {
        $controle = 1;
        $statut = $this->statuts->last();

        $statut = $statut ? $statut : $this->statuts()->create(['etat' => -1]);

        if ($statut['etat'] > -2 && $this->position == 0) {
            if ($this->composanteId == 0) {
                $this->position = $this->position($this->projet, 'composantes');
            } else {
                $this->position = $this->position($this->composante, 'sousComposantes');
            }

            $this->save();
        }

        if ($statut && $statut['etat'] > -2) {
            foreach ($this->activites as $activite) {
                if ($activite->statut != 2) {
                    $controle = 0;
                    break;
                } else
                    $controle = 2;
            }
        }

        if ($controle == 2) {
            $statut = $this->statuts()->create(['etat' => 2]);
        } else if ($controle == 1) {
            if ($statut && $statut['etat'] == -1) {
                $statut = $this->statuts()->create(['etat' => 0]);
            }
        }

        return $statut ? $statut['etat'] : null;
    }

    public function sousComposantes()
    {
        return $this->hasMany(Composante::class, 'composanteId')->orderBy('position', 'asc');
    }

    public function composante()
    {
        return $this->belongsTo(Composante::class, 'composanteId');
    }

    public function activites()
    {
        return $this->hasMany(Activite::class, 'composanteId')->orderBy('position', 'asc');
    }

    public function ppm()
    {
        return $this
            ->hasMany(Activite::class, 'composanteId')
            ->where('type', 'ppm')
            ->get();
    }

    public function suivis()
    {
        return $this->morphMany(Suivi::class, 'suivitable');
    }

    public function suivi()
    {
        return $this->morphMany(Suivi::class, 'suivitable')->last();
    }

    public function fichiers()
    {
        return $this->morphMany(Fichier::class, 'fichiertable');
    }

    public function commentaires()
    {
        return $this->morphMany(Commentaire::class, 'commentable');
    }

    public function getConsommerAttribute($annee = null, $type = null)
    {
        // Sum 'comsommer' for the current composante's activites
        $total = $this->activites->sum(function ($activite) {
            return $activite->consommer;
        });

        // Recursively sum 'comsommer' for all souscomposantes
        foreach ($this->souscomposantes as $souscomposante) {
            $total += $souscomposante->consommer;  // Recursive call
        }

        return $total;
    }

    private function calculateSousComposantesTep($sousComposantes)
    {
        if ($sousComposantes->isEmpty()) {
            return 0;
        }

        return $sousComposantes->map(function ($sousComposante) {
            // Get tep for the current sousComposante
            $currentTep = $sousComposante->tep;

            // Recursively calculate tep for nested sousComposantes
            $nestedTep = $this->calculateSousComposantesTep($sousComposante->sousComposantes);

            return $currentTep + $nestedTep;
        })->sum() / $sousComposantes->count();
    }

    private function calculateSousComposantesTef($sousComposantes)
    {
        if ($sousComposantes->isEmpty()) {
            return 0;
        }

        return $sousComposantes->map(function ($sousComposante) {
            // Get tef for the current sousComposante
            $currentTef = $sousComposante->tef;

            // Recursively calculate tef for nested sousComposantes
            $nestedTef = $this->calculateSousComposantesTef($sousComposante->sousComposantes);

            return $currentTef + $nestedTef;
        })->sum() / $sousComposantes->count();
    }

    public function getTepAttribute()
    {
        $activites = $this->activites;

        // Calculate tep for activites
        $activitesCount = $activites->count();
        $activitesTep = $activitesCount > 0
            ? $activites->map(fn($activite) => $activite->tep)->sum() / $activitesCount
            : 0;

        // Calculate tep for sousComposantes recursively
        $sousComposantesTep = $this->calculateSousComposantesTep($this->sousComposantes);

        // Aggregate the results
        return $activitesTep + $sousComposantesTep;

        $somme = 0;
        $sommeActuel = 0;

        if (count($activites)) {
            foreach ($activites as $activite) {
                $suivi = $activite->suivis->last();
                $somme += $activite->poids;
                $sommeActuel += optional($suivi)->poidsActuel;
            }
        }

        if (count($sousComposantes)) {
            foreach ($sousComposantes as $sousComposante) {
                $suivi = $sousComposante->suivis->last();
                $somme += $sousComposante->poids;
                $sommeActuel += optional($suivi)->poidsActuel;
            }
        }

        if (!$somme)
            return 0;

        return ($sommeActuel * 100) / $somme;
    }

    public function getTefAttribute()
    {
        $activites = $this->activites;

        // Calculate tep for activites
        $activitesCount = $activites->count();
        $activitesTef = $activitesCount > 0
            ? $activites->map(fn($activite) => $activite->tef)->sum() / $activitesCount
            : 0;

        // Calculate tep for sousComposantes recursively
        $sousComposantesTef = $this->calculateSousComposantesTef($this->sousComposantes);

        // Aggregate the results
        return $activitesTef + $sousComposantesTef;
    }

    public function sousComposanteTerminer()
    {
        $etat = ['etat' => 2];
        $suivi = ['poidsActuel' => $this->poids];

        $etats = $this->statuts()->create($etat);
        $suivi = $this->suivis()->create($suivi);

        $activites = $this->activites;

        foreach ($activites as $activite) {
            $activite->terminer();
        }
    }

    public function composanteTerminer()
    {
        $etat = ['etat' => 2];
        $suivi = ['poidsActuel' => $this->poids];

        $etats = $this->statuts()->create($etat);
        $suivi = $this->suivis()->create($suivi);

        $sousComposantes = $this->sousComposantes;

        foreach ($sousComposantes as $sousComposante) {
            $sousComposante->sousComposanteTerminer();
        }
    }

    public function getCodePtaAttribute()
    {
        if (!is_null($this->composanteId) && $this->composanteId !== 0 && $this->composante) {
            // Return composante-based code
            return $this->composante->codePta . '.' . $this->position;
        }

        if ($this->projet) {
            // Return projet-based code
            return $this->projet->codePta . '.' . $this->position;
        }

        return $this->position;

        if ($this->composanteId !== 0 || $this->composanteId !== NULL) {
            return '' . $this->composante->codePta . '.' . $this->position;
        }
        return '' . $this->projet->codePta . '.' . $this->position;
    }

    public function getBailleurAttribute()
    {
        if ($this->composanteId) {
            return $this->composante->bailleur;
        } else {
            return $this->projet->bailleur;
        }
    }

    public function planDeDecaissement($trimestre, $annee)
    {
        if ($this->composanteId) {
            $activites = $this->activites;
            $plan = [
                'pret' => 0,
                'budgetNational' => 0
            ];

            foreach ($activites as $activite) {
                $aplan = $activite->planDeDecaissement($trimestre, $annee);
                $plan['pret'] += optional($aplan)->pret;
                $plan['budgetNational'] += optional($aplan)->budgetNational;
            }
        } else {
            $scs = $this->sousComposantes;
            $plan = [
                'pret' => 0,
                'budgetNational' => 0
            ];

            foreach ($scs as $sc) {
                $scplan = $sc->planDeDecaissement($trimestre, $annee);
                $plan['pret'] += optional($scplan)->pret;
                $plan['budgetNational'] += optional($scplan)->budgetNational;
            }
        }

        return $plan;
    }

    public function planDeDecaissementParAnnee($annee)
    {
        $pret = 0;
        $budgetNational = 0;

        for ($i = 1; $i < 5; $i++) {
            $pret += $this->planDeDecaissement($i, $annee)['pret'];

            $budgetNational += $this->planDeDecaissement($i, $annee)['budgetNational'];
        }

        return ['pret' => $pret,
            'budgetNational' => $budgetNational];
    }
}
