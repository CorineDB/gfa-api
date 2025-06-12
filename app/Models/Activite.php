<?php

namespace App\Models;

use App\Events\NewNotification;
use App\Jobs\ChangementStatutJob;
use App\Notifications\ChangementStatutNotification;
use App\Traits\Helpers\HelperTrait;
use App\Traits\Helpers\Pta;
use DateTime;
use Exception;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\DB;
use SaiAshirwadInformatia\SecureIds\Models\Traits\HasSecureIds;

class Activite extends Model
{
    use HasSecureIds, HasFactory, Pta, HelperTrait;

    protected $table = 'activites';

    public $timestamps = true;

    protected $fillable = ['nom', 'position', 'poids', 'type', 'pret', 'budgetNational', 'userId', 'composanteId', 'statut', 'programmeId'];

    protected $appends = ['consommer'];

    protected static function boot() {
        parent::boot();

        static::deleted(function($activite) {
            DB::beginTransaction();
            try {

                if(optional($activite->statuts->last())->etat !== -2)
                {
                    if($activite->composante){
                        $activite->rangement($activite->composante->activites->where("position", ">", $activite->position ));
                    }

                }

                $activite->taches()->delete();
                $activite->suivis()->delete();
                $activite->durees()->delete();
                $activite->statuts()->delete();
                $activite->fichiers()->delete();
                $activite->commentaires()->delete();
                $activite->planDeDecaissements()->delete();
                $activite->suiviFinanciers(null, null)->delete();

                DB::commit();
            } catch (\Throwable $th) {
                DB::rollBack();

                throw new Exception($th->getMessage(), 1);

            }
        });
    }

    public function responsable()
    {
        return $this->belongsTo(User::class, 'userId');
    }

    public function programme()
    {
        return $this->belongsTo(Programme::class, 'programmeId');
    }

    public function structures()
    {
        return $this->belongsToMany(User::class,'activite_users', 'activiteId', 'userId');
    }

    public function structureResponsable()
    {
        return $this->belongsToMany(User::class,'activite_users', 'activiteId', 'userId')->wherePivot('type', 'Responsable')->first();
    }

    public function structureAssociee()
    {
        return $this->belongsToMany(User::class,'activite_users', 'activiteId', 'userId')->wherePivot('type', 'Associée')->first();
    }

    public function projet()
    {
        $composante = $this->composante;

        while ($composante->composante) {
           $composante = $composante->composante;
        }

        return $composante->projet();
    }

    public function composante()
    {
        return $this->belongsTo(Composante::class, 'composanteId');
    }

    public function suivis()
    {
        return $this->morphMany(Suivi::class, 'suivitable');
    }

    public function suivi()
    {
        return $this->morphMany(Suivi::class, 'suivitable')->last();
    }

    public function durees()
    {
        return $this->morphMany(Duree::class, 'dureeable');
    }

    public function fichiers()
    {
        return $this->morphMany(Fichier::class, 'fichiertable');
    }

    public function commentaires()
    {
        return $this->morphMany(Commentaire::class, 'commentable');
    }

    public function taches()
    {
        return $this->hasMany(Tache::class, 'activiteId')->orderBy('position', 'asc');
    }

    public function planDeDecaissements()
    {
        return $this->hasMany(PlanDecaissement::class, 'activiteId');
    }

    public function planDeDecaissement($trimestre = null, $annee = null)
    {
        $plan = $this->planDeDecaissements()->when($trimestre != null, function($query) use ($trimestre){
            $query->where('trimestre', $trimestre);
        })->when($annee != null, function($query) use ($annee){
            $query->where('annee', $annee);
        })->first();

        if($plan)
            return ['pret' => $plan->pret,
                'budgetNational' => $plan->budgetNational];

        return ['pret' => 0,
                'budgetNational' => 0];
    }

    public function planDeDecaissementParAnnee($annee = null)
    {
        $plans = $this->planDeDecaissements()->when($annee != null, function($query) use ($annee) {
            $query->where('annee', $annee);
        })->get();

        $pret = 0;
        $budgetNational = 0;

        if($plans->count() > 0){
            $pret = $plans->sum('pret');
            $budgetNational = $plans->sum('budgetNational');
        }
        return ['pret' => $pret,
                'budgetNational' => $budgetNational];
    }

