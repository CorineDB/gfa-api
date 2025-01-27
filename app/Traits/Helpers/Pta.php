<?php


namespace App\Traits\Helpers;

use App\Models\Activite;
use App\Models\Composante;
use App\Models\Organisation;
use App\Models\Projet;
use App\Models\Tache;
use Exception;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;

trait Pta{

    public function dureePta(Array $durees)
    {
        $tab = [0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0];

        foreach($durees as $duree)
        {
            $debutTab = explode('-', $duree['debut']);
            $finTab = explode('-', $duree['fin']);

            for($i = $debutTab[1]; $i <= $finTab[1]; $i++)
            {
                $tab[$i-1] = 1;
            }
        }

        return $tab;
    }

    public function searchByCodePta(Model $model, $codePta)
    {
        $models = $model::all();

        foreach($models as $m)
        {
            //dump($m->codePta);
            if($m->codePta == $codePta)
                return $m;
        }

        return null;
    }


    public function stringStatutCode($statut){

        switch ($statut) {
            case -2:
                return "Non validé";
                break;

            case -1:
                return "Validé";
                break;

            case 0:
                return "En cours";
                break;

            case 1:
                return "En retard";
                break;

            case 2:
                return "Terminé";
                break;

            default:
                return "Statut code inconnu";
                break;
        }
    }

    public function verifieStatut(int $statut, int $newStatut)
    {
        if( $statut > $newStatut) throw new Exception( "Impossible de changer le statut, le statut est déjà à : ". $this->stringStatutCode($statut), 500);

        //if($statut != $newStatut-1 && $statut != $newStatut) return false;

        return true;
    }

    public function verifieDuree(array $model, array $request)
    {
        if($model['fin'] >= $request['debut']) return false;

        return true;
    }

    public function position(object $parent, string $enfant)
    {
        $objets = $parent->$enfant;
        $i = 0;

        foreach($objets as $objet)
        {
            if($objet->position != 0)
                $i++;
        }
        $i++;

        return $i;
    }

    public function triPta($objet)
    {
        if(!isset($objet))
        {
            return null;
        }

        $size = count($objet);


        for($i = 0; $i < $size-1; $i++)
        {
            $index = $i;

            for($j = $i; $j< $size; $j++)
            {
                if($objet[$index]->position > $objet[$j]->position)
                {
                    $index = $j;
                }
            }

            if($index != $i)
            {
                $tmp = $objet[$i];
                $objet[$i] = $objet[$index];
                $objet[$index] = $tmp;
            }
        }

        return $objet;
    }

