<?php

namespace App\Models;

use App\Http\Resources\ActiviteResource;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

use SaiAshirwadInformatia\SecureIds\Models\Traits\HasSecureIds;

class ArchiveProjet extends Model
{

    use HasSecureIds, HasFactory ;

    protected $table = 'archive_projets';

    public $timestamps = true;

    protected $dates = ['deleted_at'];

    protected $fillable = array(
        'nom',
        'poids',
        'couleur',
        'description',
        'ville',
        'pret',
        'budgetNational',
        'debut',
        'fin',
        'bailleurId',
        'objectifGlobaux',
        'nombreEmpoie',
        'programmeId',
        'pays',
        'commune',
        'arrondissement',
        'quartier',
        'secteurActivite',
        'dateAprobation',
        'parentId',
        'ptabScopeId',
        'tauxEngagement',
        'statut'
    );

    protected $with = ['bailleur'];

    public function ptabScope()
    {
        return $this->belongsTo(PtabScope::class, 'ptabScopeId');
    }

    public function bailleur()
    {
        return $this->belongsTo(Bailleur::class, 'bailleurId');
    }

    public function programme()
    {
        return $this->belongsTo(Programme::class, 'programmeId');
    }

    public function composantes()
    {
        return $this->hasMany(ArchiveComposante::class, 'projetId')->where('composanteId', 0)->orderBy('position', 'asc');
    }

    public function sousComposantes()
    {
    	$composantes = $this->composantes;
    	$sc = [];

    	if(count($composantes))
    	{
    		foreach($composantes as $composante)
    		{
    			$sousComposantes = $composante->sousComposantes;

    			if(count($sousComposantes))
    			{
    				foreach($sousComposantes as $c)
    				{
	    				array_push($sc, $c);
    				}
    			}

	    	}
    	}

    	return $sc;
    }

    public function activites()
    {
    	$sousComposantes = $this->sousComposantes();
    	$activites = [];

    	if(count($sousComposantes))
    	{
    		foreach($sousComposantes as $sc)
    		{
    			$activite = $sc->activites;

    			if(count($activite))
    			{
    				foreach($activite as $a)
    				{
	    				array_push($activites, $a);
    				}
    			}

	    	}
    	}


    	if(count( $this->composantes)){
            foreach ($this->composantes as $composante) {

    			$activite = $composante->activites;

    			if(count($activite))
    			{
    				foreach($activite as $a)
    				{
	    				array_push($activites, $a);
    				}
    			}
            }
        }

    	return $activites;
    }

    public function allComposantes()
    {
        return $this->hasMany(ArchiveComposante::class, 'projetId')->orderBy('position', 'asc');
    }

    public function statuts()
    {
        return $this->morphMany(Statut::class, 'statuttable');
    }

    public function suivis()
    {

        $this->allComposantes->map(function($composante) {
            $composante->sousComposantes->map(function($sousComposante) {
                $this->suivis = array_merge($this->suivis, $sousComposante->activites->load('suivis')->pluck('suivis')->collapse()->toArray());
            });

            $this->suivis = array_merge($this->suivis, $composante->activites->load('suivis')->pluck('suivis')->collapse()->toArray());
        });

        return $this->suivis;
    }

    public function indicateurs()
    {
        return $this->morphMany(CadreLogiqueIndicateur::class, 'indicatable');
    }

    public function objectifSpecifiques()
    {
        return $this->morphMany(ObjectifSpecifique::class, 'objectifable');
    }

    public function objectifGlobauxes()
    {
        return $this->morphMany(ObjectifGlobaux::class, 'objectifable');
    }

    public function fichiers()
    {
        return $this->morphMany(Fichier::class, 'fichiertable');
    }

    public function allFichiers()
    {
        return $this->morphMany(Fichier::class, 'fichiertable')->where('description', 'fichier')->get();
    }

    public function image()
    {
        return $this->morphMany(Fichier::class, 'fichiertable')->where('description', 'logo')->orderBy('id', 'desc')->first();
    }

    public function images()
    {
        return $this->morphMany(Fichier::class, 'fichiertable');
    }

    public function getCheminAttribute()
    {
        $image = $this->images->last();
        if ($image) {
            return $image->chemin;
        }

        return null;
    }

    public function getDureeAttribute()
    {
        $duree = $this->durees->last();
        return $duree;
    }