    public function suiviFinanciers($annee = null, $type= null)
    {
        if(!isset($annee))
        {
            if($type == null)
            {
                return $this->hasMany(SuiviFinancier::class, 'activiteId');
            }
            return $this->hasMany(SuiviFinancier::class, 'activiteId')
                        ->where('suivi_financierable_type', $type)->get();
        }

        if($type == null)
        {
            return $this->hasMany(SuiviFinancier::class, 'activiteId')
                        ->where('annee', $annee)->get();
        }
        return $this->hasMany(SuiviFinancier::class, 'activiteId')
                        ->where('annee', $annee)
                        ->where('suivi_financierable_type', $type)->get();
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

        if($statut['etat'] > -2 && $this->position == 0)
        {
            $this->position = $this->position($this->composante, 'activites');
            $this->save();
        }

        if($statut && $statut['etat'] > -2)
        {

            foreach($this->taches as $tache)
            {
                if($tache->statut != 2)
                {
                    $controle = 0;
                    break;
                }

                else $controle = 2;
            }
        }

        if($controle == 2)
        {
            $statut = $this->statuts()->create(['etat' => 2]);

        }

        else if($controle == 1 || $controle == 0)
        {
            $fin = $this->duree->fin;
            $debut = $this->duree->debut;

            if(($statut && $statut['etat'] == -1 || $statut['etat'] == 2) && $debut <= date('Y-m-d'))
            {
                $etat = ['etat' => 0];
                $statut = $this->statuts()->create($etat);

            }

            else if($statut && $statut['etat'] < 1 && $statut['etat'] != -2 && $fin < date('Y-m-d'))
            {
                $etat = ['etat' => 1];
                $statut = $this->statuts()->create($etat);

            }

            else if($statut && $statut['etat'] == 1 && $fin > date('Y-m-d'))
            {
                $etat = ['etat' => 0];
                $statut = $this->statuts()->create($etat);
            }

        }

        return $statut ? $statut['etat'] : null;
    }

    public function getCodePtaAttribute()
    {
        $composante = $this->composante;
        if($this->statut != -2 && $this->position == 0)
        {
            $this->position = max($this->composante->activites->pluck('position')->all()) + 1;
            $this->save();
        }
        return ''.optional($this->composante)->codePta.'.'.$this->position;
    }

    public function getDureeAttribute()
    {

        $today = new DateTime();
        $nextDuree = null;
        $nextStart = null;

        foreach ($this->durees as $duree) {
            $debut = new DateTime($duree->debut);
            $fin = new DateTime($duree->fin);

            // Check if currently active
            if ($today >= $debut && $today <= $fin) {
                return $duree;
            }

            // Otherwise, track next upcoming duree (starting after today)
            if ($debut > $today) {
                if ($nextStart === null || $debut < $nextStart) {
                    $nextStart = $debut;
                    $nextDuree = $duree;
                }
            }
        }

        // Return next upcoming duree if no active one found, or null if none
        return $nextDuree;

        $duree = $this->durees->first();
        $min = strtotime($duree->debut) - strtotime('1970-01-01');

        foreach($this->durees as $d)
        {
            $dif = strtotime(date('Y-m-d')) - strtotime($d->debut);

            if($dif <= $min)
            {
                $min = $dif;
                $duree = $d;
            }

        }

        return $duree;
    }

    public function getDureeActiviteAttribute()
    {
        return new Duree(["debut" => $this->durees->first()->debut, "fin" => $this->durees->last()->fin]);
    }

    public function getTepAttribute()
    {
        $count = $this->taches->count();
        return $count > 0
            ? $this->taches->map(fn($tache) => $tache->tep)->sum() / $count
            : 0; // Or any default value

        $taches = $this->taches;
        $somme = 0;
        $sommeActuel = 0;

        if(count($taches))
        {
            foreach($taches as $tache)
            {
                if($tache->statut == 2)
                {
                    $sommeActuel += $tache->poids;
                }
                $somme += $tache->poids;
            }
        }

        if(!$somme && $this->statut != 2) return 0;

        else if($this->statut == 2) return $this->poids;

        return ($sommeActuel * 100) / $somme;
    }

    public function getTefAttribute()
    {
        $total = $this->planDeDecaissements->sum('pret') + $this->planDeDecaissements->sum('budgetNational');
        return $total ? ($this->consommer(null, null) * 100) / $total : 0;
    }

    public function getPoidActuelAttribute()
    {
        $taches = $this->taches;
        $somme = 0;
        $sommeActuel = 0;

        if($taches)
        {
            foreach($taches as $tache)
            {
                if($tache->statut == 2)
                {
                    $sommeActuel += $tache->poids;
                }
                $somme += $tache->poids;
            }
        }

        if(!$somme && $this->statut != 2) return 0;

        else if($this->statut == 2) return $this->poids;

        return ($sommeActuel * $this->poids) / $somme;
    }

    public function getConsommerAttribute($annee = null, $type = null)
    {
        $suiviFinanciers = $this->suiviFinanciers($annee, $type)->pluck('consommer');

        return array_sum($suiviFinanciers->all());
    }

    public function consommer($annee = null, $type = null)
    {
        $suiviFinanciers = $this->suiviFinanciers($annee, $type)->pluck('consommer');

        return array_sum($suiviFinanciers->all());
    }

    public function tef()
    {
        $total = $this->planDeDecaissements->sum('pret') + $this->planDeDecaissements->sum('budgetNational');
        return $total ? ($this->consommer(null, null) * 100) / $total : 0;
    }

    public function terminer()
    {
        $etat = ['etat' => 2];
        $suivi = ['poidsActuel' => $this->poids];

        $etats = $this->statuts()->create($etat);
        $suivi = $this->suivis()->create($suivi);

        $taches = $this->taches;

        foreach($taches as $tache)
        {
            $tache->terminer();
        }
    }

    public function getBailleurAttribut()
    {
        return $this->composante->bailleur;
    }

}
