<?php

namespace App\Models;

use App\Traits\Helpers\Pta;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use SaiAshirwadInformatia\SecureIds\Models\Traits\HasSecureIds;

class Tache extends Model
{
    protected $table = 'taches';
    public $timestamps = true;

    use HasSecureIds, SoftDeletes, HasFactory, Pta;

    protected $dates = ['deleted_at'];
    protected $fillable = array('nom', 'position', 'poids', 'activiteId', 'programmeId', 'description', 'statut');

    protected static function boot()
    {
        parent::boot();

        static::deleting(function ($tache) {
            // Vérifier si la tâche a des suivis physiques
            $suiviExists = \App\Models\Suivi::where('suivitable_type', \App\Models\Tache::class)
                ->where('suivitable_id', $tache->id)
                ->exists();

            if ($suiviExists) {
                throw new \Exception(
                    "Impossible de supprimer cette tâche car elle a déjà fait l'objet d'un suivi physique (TEP)",
                    403
                );
            }
        });

        static::deleted(function ($tache) {
            if (optional($tache->statuts->last())->etat !== -2) {
                if ($tache->activite) {
                    $tache->rangement($tache->activite->taches->where('position', '>', $tache->position));
                }
            }
        });
    }

    public function projet()
    {
        return $this->activite->projet();

        while ($composante->composante) {
            $composante = $composante->composante;
        }

        return $composante->projet();
    }

    public function activite()
    {
        return $this->belongsTo(Activite::class, 'activiteId');
    }

    public function programme()
    {
        return $this->belongsTo(Programme::class, 'programmeId');
    }

    public function durees()
    {
        return $this->morphMany(Duree::class, 'dureeable');
    }

    public function suivis()
    {
        return $this->morphMany(Suivi::class, 'suivitable');
    }

    public function suivi()
    {
        return $this->suivis()->get()->last();
    }

    public function fichiers()
    {
        return $this->morphMany(Fichier::class, 'fichiertable');
    }

    public function commentaires()
    {
        return $this->morphMany(Commentaire::class, 'commentable');
    }

    public function statuts()
    {
        return $this->morphMany(Statut::class, 'statuttable');
    }

    public function getStatusAttribute()
    {
        $statut = $this->statuts->last();

        $statut = $statut ? $statut : $this->statuts()->create(['etat' => -1]);

        if ($statut['etat'] > -2 && $this->position == 0) {
            $this->position = $this->position($this->activite, 'taches');
            $this->save();
        }

        return $statut ? $statut['etat'] : null;
    }

    public function verifiePlageDuree(string $debut, string $fin, Activite $activite = null)
    {
        // Use Carbon to parse the dates for accurate comparison
        $debutDate = Carbon::parse($debut);
        $finDate = Carbon::parse($fin);

        if (!$activite) {
            $activite = $this->activite;
        }

        // Check if there exists any duration where the task's dates fit within one of the activity's date ranges
        return $activite
            ->durees()
            ->where(function ($query) use ($debutDate, $finDate) {
                // Check if the task's start date and end date fall within any of the ranges
                $query->where(function ($subQuery) use ($debutDate, $finDate) {
                    $subQuery
                        ->where('debut', '<=', $debutDate)
                        ->where('fin', '>=', $finDate);
                });
            })
            ->exists();  // Return true if such a range exists
    }

    public function getDureeAttribute()
    {
        $duree = $this->durees->first();
        $min = strtotime($duree->debut) - strtotime('1970-01-01');

        foreach ($this->durees as $d) {
            $dif = strtotime(date('Y-m-d')) - strtotime($d->debut);

            if ($dif <= $min) {
                $min = $dif;
                $duree = $d;
            }
        }

        return $duree;
    }

    public function getDureeActiviteAttribute()
    {
        return new Duree(['debut' => $this->durees->first()->debut, 'fin' => $this->durees->last()->fin]);
    }

    public function getDebutAttribute()
    {
        if ($this->duree)
            return $this->duree->debut;
    }

    public function getFinAttribute()
    {
        if ($this->duree)
            return $this->duree->fin;
    }

    public function getTepAttribute()
    {
        $suivi = $this->suivis->last();

        if (!$suivi)
            return 0;

        return $suivi->poidsActuel;
        return (optional($suivi)->poidsActuel * 100) / $this->poids;
    }

    public function terminer()
    {
        $etat = ['etat' => 2];
        $suivi = ['poidsActuel' => $this->poids];

        $etats = $this->statuts()->create($etat);
        $suivi = $this->suivis()->create($suivi);
    }

    public function getCodePtaAttribute()
    {
        $activite = $this->activite;
        if ($this->statut != -2 && $this->position == 0) {
            $this->position = max($this->activite->taches->pluck('position')->toArray()) + 1;
            $this->save();
        }
        return '' . optional($this->activite)->codePta . '.' . $this->position;
    }

    public function getBailleurAttribut()
    {
        return $this->activite->bailleur;
    }
}