    public function filtreByPpm(array $attributs)
    {
        if(array_key_exists('bailleurId', $attributs))
        {
            $projets = Projet::where('programmeId', $attributs['programmeId'])
                               ->where('bailleurId', $attributs['bailleurId'])
                               ->get();
        }

        else
        {
            $projets = Projet::where('programmeId', $attributs['programmeId'])
                               ->get();
        }

        $pta = [];

        if(count($projets))
        {
            foreach($projets as $projet)
            {
                if($projet->statut < -1) continue;

                $debutTab = explode('-', $projet->debut);
                $finTab = explode('-', $projet->fin);

                if($debutTab[0] > $attributs['annee'] || $finTab[0] < $attributs['annee'])
                {
                    continue;
                }

                $composantes = $this->triPta($projet->composantes);
                $composantestab = [];

                foreach($composantes as $composante)
                {
                    if($composante->statut < -1) continue;

                    $sousComposantes = $this->triPta($composante->sousComposantes);
                    if(count($sousComposantes))
                    {
                        $sctab = [];
                        foreach($sousComposantes as $sousComposante)
                        {
                            if($sousComposante->statut < -1) continue;

                            $activites = $this->triPta($sousComposante->ppm());
                            $activitestab = [];
                            foreach($activites as $activite)
                            {
                                if($activite->statut < -1) continue;
                                $controle = 1;

                                $durees = $activite->durees;
                                foreach($durees as $duree)
                                {
                                    $debutTab = explode('-', $duree->debut);
                                    $finTab = explode('-', $duree->fin);

                                    if($debutTab[0] <= $attributs['annee'] && $finTab[0] >= $attributs['annee'])
                                    {
                                        $controle = 0;
                                        break;
                                    }
                                }

                                if($controle)
                                {
                                    continue;
                                }

                                $taches = $this->triPta($activite->taches);
                                $tachestab = [];
                                foreach($taches as $tache)
                                {
                                    if($tache->statut < -1) continue;
                                    $controle = 1;

                                    $durees = $tache->durees;
                                    foreach($durees as $duree)
                                    {
                                        $debutTab = explode('-', $duree->debut);
                                        $finTab = explode('-', $duree->fin);

                                        if($debutTab[0] <= $attributs['annee'] && $finTab[0] >= $attributs['annee'])
                                        {
                                            $controle = 0;
                                            break;
                                        }
                                    }

                                    if($controle)
                                    {
                                        continue;
                                    }

                                    array_push($tachestab, [
                                            "id" => $tache->secure_id,
                                            "nom" => $tache->nom,
                                            "code" => $tache->codePta,
                                            "poids" => $tache->poids,
                                            "poidsActuel" => optional($tache->suivis->last())->poidsActuel ?? 0,
                                            "durees" => $this->dureePta($tache->durees->where('debut', '>=', $attributs['annee'].'-01-01')->where('fin', '<=', $attributs['annee'].'-12-31')->toArray())
                                        ]);
                                }

                                array_push($activitestab, ["id" => $activite->secure_id,
                                                      "nom" => $activite->nom,
                                                      "code" => $activite->codePta,
                                                      "budgetNational" => $activite->budgetNational,
                                                      "pret" => $activite->pret,
                                                      "trimestre1" => $activite->planDeDecaissement(1, $attributs['annee']),
                                                      "trimestre2" => $activite->planDeDecaissement(2, $attributs['annee']),
                                                      "trimestre3" => $activite->planDeDecaissement(3, $attributs['annee']),
                                                      "trimestre4" => $activite->planDeDecaissement(4, $attributs['annee']),
                                                      "budgetise" => $activite->planDeDecaissementParAnnee($attributs['annee']),
                                                      "poids" => $activite->poids,
                                                      "poidsActuel" => optional($activite->suivis->last())->poidsActuel ?? 0,
                                                      "durees" => $this->dureePta($activite->durees->where('debut', '>=', $attributs['annee'].'-01-01')->where('fin', '<=', $attributs['annee'].'-12-31')->toArray()),
                                                      "structureResponsable" => $activite->structureResponsable()->nom,
                                                      "structureAssocie" => $activite->structureAssociee()->nom,
                                                      "taches" => $tachestab]);
                            }

                            array_push($sctab, ["id" => $sousComposante->secure_id,
                                                  "nom" => $sousComposante->nom,
                                                  "budgetNational" => $sousComposante->budgetNational,
                                                "pret" => $sousComposante->pret,
                                                      "trimestre1" => $sousComposante->planDeDecaissement(1, $attributs['annee']),
                                                      "trimestre2" => $sousComposante->planDeDecaissement(2, $attributs['annee']),
                                                      "trimestre3" => $sousComposante->planDeDecaissement(3, $attributs['annee']),
                                                      "trimestre4" => $sousComposante->planDeDecaissement(4, $attributs['annee']),
                                                      "budgetise" => $sousComposante->planDeDecaissementParAnnee($attributs['annee']),
                                                      "poids" => $sousComposante->poids,
                                                      "poidsActuel" => optional($sousComposante->suivis->last())->poidsActuel ?? 0,
                                                  "code" => $sousComposante->codePta,
                                                "activites" => $activitestab]);
                        }
                    }

                    else
                    {
                        $activites = $this->triPta($composante->ppm());
                        $sctab = [];
                        $act = [];

                        foreach($activites as $activite)
                        {
                            if($activite->statut < -1) continue;

                            $controle = 1;

                            $durees = $activite->durees;
                            foreach($durees as $duree)
                            {
                                $debutTab = explode('-', $duree->debut);
                                $finTab = explode('-', $duree->fin);

                                if($debutTab[0] <= $attributs['annee'] && $finTab[0] >= $attributs['annee'])
                                {
                                    $controle = 0;
                                    break;
                                }
                            }

                            if($controle)
                            {
                                continue;
                            }

                            $taches = $this->triPta($activite->taches);
                            $tachestab = [];
                            foreach($taches as $tache)
                            {
                                if($tache->statut < -1) continue;

                                $controle = 1;

                                $durees = $tache->durees;
                                foreach($durees as $duree)
                                {
                                    $debutTab = explode('-', $duree->debut);
                                    $finTab = explode('-', $duree->fin);

                                    if($debutTab[0] <= $attributs['annee'] && $finTab[0] >= $attributs['annee'])
                                    {
                                        $controle = 0;
                                        break;
                                    }
                                }

                                if($controle)
                                {
                                    continue;
                                }

                                array_push($tachestab, [
                                            "id" => $tache->secure_id,
                                            "nom" => $tache->nom,
                                            "code" => $tache->codePta,
                                            "poids" => $tache->poids,
                                            "poidsActuel" => optional($tache->suivis->last())->poidsActuel ?? 0,
                                            "durees" => $this->dureePta($tache->durees->where('debut', '>=', $attributs['annee'].'-01-01')->where('fin', '<=', $attributs['annee'].'-12-31')->toArray())
                                        ]);
                            }

                            array_push($act, ["id" => $activite->id,
                                                  "nom" => $activite->nom,
                                                  "code" => $activite->codePta,
                                                  "budgetNational" => $activite->budgetNational,
                                                  "pret" => $activite->pret,
                                                  "trimestre1" => $activite->planDeDecaissement(1, $attributs['annee']),
                                                  "trimestre2" => $activite->planDeDecaissement(2, $attributs['annee']),
                                                  "trimestre3" => $activite->planDeDecaissement(3, $attributs['annee']),
                                                      "trimestre4" => $activite->planDeDecaissement(4, $attributs['annee']),
                                                      "budgetise" => $activite->planDeDecaissementParAnnee($attributs['annee']),
                                                      "poids" => $activite->poids,
                                                      "poidsActuel" => optional($activite->suivis->last())->poidsActuel ?? 0,
                                                      "structureResponsable" => $activite->structureResponsable()->nom,
                                                      "structureAssocie" => $activite->structureAssociee()->nom,
                                                      "durees" => $this->dureePta($activite->durees->where('debut', '>=', $attributs['annee'].'-01-01')->where('fin', '<=', $attributs['annee'].'-12-31')->toArray()),
                                                  "taches" => $tachestab]);
                        }

                        array_push($sctab, ["id" => 0,
                                            "nom" => 0,
                                            "code" => 0,
                                            "budgetNational" => 0,
                                            "pret" => 0,
                                            "trimestre1" => 0,
                                            "trimestre2" => 0,
                                            "trimestre3" => 0,
                                            "trimestre4" => 0,
                                            "budgetise" => 0,
                                            "poids" => 0,
                                            "poidsActuel" => 0,
                                            "activites" => $act]);
                    }

                    array_push($composantestab, ["id" => $composante->secure_id,
                                                      "nom" => $composante->nom,
                                                      "code" => $composante->codePta,
                                                      "budgetNational" => $composante->budgetNational,
                                                      "pret" => $composante->pret,
                                                      "trimestre1" => $composante->planDeDecaissement(1, $attributs['annee']),
                                                      "trimestre2" => $composante->planDeDecaissement(2, $attributs['annee']),
                                                      "trimestre3" => $composante->planDeDecaissement(3, $attributs['annee']),
                                                      "trimestre4" => $composante->planDeDecaissement(4, $attributs['annee']),
                                                      "budgetise" => $composante->planDeDecaissementParAnnee($attributs['annee']),
                                                      "poids" => $composante->poids,
                                                      "poidsActuel" => optional($composante->suivis->last())->poidsActuel ?? 0,
                                                      "sousComposantes" => $sctab]);
                }

                array_push($pta, ["bailleur" => $projet->bailleur->sigle,
                    "projetId" => $projet->secure_id,
                    "nom" => $projet->nom,
                    "code" => $projet->codePta,
                    "budgetNational" => $projet->budgetNational,
                    "pret" => $projet->pret,
                    "composantes" => $composantestab]);
            }
        }
        return $pta;
    }