    public function durees()
    {
        return $this->morphMany(Duree::class, 'dureeable');
    }

    public function resultats()
    {
        return $this->morphMany(Resultat::class, 'resultable');
    }

    public function getStatusAttribute()
    {
        //dd($this->statuts);
        $controle = 1;
        $statut = $this->statuts->last();

        $statut = $statut ? $statut : $this->statuts()->create(['etat' => -1]);

        if($statut &&  $statut['etat'] > -2)
        {

            foreach($this->allComposantes as $composante)
            {
                if($composante->statut != 2) $controle = 0;

                else if($controle) $controle = 2;
            }
        }

        if($controle == 2)
        {
            $statut = $this->statuts()->create(['etat' => 2]);
        }

        else if($controle == 1 )
        {
            $fin = $this->fin;
            $debut = $this->debut;

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

        else
        {
            $statut = $this->statuts()->create(['etat' => 0]);
        }
        return $statut ? $statut['etat'] : null;
    }

    public function statistiqueActivite()
    {
        $total = 0;
        $effectue = 0;
        $enCours = 0;
        $enRetard = 0;

        foreach($this->allComposantes as $composante)
        {
            foreach($composante->activites as $activite)
            {
                $total++;

                if($activite->statut == 2) $effectue++;

                else if($activite->statut == 0) $enCours++;

                else if($activite->statut == 1) $enRetard++;

            }

        }

        return [
            'total' => $total,
            'effectue' => $effectue,
            'enCours' => $enCours,
            'enRetard' => $enRetard
        ];
    }

    public function getTefAttribute()
    {
        $ptab = 0;
        for($i = 1 ; $i < 5; $i++)
        {
            $plan = $this->planDeDecaissement($i, date('Y'));
            $ptab += $plan['pret'];
        }

        $tef = 0;
        $realisationPta = 0;
        $realisationGlobale = 0;
        $composantes = $this->allComposantes;

        foreach($composantes as $composante)
        {
            $activites = $composante->activites;

            foreach($activites as $activite)
            {
                $realisationPta += $activite->consommer(date('Y'), get_class(new Bailleur));
                $realisationGlobale += $activite->consommer(null, get_class(new Bailleur));
            }
        }

        if($ptab)
        {
            $tef = round(($realisationPta / $ptab) * 100, 3);
        }

        return $tef ;

    }

    public function getTepAttribute()
    {
        /*$composantes = $this->composantes;
        $somme = 0;
        $sommeActuel = 0;

        if(count($composantes))
        {
            foreach($composantes as $composante)
            {
                $suivi = $composante->suivis->last();
                $somme += $composante->poids;
                $sommeActuel += optional($suivi)->poidsActuel;
            }

            if(!$somme) return 0 ;

            return ($sommeActuel * 100) / $somme;
        }

        return 0 ;*/

        $total = 0;
        $effectue = 0;

        foreach($this->allComposantes as $composante)
        {
            foreach($composante->activites as $activite)
            {

                foreach($activite->taches as $tache)
                {
                    $total += $tache->poids;
                    if($tache->statut == 2)
                    {
                        $effectue += $tache->poids;
                    }
                }
            }

        }

        return $total ? $effectue * 100 / $total : 0;
    }

    public function getTepByAnneeAttribute()
    {
        $activites = [];
        $total = 0;
        $effectue = 0;

        foreach($this->allComposantes as $composante)
        {
            foreach($composante->activites as $activite)
            {
                if($activite->durees->last()->debut >= date('Y').'-01-01' && $activite->durees->last()->fin <= date('Y').'-12-31')
                {
                    foreach($activite->taches as $tache)
                    {
                        $total += $tache->poids;
                        if($tache->statut == 2)
                        {
                            $effectue += $tache->poids;
                        }
                    }
                }
            }
        }

        return $total ? $effectue * 100 / $total : 0;
    }

    public function decaissements()
    {
        return $this->hasMany(ArchiveDecaissement::class, 'projetId');
    }

    public function getCodePtaAttribute()
    {
        $programme = $this->programme;
        $code = $this->bailleur->codes($programme->id)->first();
        return $programme->code . '.' . optional($code)->codePta;
    }

    public function planDeDecaissement($trimestre, $annee)
    {
        $composantes = $this->composantes;
        $plan = [
            'pret' => 0,
            'budgetNational' => 0
        ];

        foreach ($composantes as $composante) {
            $cplan = $composante->planDeDecaissement($trimestre, $annee);
            $plan['pret'] += optional($cplan)->pret;
            $plan['budgetNational'] += optional($cplan)->budgetNational;
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

    public function ppm()
    {
        $composantes = $this->composantes;
        $ppm = collect();

        foreach ($composantes as $composante) {
            $ppm1 = collect($composante->ppm());
            $ppm = $ppm->merge($ppm1);

            $sousComposantes = $composante->sousComposantes;

            foreach ($sousComposantes as $sc) {
                $ppm2 = collect($sc->ppm());
                $ppm = $ppm->merge($ppm2);
            }
        }

        return $ppm;
    }

    public function tauxDeDecaissementParAnnee()
    {
        $montantFinancement = $this->pret + $this->budgetNational;
        $taux = [];

        $debutTab = explode('-', $this->debut);
        $finTab = explode('-', date('Y-m-d'));

        for($annee = $debutTab[0]; $annee <= $finTab[0]; $annee++)
        {
            $montant = Decaissement::where('projetId', $this->id)
                                    ->where('date', '>=', $annee."-01-01")
                                    ->where('date', '<=', $annee."-12-31")
                                    ->orderBy('date', 'asc')
                                    ->sum('montant');

            if(!count($taux))
            {
                array_push($taux, [
                        'date' => $annee,
                        'taux' => ($montant/$montantFinancement) *100
                    ]);
            }

            else
            {
                array_push($taux, [
                    'date' => "".$annee,
                    'taux' => (($montant/$montantFinancement) *100) + $taux[count($taux)-1]['taux']
                ]);
            }
        }

        return $taux;
    }

    public function tauxDeDecaissementAnneeEnCours()
    {
        $montantFinancement = $this->pret + $this->budgetNational;
        $taux = [];

        $decaissements = Decaissement::where('projetId', $this->id)
                                        ->where('date', '>=', date('Y')."-01-01")
                                        ->where('date', '<=', date('Y-m-d'))
                                        ->orderBy('date', 'asc')
                                        ->get();

        foreach($decaissements as $decaissement)
        {
            if(!count($taux))
            {
                array_push($taux, [
                    'date' => $decaissement->date,
                    'taux' => ($decaissement->montant/$montantFinancement) *100
                ]);
            }

            else
            {
                array_push($taux, [
                    'date' => $decaissement->date,
                    'taux' => (($decaissement->montant/$montantFinancement) *100) + $taux[count($taux)-1]['taux']
                ]);
            }
        }

        return $taux;
    }

    public function tefParAnnee()
    {
        $montantFinancement = $this->pret + $this->budgetNational;
        $activites = $this->activites();
        $sites = $this->bailleur->sites()->where('programmeId', $this->programmeId)->get();
        $tef = [];
        $total = 0;

        $debutTab = explode('-', $this->debut);
        $finTab = explode('-', date('Y-m-d'));

        for($annee = $debutTab[0]; $annee <= $finTab[0]; $annee++)
        {
            $suivis = [];

            foreach($sites as $site)
            {
                $total += Sinistre::where('siteId', $site->id)->where('programmeId', $this->programmeId)
                                  ->where('dateDePaiement', '>=', $annee."-01-01")
                                  ->where('dateDePaiement', '<=', $annee."-12-31")
                                  ->orderBy('dateDePaiement', 'asc')
                                  ->sum('payer');
            }

            foreach($activites as $activite)
            {
                array_push($suivis, $activite->suiviFinanciers($annee, null)->sum('consommer'));
            }

            foreach($suivis as $suivi)
            {
                $total += $suivi;
            }

            if(!count($tef))
            {
                array_push($tef, [
                    'date' => $annee,
                    'tef' => ($total/$montantFinancement) *100
                ]);
            }

            else
            {
                array_push($tef, [
                    'date' => "".$annee,
                    'tef' => (($total/$montantFinancement) *100) + $tef[count($tef)-1]['tef']
                ]);
            }
        }

        return $tef;

    }

    public function indicateurs_cadre_logique()
    {
        return $this->morphMany(IndicateurCadreLogique::class, 'indicatable');
    }
}
