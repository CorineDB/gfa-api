<?php

namespace App\Models;

use App\Traits\Helpers\Pta;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

use SaiAshirwadInformatia\SecureIds\Models\Traits\HasSecureIds;

class ArchiveComposante extends Model
{

    use Pta;

    use HasSecureIds, HasFactory ;

    protected $table = 'archive_composantes';

    public $timestamps = true;

    protected $dates = ['deleted_at'];

    protected $fillable = array('nom', 'position', 'poids', 'pret', 'budgetNational', 'parentId', 'description', 'projetId', 'composanteId', 'ptabScopeId', 'statut');

    public function projet()
    {
        return $this->belongsTo(ArchiveProjet::class, 'projetId');
    }

    public function ptabScope()
    {
        return $this->belongsTo(PtabScope::class, 'ptabScopeId');
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
            if($this->composanteId == 0)
            {
                $this->position = $this->position($this->projet, 'composantes');
            }

            else
            {
                $this->position = $this->position($this->composante, 'sousComposantes');
            }

            $this->save();
        }

        if($statut &&  $statut['etat'] > -2)
        {

            foreach($this->activites as $activite)
            {
                if($activite->statut != 2) $controle = 0;

                else if($controle) $controle = 2;
            }
        }

        if($controle == 2)
        {
            $statut = $this->statuts()->create(['etat' => 2]);
        }

        else if($controle == 1)
        {
            if($statut && $statut['etat'] == -1)
            {
                $statut = $this->statuts()->create(['etat' => 0]);
            }
        }

        return $statut ? $statut['etat'] : null;
    }

    public function sousComposantes()
    {
        return $this->hasMany(ArchiveComposante::class, 'composanteId')->orderBy('position', 'asc');
    }

    public function composante()
    {
        return $this->belongsTo(ArchiveComposante::class, 'composanteId');
    }

    public function activites()
    {
        return $this->hasMany(ArchiveActivite::class, 'composanteId')->orderBy('position', 'asc');
    }

    public function ppm()
    {
        return $this->hasMany(ArchiveActivite::class, 'composanteId')
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

    public function getTepAttribute()
    {
        $activites = $this->activites;
        $sousComposantes = $this->sousComposantes;
        $somme = 0;
        $sommeActuel = 0;

        if(count($activites))
        {
            foreach($activites as $activite)
            {
                $suivi = $activite->suivis->last();
                $somme += $activite->poids;
                $sommeActuel += optional($suivi)->poidsActuel;
            }
        }

        if(count($sousComposantes))
        {
            foreach($sousComposantes as $sousComposante)
            {
                $suivi = $sousComposante->suivis->last();
                $somme += $sousComposante->poids;
                $sommeActuel += optional($suivi)->poidsActuel;
            }
        }

        if(!$somme) return 0;

        return ($sommeActuel * 100) / $somme;
    }

    public function sousComposanteTerminer()
    {
        $etat = ['etat' => 2];
        $suivi = ['poidsActuel' => $this->poids];

        $etats = $this->statuts()->create($etat);
        $suivi = $this->suivis()->create($suivi);

        $activites = $this->activites;

        foreach($activites as $activite)
        {
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

        foreach($sousComposantes as $sousComposante)
        {
            $sousComposante->sousComposanteTerminer();
        }
    }

    public function getCodePtaAttribute()
    {
        if($this->composanteId !== 0 || $this->composanteId !== NULL){
            return ''.optional($this->composante)->codePta.'.'.$this->position;
        }
        return ''.optional($this->projet)->codePta.'.'.$this->position;
    }

    public function getBailleurAttribute()
    {
        if($this->composanteId)
        {
            return $this->composante->bailleur;
        }

        else
        {
            return $this->projet->bailleur ;
        }
    }

    public function planDeDecaissement($trimestre, $annee)
    {
        if($this->composanteId)
        {
            $activites = $this->activites;
            $plan = ['pret' => 0,
                     'budgetNational' => 0];

            foreach($activites as $activite)
            {
                $aplan = $activite->planDeDecaissement($trimestre, $annee);
                $plan['pret'] += optional($aplan)->pret;
                $plan['budgetNational'] += optional($aplan)->budgetNational;
            }

        }

        else
        {
            $scs = $this->sousComposantes;
            $plan = ['pret' => 0,
                     'budgetNational' => 0];

            foreach($scs as $sc)
            {
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

        for($i = 1; $i < 5; $i++)
        {
            $pret += $this->planDeDecaissement($i, $annee)['pret'];

            $budgetNational += $this->planDeDecaissement($i, $annee)['budgetNational'];
        }

        return ['pret' => $pret,
                'budgetNational' => $budgetNational];
    }
}