    public function filtreByAnnee(array $attributs)
    {
        $projets = Projet::where('programmeId', $attributs['programmeId'])
                           ->get();
        
        if(Auth::user()->hasRole('organisation') || ( get_class(auth()->user()->profilable) == Organisation::class))
        {
            $projets = $projets->where('projetable_id', Auth::user()->profilable->id);
            /*$programme = Auth::user()->programme;
            $projets = Projet::where('programmeId', $programme->id)
                             ->where('projetable_id', Auth::user()->profilable->id)
                             ->get();*/
        }
        else if(isset($attributs['organisationId']) && !empty($attributs["organisationId"])){
            $projets = $projets->where('projetable_id', $attributs['organisationId']);
        }

        $pta = [];

        if(count($projets))
        {
            foreach($projets as $projet)
            {
                if($projet->statut < -1) continue;

                $debutTab = explode('-', $projet->debut);
                $finTab = explode('-', $projet->fin);

                if($debutTab[0] > $attributs['annee'] || $finTab[0] < $attributs['annee'])
                {
                    continue;
                }

                $composantes = $this->triPta($projet->composantes);
                $composantestab = [];

                foreach($composantes as $composante)
                {
                    if($composante->statut < -1) continue;

                    $sousComposantes = $this->triPta($composante->sousComposantes);
                    if(count($sousComposantes))
                    {
                        $sctab = [];
                        foreach($sousComposantes as $sousComposante)
                        {
                            if($sousComposante->statut < -1) continue;

                            $activites = $this->triPta($sousComposante->activites);
                            $activitestab = [];
                            foreach($activites as $activite)
                            {
                                if($activite->statut < -1) continue;

                                $controle = 1;

                                $durees = $activite->durees;
                                foreach($durees as $duree)
                                {
                                    $debutTab = explode('-', $duree->debut);
                                    $finTab = explode('-', $duree->fin);

                                    if($debutTab[0] <= $attributs['annee'] && $finTab[0] >= $attributs['annee'])
                                    {
                                        $controle = 0;
                                        break;
                                    }
                                }

                                if($controle)
                                {
                                    continue;
                                }

                                $taches = $this->triPta($activite->taches);
                                $tachestab = [];
                                foreach($taches as $tache)
                                {
                                    if($tache->statut < -1) continue;

                                    $controle = 1;

                                    $durees = $tache->durees;
                                    foreach($durees as $duree)
                                    {
                                        $debutTab = explode('-', $duree->debut);
                                        $finTab = explode('-', $duree->fin);

                                        if($debutTab[0] <= $attributs['annee'] && $finTab[0] >= $attributs['annee'])
                                        {
                                            $controle = 0;
                                            break;
                                        }
                                    }

                                    if($controle)
                                    {
                                        continue;
                                    }

                                    array_push($tachestab, [
                                            "id" => $tache->secure_id,
                                            "nom" => $tache->nom,
                                            "code" => $tache->codePta,
                                            "poids" => $tache->poids,
                                            "poidsActuel" => optional($tache->suivis->last())->poidsActuel ?? 0,
                                            "durees" => $this->dureePta($tache->durees->where('debut', '>=', $attributs['annee'].'-01-01')->where('fin', '<=', $attributs['annee'].'-12-31')->toArray()),
                                            "tep" => $tache->tep,
                                            "suivis" => $tache->suivis,
                                        ]);
                                }

                                array_push($activitestab, ["id" => $activite->secure_id,
                                                      "nom" => $activite->nom,
                                                      "code" => $activite->codePta,
                                                      "budgetNational" => $activite->budgetNational,
                                                      "pret" => $activite->pret,
                                                      "depenses" => round($activite->consommer,2),
                                                      "tep" => round($activite->tep,2),
                                                      "tef" => round($activite->tef,2),
                                                      "trimestre1" => $activite->planDeDecaissement(1, $attributs['annee']),
                                                      "trimestre2" => $activite->planDeDecaissement(2, $attributs['annee']),
                                                      "trimestre3" => $activite->planDeDecaissement(3, $attributs['annee']),
                                                      "trimestre4" => $activite->planDeDecaissement(4, $attributs['annee']),
                                                      "budgetise" => $activite->planDeDecaissementParAnnee($attributs['annee']),
                                                      "poids" => $activite->poids,
                                                      "poidsActuel" => optional($activite->suivis->last())->poidsActuel ?? 0,
                                                      "durees" => $this->dureePta($activite->durees->where('debut', '>=', $attributs['annee'].'-01-01')->where('fin', '<=', $attributs['annee'].'-12-31')->toArray()),
                                                      /*"structureResponsable" => $activite->structureResponsable()->nom,
                                                      "structureAssocie" => $activite->structureAssociee()->nom,*/
                                                      "taches" => $tachestab]);
                            }

                            array_push($sctab, ["id" => $sousComposante->secure_id,
                                                  "nom" => $sousComposante->nom,
                                                  "budgetNational" => $sousComposante->budgetNational,
                                                  "pret" => $sousComposante->pret,
                                                  "depenses" => round($sousComposante->consommer,2),
                                                  "tep" => round($sousComposante->tep,2),
                                                  "tef" => round($sousComposante->tef,2),
                                                      "trimestre1" => $sousComposante->planDeDecaissement(1, $attributs['annee']),
                                                      "trimestre2" => $sousComposante->planDeDecaissement(2, $attributs['annee']),
                                                      "trimestre3" => $sousComposante->planDeDecaissement(3, $attributs['annee']),
                                                      "trimestre4" => $sousComposante->planDeDecaissement(4, $attributs['annee']),
                                                      "budgetise" => $sousComposante->planDeDecaissementParAnnee($attributs['annee']),
                                                    "poids" => $sousComposante->poids,
                                                      "poidsActuel" => optional($sousComposante->suivis->last())->poidsActuel ?? 0,
                                                  "code" => $sousComposante->codePta,
                                                "activites" => $activitestab]);
                        }
                    }

                    else
                    {
                        $activites = $this->triPta($composante->activites);
                        $sctab = [];
                        $act = [];

                        foreach($activites as $activite)
                        {
                            if($activite->statut < -1) continue;
                            $controle = 1;

                            $durees = $activite->durees;
                            foreach($durees as $duree)
                            {
                                $debutTab = explode('-', $duree->debut);
                                $finTab = explode('-', $duree->fin);

                                if($debutTab[0] <= $attributs['annee'] && $finTab[0] >= $attributs['annee'])
                                {
                                    $controle = 0;
                                    break;
                                }
                            }

                            if($controle)
                            {
                                continue;
                            }

                            $taches = $this->triPta($activite->taches);
                            $tachestab = [];
                            foreach($taches as $tache)
                            {
                                if($tache->statut < -1) continue;

                                $controle = 1;

                                $durees = $tache->durees;
                                foreach($durees as $duree)
                                {
                                    $debutTab = explode('-', $duree->debut);
                                    $finTab = explode('-', $duree->fin);

                                    if($debutTab[0] <= $attributs['annee'] && $finTab[0] >= $attributs['annee'])
                                    {
                                        $controle = 0;
                                        break;
                                    }
                                }

                                if($controle)
                                {
                                    continue;
                                }

                                array_push($tachestab, [
                                            "id" => $tache->secure_id,
                                            "nom" => $tache->nom,
                                            "code" => $tache->codePta,
                                            "poids" => $tache->poids,
                                            "poidsActuel" => optional($tache->suivis->last())->poidsActuel ?? 0,
                                            "durees" => $this->dureePta($tache->durees->where('debut', '>=', $attributs['annee'].'-01-01')->where('fin', '<=', $attributs['annee'].'-12-31')->toArray()),
                                            "tep" => $tache->tep,
                                            "suivis" => $tache->suivis,
                                        ]);
                            }

                            array_push($act, ["id" => $activite->id,
                                                "nom" => $activite->nom,
                                                "code" => $activite->codePta,
                                                "budgetNational" => $activite->budgetNational,
                                                "pret" => $activite->pret,
                                                "depenses" => round($activite->consommer,2),
                                                "tep" => round($activite->tep,2),
                                                "tef" => round($activite->tef,2),
                                                    "trimestre1" => $activite->planDeDecaissement(1, $attributs['annee']),
                                                    "trimestre2" => $activite->planDeDecaissement(2, $attributs['annee']),
                                                    "trimestre3" => $activite->planDeDecaissement(3, $attributs['annee']),
                                                    "trimestre4" => $activite->planDeDecaissement(4, $attributs['annee']),
                                                    "budgetise" => $activite->planDeDecaissementParAnnee($attributs['annee']),
                                                      "poids" => $activite->poids,
                                                      "poidsActuel" => optional($activite->suivis->last())->poidsActuel ?? 0,
                                                      /*"structureResponsable" => $activite->structureResponsable()->nom,
                                                      "structureAssocie" => $activite->structureAssociee()->nom,*/
                                                      "durees" => $this->dureePta($activite->durees->where('debut', '>=', $attributs['annee'].'-01-01')->where('fin', '<=', $attributs['annee'].'-12-31')->toArray()),
                                                  "taches" => $tachestab]);
                        }

                        array_push($sctab, ["id" => 0,
                                            "nom" => 0,
                                            "code" => 0,
                                            "budgetNational" => 0,
                                            "pret" => 0,
                                            "depenses" => 0,
                                            "tep" => 0,
                                            "tef" => 0,
                                            "trimestre1" => 0,
                                            "trimestre2" => 0,
                                            "trimestre3" => 0,
                                            "trimestre4" => 0,
                                            "budgetise" => 0,
                                            "poids" => 0,
                                            "poidsActuel" => 0,
                                            "activites" => $act]);
                    }

                    array_push($composantestab, ["id" => $composante->secure_id,
                                                      "nom" => $composante->nom,
                                                      "code" => $composante->codePta,
                                                      "budgetNational" => $composante->budgetNational,
                                                      "pret" => $composante->pret,
                                                      "depenses" => round($composante->consommer,2),
                                                      "tep" => round($composante->tep,2),
                                                      "tef" => round($composante->tef,2),
                                                      "trimestre1" => $composante->planDeDecaissement(1, $attributs['annee']),
                                                      "trimestre2" => $composante->planDeDecaissement(2, $attributs['annee']),
                                                      "trimestre3" => $composante->planDeDecaissement(3, $attributs['annee']),
                                                      "trimestre4" => $composante->planDeDecaissement(4, $attributs['annee']),
                                                      "budgetise" => $composante->planDeDecaissementParAnnee($attributs['annee']),
                                                      "poids" => $composante->poids,
                                                      "poidsActuel" => optional($composante->suivis->last())->poidsActuel ?? 0,
                                                      "sousComposantes" => $sctab]);
                }

                array_push($pta, [
                    "owner_id" => $projet->projetable->secure_id,
                    "owner_nom" => $projet->projetable->user->nom,
                    "projetId" => $projet->secure_id,
                    "nom" => $projet->nom,
                    "code" => $projet->codePta,
                    "budgetNational" => $projet->budgetNational,
                    "pret" => $projet->pret,
                    "depenses" => round($projet->consommer,2),
                    "tep" => round($projet->tep,2),
                    "tef" => round($projet->tef,2),
                    "composantes" => $composantestab]);
            }
        }
        return $pta;
    }

