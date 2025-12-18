<?php

namespace App\Models;

use App\Events\NewNotification;
use App\Jobs\ChangementStatutJob;
use App\Notifications\ChangementStatutNotification;
use App\Traits\Helpers\HelperTrait;
use App\Traits\Helpers\Pta;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use SaiAshirwadInformatia\SecureIds\Models\Traits\HasSecureIds;

class ArchiveActivite extends Model
{
    use HasSecureIds, HasFactory , Pta, HelperTrait;

    protected $table = 'archive_activites';

    public $timestamps = true;

    protected $dates = ['deleted_at'];

    protected $fillable = ['nom', 'position', 'poids', 'type', 'pret', 'parentId', 'budgetNational', 'userId', 'composanteId', 'ptabScopeId', 'statut'];

    public function responsable()
    {
        return $this->belongsTo(User::class, 'userId');
    }

    public function structures()
    {
        return $this->belongsToMany(User::class,'archive_activite_users', 'activiteId', 'userId');
    }

    public function structureResponsable()
    {
        return $this->belongsToMany(User::class,'archive_activite_users', 'activiteId', 'userId')->wherePivot('type', 'Responsable')->first();
    }

    public function structureAssociee()
    {
        return $this->belongsToMany(User::class,'archive_activite_users', 'activiteId', 'userId')->wherePivot('type', 'Associée')->first();
    }

    public function ptabScope()
    {
        return $this->belongsTo(PtabScope::class, 'ptabScopeId');
    }

    public function composante()
    {
        return $this->belongsTo(ArchiveComposante::class, 'composanteId');
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
        return $this->hasMany(ArchiveTache::class, 'activiteId')->orderBy('position', 'asc');
    }

    public function planDeDecaissements()
    {
        return $this->hasMany(ArchivePlanDecaissement::class, 'activiteId');
    }

    public function planDeDecaissement($trimestre, $annee)
    {
        $plan = $this->planDeDecaissements()
                    ->where('trimestre', $trimestre)
                    ->where('annee', $annee)
                    ->first();

        if($plan)
            return ['pret' => $plan->pret,
                'budgetNational' => $plan->budgetNational];

        return ['pret' => 0,
                'budgetNational' => 0];
    }

    public function planDeDecaissementParAnnee($annee)
    {
        $pret = $this->planDeDecaissements()->where('annee', $annee)->sum('pret');

        $budgetNational = $this->planDeDecaissements()->where('annee', $annee)->sum('budgetNational');

        return ['pret' => $pret,
                'budgetNational' => $budgetNational];
    }

    public function suiviFinanciers($annee, $type)
    {
        if(!isset($annee))
        {
            if($type == null)
            {
                return $this->hasMany(SuiviFinancier::class, 'activiteId')->get();
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

        if($statut &&  $statut['etat'] > -2)
        {

            foreach($this->taches as $tache)
            {
                if($tache->statut != 2) $controle = 0;

                else if($controle) $controle = 2;
            }
        }

        if($controle == 2)
        {
            $statut = $this->statuts()->create(['etat' => 2]);

            $allUsers = User::where('programmeId', $this->composante->projet->programmeId)->get();
                foreach($allUsers as $user)
                {
                    if($user->hasPermissionTo('alerte-activite'))
                    {
                        $data['texte'] = "Le statut de l'activite: ".$this->nom." a changé";
                        $data['id'] = $this->id;
                        $data['auteurId'] = 0;
                        $notification = new ChangementStatutNotification($data);

                        $user->notify($notification);

                        $notification = $user->notifications->last();

                        event(new NewNotification($this->formatageNotification($notification, $user)));

                        ChangementStatutJob::dispatch($user,$this, null, 'activite', 'terminer')->delay(10);
                    }
                }
        }

        else if($controle == 1 || $controle == 0)
        {
            $fin = $this->duree->fin;
            $debut = $this->duree->debut;

            if(($statut && $statut['etat'] == -1 || $statut['etat'] == 2) && $debut <= date('Y-m-d'))
            {
                $etat = ['etat' => 0];
                $statut = $this->statuts()->create($etat);

                $allUsers = User::where('programmeId', $this->composante->projet->programmeId)->get();
                foreach($allUsers as $user)
                {
                    if($user->hasPermissionTo('alerte-activite'))
                    {
                        $data['texte'] = "Le statut de l'activite: ".$this->nom." a changé";
                        $data['id'] = $this->id;
                        $data['auteurId'] = 0;
                        $notification = new ChangementStatutNotification($data);

                        $user->notify($notification);

                        $notification = $user->notifications->last();

                        event(new NewNotification($this->formatageNotification($notification, $user)));

                        ChangementStatutJob::dispatch($user,$this, null, 'activite', 'en cours')->delay(10);
                    }
                }
            }

            else if($statut && $statut['etat'] < 1 && $statut['etat'] != -2 && $fin < date('Y-m-d'))
            {
                $etat = ['etat' => 1];
                $statut = $this->statuts()->create($etat);

                $allUsers = User::where('programmeId', $this->composante->projet->programmeId)->get();
                foreach($allUsers as $user)
                {
                    if($user->hasPermissionTo('alerte-activite'))
                    {
                        $data['texte'] = "Le statut de l'activite: ".$this->nom." a changé";
                        $data['id'] = $this->id;
                        $data['auteurId'] = 0;
                        $notification = new ChangementStatutNotification($data);

                        $user->notify($notification);

                        $notification = $user->notifications->last();

                        event(new NewNotification($this->formatageNotification($notification, $user)));

                        ChangementStatutJob::dispatch($user,$this, null, 'activite', 'en retard')->delay(10);
                    }
                }
            }

            else if($statut && $statut['etat'] == 1 && $fin > date('Y-m-d'))
            {
                $etat = ['etat' => 0];
                $statut = $this->statuts()->create($etat);

                $allUsers = User::where('programmeId', $this->composante->projet->programmeId)->get();
                foreach($allUsers as $user)
                {
                    if($user->hasPermissionTo('alerte-activite'))
                    {
                        $data['texte'] = "Le statut de l'activite: ".$this->nom." a changé";
                        $data['id'] = $this->id;
                        $data['auteurId'] = 0;
                        $notification = new ChangementStatutNotification($data);

                        $user->notify($notification);

                        $notification = $user->notifications->last();

                        event(new NewNotification($this->formatageNotification($notification, $user)));

                        ChangementStatutJob::dispatch($user,$this, null, 'activite', 'en cours')->delay(10);
                    }
                }
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

    public function getTepAttribute()
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

        return ($sommeActuel * 100) / $somme;
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

    public function consommer($annee, $type)
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