    public function oldFiltreByAnnee(array $attributs)
    {
        if(array_key_exists('bailleurId', $attributs))
        {
            $projets = Projet::where('programmeId', $attributs['programmeId'])
                               ->where('bailleurId', $attributs['bailleurId'])
                               ->get();
        }

        else if(Auth::user()->hasRole('organisation') || ( get_class(auth()->user()->profilable) == Organisation::class))
        {
            $programme = Auth::user()->programme;
            $projets = Projet::where('programmeId', $programme->id)
                             ->where('projetable_id', Auth::user()->profilable->id)
                             ->get();
        }

        else
        {
            $projets = Projet::where('programmeId', $attributs['programmeId'])
                               ->get();
        }

        $pta = [];

        if(count($projets))
        {
            foreach($projets as $projet)
            {
                if($projet->statut < -1) continue;

                $debutTab = explode('-', $projet->debut);
                $finTab = explode('-', $projet->fin);

                if($debutTab[0] > $attributs['annee'] || $finTab[0] < $attributs['annee'])
                {
                    continue;
                }

                $composantes = $this->triPta($projet->composantes);
                $composantestab = [];

                foreach($composantes as $composante)
                {
                    if($composante->statut < -1) continue;

                    $sousComposantes = $this->triPta($composante->sousComposantes);
                    if(count($sousComposantes))
                    {
                        $sctab = [];
                        foreach($sousComposantes as $sousComposante)
                        {
                            if($sousComposante->statut < -1) continue;

                            $activites = $this->triPta($sousComposante->activites);
                            $activitestab = [];
                            foreach($activites as $activite)
                            {
                                if($activite->statut < -1) continue;

                                $controle = 1;

                                $durees = $activite->durees;
                                foreach($durees as $duree)
                                {
                                    $debutTab = explode('-', $duree->debut);
                                    $finTab = explode('-', $duree->fin);

                                    if($debutTab[0] <= $attributs['annee'] && $finTab[0] >= $attributs['annee'])
                                    {
                                        $controle = 0;
                                        break;
                                    }
                                }

                                if($controle)
                                {
                                    continue;
                                }

                                $taches = $this->triPta($activite->taches);
                                $tachestab = [];
                                foreach($taches as $tache)
                                {
                                    if($tache->statut < -1) continue;

                                    $controle = 1;

                                    $durees = $tache->durees;
                                    foreach($durees as $duree)
                                    {
                                        $debutTab = explode('-', $duree->debut);
                                        $finTab = explode('-', $duree->fin);

                                        if($debutTab[0] <= $attributs['annee'] && $finTab[0] >= $attributs['annee'])
                                        {
                                            $controle = 0;
                                            break;
                                        }
                                    }

                                    if($controle)
                                    {
                                        continue;
                                    }

                                    array_push($tachestab, [
                                            "id" => $tache->secure_id,
                                            "nom" => $tache->nom,
                                            "code" => $tache->codePta,
                                            "poids" => $tache->poids,
                                            "poidsActuel" => optional($tache->suivis->last())->poidsActuel ?? 0,
                                            "durees" => $this->dureePta($tache->durees->where('debut', '>=', $attributs['annee'].'-01-01')->where('fin', '<=', $attributs['annee'].'-12-31')->toArray())
                                        ]);
                                }

                                array_push($activitestab, ["id" => $activite->secure_id,
                                                      "nom" => $activite->nom,
                                                      "code" => $activite->codePta,
                                                      "budgetNational" => $activite->budgetNational,
                                                      "pret" => $activite->pret,
                                                      "trimestre1" => $activite->planDeDecaissement(1, $attributs['annee']),
                                                      "trimestre2" => $activite->planDeDecaissement(2, $attributs['annee']),
                                                      "trimestre3" => $activite->planDeDecaissement(3, $attributs['annee']),
                                                      "trimestre4" => $activite->planDeDecaissement(4, $attributs['annee']),
                                                      "budgetise" => $activite->planDeDecaissementParAnnee($attributs['annee']),
                                                      "poids" => $activite->poids,
                                                      "poidsActuel" => optional($activite->suivis->last())->poidsActuel ?? 0,
                                                      "durees" => $this->dureePta($activite->durees->where('debut', '>=', $attributs['annee'].'-01-01')->where('fin', '<=', $attributs['annee'].'-12-31')->toArray()),
                                                      "structureResponsable" => $activite->structureResponsable()->nom,
                                                      "structureAssocie" => $activite->structureAssociee()->nom,
                                                      "taches" => $tachestab]);
                            }

                            array_push($sctab, ["id" => $sousComposante->secure_id,
                                                  "nom" => $sousComposante->nom,
                                                  "budgetNational" => $sousComposante->budgetNational,
                                                "pret" => $sousComposante->pret,
                                                      "trimestre1" => $sousComposante->planDeDecaissement(1, $attributs['annee']),
                                                      "trimestre2" => $sousComposante->planDeDecaissement(2, $attributs['annee']),
                                                      "trimestre3" => $sousComposante->planDeDecaissement(3, $attributs['annee']),
                                                      "trimestre4" => $sousComposante->planDeDecaissement(4, $attributs['annee']),
                                                      "budgetise" => $sousComposante->planDeDecaissementParAnnee($attributs['annee']),
                                                      "poids" => $sousComposante->poids,
                                                      "poidsActuel" => optional($sousComposante->suivis->last())->poidsActuel ?? 0,
                                                  "code" => $sousComposante->codePta,
                                                "activites" => $activitestab]);
                        }
                    }

                    else
                    {
                        $activites = $this->triPta($composante->activites);
                        $sctab = [];
                        $act = [];

                        foreach($activites as $activite)
                        {
                            if($activite->statut < -1) continue;
                            $controle = 1;

                            $durees = $activite->durees;
                            foreach($durees as $duree)
                            {
                                $debutTab = explode('-', $duree->debut);
                                $finTab = explode('-', $duree->fin);

                                if($debutTab[0] <= $attributs['annee'] && $finTab[0] >= $attributs['annee'])
                                {
                                    $controle = 0;
                                    break;
                                }
                            }

                            if($controle)
                            {
                                continue;
                            }

                            $taches = $this->triPta($activite->taches);
                            $tachestab = [];
                            foreach($taches as $tache)
                            {
                                if($tache->statut < -1) continue;

                                $controle = 1;

                                $durees = $tache->durees;
                                foreach($durees as $duree)
                                {
                                    $debutTab = explode('-', $duree->debut);
                                    $finTab = explode('-', $duree->fin);

                                    if($debutTab[0] <= $attributs['annee'] && $finTab[0] >= $attributs['annee'])
                                    {
                                        $controle = 0;
                                        break;
                                    }
                                }

                                if($controle)
                                {
                                    continue;
                                }

                                array_push($tachestab, [
                                            "id" => $tache->secure_id,
                                            "nom" => $tache->nom,
                                            "code" => $tache->codePta,
                                            "poids" => $tache->poids,
                                            "poidsActuel" => optional($tache->suivis->last())->poidsActuel ?? 0,
                                            "durees" => $this->dureePta($tache->durees->where('debut', '>=', $attributs['annee'].'-01-01')->where('fin', '<=', $attributs['annee'].'-12-31')->toArray())
                                        ]);
                            }

                            array_push($act, ["id" => $activite->id,
                                                  "nom" => $activite->nom,
                                                  "code" => $activite->codePta,
                                                  "budgetNational" => $activite->budgetNational,
                                                  "pret" => $activite->pret,
                                                  "trimestre1" => $activite->planDeDecaissement(1, $attributs['annee']),
                                                  "trimestre2" => $activite->planDeDecaissement(2, $attributs['annee']),
                                                  "trimestre3" => $activite->planDeDecaissement(3, $attributs['annee']),
                                                      "trimestre4" => $activite->planDeDecaissement(4, $attributs['annee']),
                                                      "budgetise" => $activite->planDeDecaissementParAnnee($attributs['annee']),
                                                      "poids" => $activite->poids,
                                                      "poidsActuel" => optional($activite->suivis->last())->poidsActuel ?? 0,
                                                      "structureResponsable" => $activite->structureResponsable()->nom,
                                                      "structureAssocie" => $activite->structureAssociee()->nom,
                                                      "durees" => $this->dureePta($activite->durees->where('debut', '>=', $attributs['annee'].'-01-01')->where('fin', '<=', $attributs['annee'].'-12-31')->toArray()),
                                                  "taches" => $tachestab]);
                        }

                        array_push($sctab, ["id" => 0,
                                            "nom" => 0,
                                            "code" => 0,
                                            "budgetNational" => 0,
                                            "pret" => 0,
                                            "trimestre1" => 0,
                                            "trimestre2" => 0,
                                            "trimestre3" => 0,
                                            "trimestre4" => 0,
                                            "budgetise" => 0,
                                            "poids" => 0,
                                            "poidsActuel" => 0,
                                            "activites" => $act]);
                    }

                    array_push($composantestab, ["id" => $composante->secure_id,
                                                      "nom" => $composante->nom,
                                                      "code" => $composante->codePta,
                                                      "budgetNational" => $composante->budgetNational,
                                                      "pret" => $composante->pret,
                                                      "trimestre1" => $composante->planDeDecaissement(1, $attributs['annee']),
                                                      "trimestre2" => $composante->planDeDecaissement(2, $attributs['annee']),
                                                      "trimestre3" => $composante->planDeDecaissement(3, $attributs['annee']),
                                                      "trimestre4" => $composante->planDeDecaissement(4, $attributs['annee']),
                                                      "budgetise" => $composante->planDeDecaissementParAnnee($attributs['annee']),
                                                      "poids" => $composante->poids,
                                                      "poidsActuel" => optional($composante->suivis->last())->poidsActuel ?? 0,
                                                      "sousComposantes" => $sctab]);
                }

                array_push($pta, ["bailleur" => $projet->bailleur->sigle,
                    "projetId" => $projet->secure_id,
                    "nom" => $projet->nom,
                    "code" => $projet->codePta,
                    "budgetNational" => $projet->budgetNational,
                    "pret" => $projet->pret,
                    "composantes" => $composantestab]);
            }
        }
        return $pta;
    }

    public function filtreByMois(array $attributs)
    {
        if(array_key_exists('bailleurId', $attributs))
        {
            $projets = Projet::where('programmeId', $attributs['programmeId'])
                               ->where('bailleurId', $attributs['bailleurId'])
                               ->get();
        }

        else
        {
            $projets = Projet::where('programmeId', $attributs['programmeId'])
                               ->get();
        }


        $pta = [];

        if(count($projets))
        {
            foreach($projets as $projet)
            {
                $debutTab = explode('-', $projet->debut);
                $finTab = explode('-', $projet->fin);

                if($debutTab[0] > $attributs['annee'] || $finTab[0] < $attributs['annee'])
                {
                    continue;
                }

                $bailleur = $projet->bailleur;
                $composantes = $this->triPta($projet->composantes);
                $composantestab = [];

                foreach($composantes as $composante)
                {
                    $sousComposantes = $this->triPta($composante->sousComposantes);

                    if(count($sousComposantes))
                    {
                        $sctab = [];
                        foreach($sousComposantes as $sousComposante)
                        {
                            $activites = $this->triPta($sousComposante->activites);
                            $activitestab = [];

                            foreach($activites as $activite)
                            {
                                $controle = 1;

                                $durees = $activite->durees;
                                foreach($durees as $duree)
                                {
                                    $debutTab = explode('-', $duree->debut);
                                    $finTab = explode('-', $duree->fin);

                                    if($debutTab[0] <= $attributs['annee'] && $finTab[0] >= $attributs['annee'])
                                    {
                                        $controle = 0;
                                        break;
                                    }
                                }

                                if($controle)
                                {
                                    continue;
                                }

                                $moisd = (int)$debutTab[1];

                                $moisf = (int)$finTab[1];

                                if($moisd <= (int)$attributs['mois'] && $moisf >= (int)$attributs['mois'])
                                {
                                    $taches = $this->triPta($activite->taches);
                                    $tachestab = [];

                                    foreach($taches as $tache)
                                    {
                                        $controle = 1;

                                        $durees = $tache->durees;
                                        foreach($durees as $duree)
                                        {
                                            $debutTab = explode('-', $duree->debut);
                                            $finTab = explode('-', $duree->fin);

                                            if($debutTab[0] <= $attributs['annee'] && $finTab[0] >= $attributs['annee'])
                                            {
                                                $controle = 0;
                                                break;
                                            }
                                        }

                                        if($controle)
                                        {
                                            continue;
                                        }

                                        $moisd = (int)$debutTab[1];

                                        $moisf = (int)$finTab[1];

                                        if($moisd <= (int)$attributs['mois'] && $moisf >= (int)$attributs['mois'])
                                        {
                                            array_push($tachestab, [
                                            "id" => $tache->secure_id,
                                            "nom" => $tache->nom,
                                            "code" => $tache->codePta,
                                            "poids" => $tache->poids,
                                            "durees" => $this->dureePta($tache->durees->where('debut', '>=', $attributs['annee'].'-01-01')->where('fin', '<=', $attributs['annee'].'-12-31')->toArray())
                                        ]);
                                        }
                                    }

                                    array_push($activitestab, ["id" => $activite->id,
                                                      "nom" => $activite->nom,
                                                      "code" => $activite->codePta,
                                                      "budgetNational" => $activite->budgetNational,
                                                      "pret" => $activite->pret,
                                                      "structureResponsable" => $activite->structureResponsable()->nom,
                                                      "structureAssocie" => $activite->structureAssociee()->nom,
                                                      "taches" => $tachestab]);
                                }
                            }

                            array_push($sctab, ["id" => $sousComposante->id,
                                                "nom" => $sousComposante->nom,
                                                "code" => $sousComposante->codePta,
                                                "budgetNational" => $sousComposante->budgetNational,
                                                      "pret" => $sousComposante->pret,
                                                "activites" => $activitestab]);
                        }
                    }

                    else
                    {
                        $activites = $this->triPta($composante->activites);
                        $sctab = [];

                        foreach($activites as $activite)
                        {
                            $controle = 1;

                            $durees = $activite->durees;
                            foreach($durees as $duree)
                            {
                                $debutTab = explode('-', $duree->debut);
                                $finTab = explode('-', $duree->fin);

                                if($debutTab[0] <= $attributs['annee'] && $finTab[0] >= $attributs['annee'])
                                {
                                    $controle = 0;
                                    break;
                                }
                            }

                            if($controle)
                            {
                                continue;
                            }

                            $moisd = (int)$debutTab[1];

                            $moisf = (int)$finTab[1];

                            if($moisd <= (int)$attributs['mois'] && $moisf >= (int)$attributs['mois'])
                            {
                                $taches = $this->triPta($activite->taches);
                                $tachestab = [];

                                foreach($taches as $tache)
                                {
                                    $controle = 1;

                                    $durees = $tache->durees;
                                    foreach($durees as $duree)
                                    {
                                        $debutTab = explode('-', $duree->debut);
                                        $finTab = explode('-', $duree->fin);

                                        if($debutTab[0] <= $attributs['annee'] && $finTab[0] >= $attributs['annee'])
                                        {
                                            $controle = 0;
                                            break;
                                        }
                                    }

                                    if($controle)
                                    {
                                        continue;
                                    }

                                    $moisd = (int)$debutTab[1];

                                    $moisf = (int)$finTab[1];

                                    if($moisd <= (int)$attributs['mois'] && $moisf >= (int)$attributs['mois'])
                                    {
                                        array_push($tachestab, [
                                            "id" => $tache->secure_id,
                                            "nom" => $tache->nom,
                                            "code" => $tache->codePta,
                                            "poids" => $tache->poids,
                                            "durees" => $this->dureePta($tache->durees->where('debut', '>=', $attributs['annee'].'-01-01')->where('fin', '<=', $attributs['annee'].'-12-31')->toArray())
                                        ]);
                                    }
                                }

                                array_push($sctab, ["id" => $activite->id,
                                                    "nom" => $activite->nom,
                                                    "code" => $activite->codePta,
                                                    "budgetNational" => $activite->budgetNational,
                                                      "pret" => $activite->pret,
                                                      "structureResponsable" => $activite->structureResponsable()->nom,
                                                      "structureAssocie" => $activite->structureAssociee()->nom,
                                                    "taches" => $tachestab]);
                            }
                        }
                    }

                    array_push($composantestab, ["id" => $composante->id,
                                                      "nom" => $composante->nom,
                                                      "code" => $composante->codePta,
                                                      "budgetNational" => $composante->budgetNational,
                                                      "pret" => $composante->pret,
                                                      "sousComposantes" => $sctab]);
                }

                $pta = array_merge($pta, ["{$bailleur->sigle}" => $composantestab]);
            }
        }
        return $pta;

    }

    public function filtreByDate(array $attributs)
    {
        $pta = [];

        if(array_key_exists('bailleurId', $attributs))
        {
            $projets = Projet::where('programmeId', $attributs['programmeId'])
                               ->where('bailleurId', $attributs['bailleurId'])
                               ->get();
        }

        else
        {
            $projets = Projet::where('programmeId', $attributs['programmeId'])
                               ->get();
        }

        if($projets)
        {
            foreach($projets as $projet)
            {
                $debutTab = explode('-', $projet->debut);
                $finTab = explode('-', $projet->fin);

                if($projet->debut > $attributs['debut'] || $projet->fin < $attributs['fin'])
                {
                    continue;
                }

                $composantes = $this->triPta($projet->composantes);
                $composantestab = [];

                foreach($composantes as $composante)
                {
                    $sousComposantes = $this->triPta($composante->sousComposantes);
                    if(count($sousComposantes))
                    {
                        $sctab = [];

                        foreach($sousComposantes as $sousComposante)
                        {
                            $activites = $this->triPta($sousComposante->activites);
                            $activitestab = [];

                            foreach($activites as $activite)
                            {
                                $controle = 1;

                                $durees = $activite->durees;
                                foreach($durees as $duree)
                                {

                                    if($duree->debut <= $attributs['debut'])
                                    {
                                        $controle = 0;
                                        break;
                                    }
                                }

                                if($controle)
                                {
                                    continue;
                                }


                                $taches = $this->triPta($activite->taches);
                                $tachestab = [];

                                foreach($taches as $tache)
                                {
                                    $controle = 1;

                                    $durees = $tache->durees;
                                    foreach($durees as $duree)
                                    {

                                        if($duree->debut <= $attributs['debut'])
                                        {
                                            $controle = 0;
                                            break;
                                        }
                                    }

                                    if($controle)
                                    {

                                        continue;
                                    }

                                    $tachestab = array_merge($tachestab, [$tache]);

                                }
                                array_push($activitestab, ["id" => $activite->id,
                                                      "nom" => $activite->nom,
                                                      "code" => $activite->codePta,
                                                      "budgetNational" => $activite->budgetNational,
                                                      "pret" => $activite->pret,
                                                      "structureResponsable" => $activite->structureResponsable()->nom,
                                                      "structureAssocie" => $activite->structureAssociee()->nom,
                                                      "taches" => $tachestab]);
                            }

                            array_push($sctab, ["id" => $sousComposante->id,
                                                      "nom" => $sousComposante->nom,
                                                      "code" => $sousComposante->codePta,
                                                      "budgetNational" => $sousComposante->budgetNational,
                                                      "pret" => $sousComposante->pret,
                                                      "activites" => $activitestab]);

                        }
                    }

                    else
                    {
                        $activites = $this->triPta($composante->activites);
                        $sctab = [];

                        foreach($activites as $activite)
                        {
                            $controle = 1;

                            $durees = $activite->durees;
                            foreach($durees as $duree)
                            {

                                if($duree->debut <= $attributs['debut'])
                                {
                                    $controle = 0;
                                    break;
                                }
                            }

                            if($controle)
                            {
                                continue;
                            }


                            $taches = $this->triPta($activite->taches);
                            $tachestab = [];

                            foreach($taches as $tache)
                            {
                                $controle = 1;

                                $durees = $tache->durees;
                                foreach($durees as $duree)
                                {

                                    if($duree->debut <= $attributs['debut'])
                                    {
                                        $controle = 0;
                                        break;
                                    }
                                }

                                if($controle)
                                {
                                    continue;
                                }

                                $tachestab = array_merge($tachestab, [$tache]);

                            }
                            array_push($activitestab, ["id" => $activite->id,
                                                      "nom" => $activite->nom,
                                                      "code" => $activite->codePta,
                                                      "budgetNational" => $activite->budgetNational,
                                                      "pret" => $activite->pret,
                                                      "structureResponsable" => $activite->structureResponsable()->nom,
                                                      "structureAssocie" => $activite->structureAssociee()->nom,
                                                      "taches" => $tachestab]);
                        }
                    }


                    array_push($composantestab, ["id" => $composante->id,
                                                      "nom" => $composante->nom,
                                                      "code" => $composante->codePta,
                                                      "budgetNational" => $composante->budgetNational,
                                                      "pret" => $composante->pret,
                                                      "sousComposante" => $sctab]);
                }

                array_push($pta, ["id" => $projet->id,
                    "nom" => $projet->nom,
                    "code" => $projet->codePta,
                    "budgetNational" => $projet->budgetNational,
                    "pret" => $projet->pret,
                    "composantes" => $composantestab]);
            }
        }
        return $pta;
    }

    public function filtreAll(array $attributs)
    {
        $pta = [];

        if(array_key_exists('bailleurId', $attributs))
        {
            $projets = Projet::where('programmeId', $attributs['programmeId'])
                               ->where('bailleurId', $attributs['bailleurId'])
                               ->get();
        }

        else
        {
            $projets = Projet::where('programmeId', $attributs['programmeId'])
                               ->get();
        }

        if($projets)
        {
            foreach($projets as $projet)
            {
                $bailleur = $projet->bailleur;
                $composantes = $this->triPta($projet->composantes);
                $composantestab = [];

                foreach($composantes as $composante)
                {
                    $sousComposantes = $this->triPta($composante->sousComposantes);
                    if(count($sousComposantes))
                    {
                        $sctab = [];
                        foreach($sousComposantes as $sousComposante)
                        {
                            $activites = $this->triPta($sousComposante->activites);
                            $activitestab = [];

                            foreach($activites as $activite)
                            {
                                $taches = $this->triPta($activite->taches);

                                array_push($activitestab, ["id" => $activite->id,
                                                      "nom" => $activite->nom,
                                                      "code" => $activite->codePta,
                                                      "budgetNational" => $activite->budgetNational,
                                                      "pret" => $activite->pret,
                                                      "tache" => $taches]);
                            }

                            array_push($sctab, ["id" => $sousComposante->id,
                                                      "nom" => $sousComposante->nom,
                                                      "code" => $sousComposante->codePta,
                                                      "budgetNational" => $sousComposante->budgetNational,
                                                      "pret" => $sousComposante->pret,
                                                      "activites" => $activitestab]);
                        }
                    }

                    else
                    {
                        $activites = $this->triPta($composante->activites);
                        $sctab = [];

                        foreach($activites as $activite)
                        {
                            $taches = $activite->taches;

                            array_push($sctab, ["id" => $activite->id,
                                                      "nom" => $activite->nom,
                                                      "code" => $activite->codePta,
                                                      "budgetNational" => $activite->budgetNational,
                                                      "pret" => $activite->pret,
                                                      "taches" => $sctab]);
                        }
                    }

                    array_push($composantestab, ["id" => $composante->id,
                                                      "nom" => $composante->nom,
                                                      "code" => $composante->codePta,
                                                      "budgetNational" => $composante->budgetNational,
                                                      "pret" => $composante->pret,
                                                      "sousComposantes/activites" => $sctab]);
                }

                array_push($pta, ["id" => $projet->id,
                    "nom" => $projet->nom,
                    "code" => $projet->codePta,
                    "budgetNational" => $projet->budgetNational,
                    "pret" => $projet->pret,
                    "composantes" => $composantestab]);

            }
        }
        return $pta;
    }

    public function rangement($objects)
    {

        $boundaries = [get_class(new Composante()), get_class(new Activite()), get_class(new Tache()) ];

        foreach ($objects as $object)
        {
            if( in_array(get_class($object), $boundaries) ){

                $object->position--;

                $object->save();
            }
        }
    }

}
