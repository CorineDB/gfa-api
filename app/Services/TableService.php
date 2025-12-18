<?php

namespace App\Services;

use App\Models\Bailleur;
use App\Models\Gouvernement;
use App\Repositories\PtaRepository;
use App\Repositories\ProgrammeRepository;
use App\Models\Programme;
use App\Models\Projet;
use App\Models\Sinistre;
use Core\Services\Contracts\BaseService;
use Core\Services\Interfaces\TableServiceInterface;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;
use App\Traits\Helpers\Pta;
use Exception;
use Illuminate\Support\Facades\Auth;

/**
* Interface TableServiceInterface
* @package Core\Services\Interfaces
*/
class TableService extends BaseService implements TableServiceInterface
{


    /**
     * @var service
     */
    protected $repository, $programmeRepository;

    /**
     * TableService constructor.
     *
     * @param PtaRepository $ptaRepository
     */
    public function __construct(ProgrammeRepository $programmeRepository)
    {
        parent::__construct($programmeRepository);
        $this->programmeRepository = $programmeRepository;
    }

    public function tauxDecaissement(array $attributs) : JsonResponse
    {
        try
        {

            $programme = Auth::user()->programme;

            $bailleurs = $programme->bailleurs ;

            $table = [];

            $gouvernement = $programme->gouvernement;

            if(array_key_exists('annee', $attributs))
            {
                if($gouvernement)
                {
                    $projets = Projet::where('programmeId', $programme->id)->get();
                    $montantFinancement = 0;
                    $decaissement = 0;
                    $realisationPta = 0;
                    $realisationGlobale = 0;
                    $ptab = 0;
                    $tauxDecaissement = 0;
                    $tef = 0;
                    $tefGlobal = 0;

                    foreach($projets as $projet)
                    {
                        $debut = explode('-', $projet->debut);
                        $fin = explode('-', $projet->fin);

                        if($debut[0] <= $attributs['annee'] && $fin[0] >= $attributs['annee'])
                        {
                            $montantFinancement += $projet->budgetNational;

                            for($i = 1 ; $i < 5; $i++)
                            {
                                $plan = $projet->planDeDecaissement($i, $attributs['annee']);
                                $ptab += $plan['budgetNational'];
                            }

                            $decaissements = $gouvernement->profilable->projetDecaissements($projet->id);


                            foreach($decaissements as $d)
                            {
                                $date = explode('-', $d->date);

                                if($date[0] == $attributs['annee'])
                                {
                                    $decaissement += $d->montant;
                                }
                            }

                            if($montantFinancement)
                            {
                                $tauxDecaissement = round(($decaissement * 100) / $montantFinancement, 2);
                            }

                            $composantes = $projet->composantes;

                            foreach($composantes as $composante)
                            {
                                $activites = $composante->activites;

                                foreach($activites as $activite)
                                {
                                    $realisationPta += $activite->consommer($attributs['annee'], get_class(new Gouvernement));
                                    $realisationGlobale += $activite->consommer(null, get_class(new Gouvernement));
                                }
                            }

                            if($ptab)
                            {
                                $tef = round((($realisationPta + Sinistre::where('programmeId', Auth::user()->programmeId)->where('dateDePaiement', '>=', $attributs['annee']."-01-01")->where('dateDePaiement', '<=', $attributs['annee']."-12-31")->sum('payer')) / $ptab) * 100, 2);
                            }

                            if($montantFinancement)
                            {
                                $tefGlobal = round((($realisationGlobale + Sinistre::where('programmeId', Auth::user()->programmeId)->where('dateDePaiement', '>=', $attributs['annee']."-01-01")->where('dateDePaiement', '<=', $attributs['annee']."-12-31")->sum('payer')) / $montantFinancement)* 100, 2);
                            }
                        }
                    }

                    array_push($table, [
                        'id' => $gouvernement->id,
                        'sigle' => 'budget national',
                        'montantFinancement' => $montantFinancement,
                        'ptab' => $ptab,
                        'decaissement' => $decaissement,
                        'tauxDecaissement' => $tauxDecaissement . "%",
                        'realisationPta' => $realisationPta + Sinistre::where('programmeId', Auth::user()->programmeId)->where('dateDePaiement', '>=', $attributs['annee']."-01-01")->where('dateDePaiement', '<=', $attributs['annee']."-12-31")->sum('payer'),
                        'tef' => $tef . " %" ,
                        'realisationGlobale' => $realisationGlobale + Sinistre::where('programmeId', Auth::user()->programmeId)->where('dateDePaiement', '>=', $attributs['annee']."-01-01")->where('dateDePaiement', '<=', $attributs['annee']."-12-31")->sum('payer'),
                        'tefGlobale' => $tefGlobal . " %"
                    ]);

                }

                foreach($bailleurs as $bailleur)
                {
                    $projet = Projet::where('bailleurId', $bailleur->profilable->id)
                                    ->where('programmeId', $programme->id)
                                    ->first();

                    if($projet == null)
                        continue;

                    $debut = explode('-', $projet->debut);
                    $fin = explode('-', $projet->fin);

                    if($debut[0] <= $attributs['annee'] && $fin[0] >= $attributs['annee'])
                    {
                        $montantFinancement = $projet->pret;


                        $ptab = 0;
                        for($i = 1 ; $i < 5; $i++)
                        {
                            $plan = $projet->planDeDecaissement($i, $attributs['annee']);
                            $ptab += $plan['pret'];
                        }

                        $decaissements = $projet->bailleur->decaissements;
                        $decaissement = 0;
                        $tauxDecaissement = 0;

                        foreach($decaissements as $d)
                        {
                            $date = explode('-', $d->date);

                            if($date[0] == $attributs['annee'])
                            {
                                $decaissement += $d->montant;
                            }
                        }

                        if($montantFinancement)
                        {
                            $tauxDecaissement = round(($decaissement * 100) / $montantFinancement, 2);
                        }

                        $realisationPta = 0;
                        $realisationGlobale = 0;
                        $tef = 0;
                        $tefGlobal = 0;
                        $composantes = $projet->allComposantes;

                        foreach($composantes as $composante)
                        {
                            $activites = $composante->activites;

                            foreach($activites as $activite)
                            {
                                $realisationPta += $activite->consommer($attributs['annee'], get_class(new Bailleur));
                                $realisationGlobale += $activite->consommer(null, get_class(new Bailleur));
                            }
                        }

                        if($ptab)
                        {
                            $tef = round(($realisationPta / $ptab) * 100, 3);
                        }

                        if($montantFinancement)
                        {
                            $tefGlobal = round(($realisationGlobale / $montantFinancement)* 100, 2);
                        }

                        array_push($table, [
                            'id' => $bailleur->profilable->id,
                            'sigle' => $bailleur->profilable->sigle,
                            'montantFinancement' => $montantFinancement,
                            'ptab' => $ptab,
                            'decaissement' => $decaissement,
                            'tauxDecaissement' => $tauxDecaissement . " %",
                            'realisationPta' => $realisationPta,
                            'tef' => $tef . " %",
                            'realisationGlobale' => $realisationGlobale,
                            'tefGlobale' => $tefGlobal . " %"
                        ]);
                    }
                }
            }

            else
            {
                if($gouvernement)
                {
                    $projets = Projet::where('programmeId', $programme->id)->get();
                    $montantFinancement = 0;
                    $decaissement = 0;
                    $realisationPta = 0;
                    $realisationGlobale = 0;

                    foreach($projets as $projet)
                    {


                        $montantFinancement += $projet->budgetNational;


                        $ptab = 0;

                        foreach($projet->activites() as $activite)
                        {
                            $ptab += $activite->planDeDecaissements->sum('budgetNational');
                        }

                        $decaissements = $gouvernement->profilable->projetDecaissements($projet->id);
                        $decaissement += $gouvernement->profilable->projetDecaissements($projet->id)->sum('montant');
                        $tauxDecaissement = 0;


                        if($montantFinancement)
                        {
                            $tauxDecaissement = round(($decaissement * 100) / $montantFinancement, 2);
                        }

                        $tef = 0;
                        $tefGlobal = 0;
                        $composantes = $projet->composantes;

                        foreach($composantes as $composante)
                        {
                            $activites = $composante->activites;

                            foreach($activites as $activite)
                            {
                                //$realisationPta += $activite->consommer($attributs['annee'], get_class(new Gouvernement));
                                $realisationGlobale += $activite->consommer(null, get_class(new Gouvernement));
                            }
                        }

                        if($ptab)
                        {
                            $tef = round((($realisationPta + Sinistre::where('programmeId', Auth::user()->programmeId)->sum('payer')) / $ptab) * 100, 2);
                        }

                        if($montantFinancement)
                        {
                            $tefGlobal = round((($realisationGlobale + Sinistre::where('programmeId', Auth::user()->programmeId)->sum('payer')) / $montantFinancement)* 100, 2);
                        }
                    }

                    array_push($table, [
                        'id' => $gouvernement->id,
                        'sigle' => 'budget national',
                        'montantFinancement' => $montantFinancement,
                        'ptab' => $ptab,
                        'decaissement' => $decaissement,
                        'tauxDecaissement' => $tauxDecaissement . "%",
                        'realisationPta' => $realisationGlobale + Sinistre::where('programmeId', Auth::user()->programmeId)->sum('payer'),
                        'tef' => $tef . " %" ,
                        'realisationGlobale' => $realisationGlobale + Sinistre::where('programmeId', Auth::user()->programmeId)->sum('payer'),
                        'tefGlobale' => $tefGlobal . " %"
                    ]);
                }

                foreach($bailleurs as $bailleur)
                {
                    $projet = Projet::where('bailleurId', $bailleur->profilable->id)
                                    ->where('programmeId', $programme->id)
                                    ->first();

                    if($projet == null)
                        continue;

                    $montantFinancement = $projet->pret;


                    $ptab = 0;

                    foreach($projet->activites() as $activite)
                    {
                        $ptab += $activite->planDeDecaissements->sum('pret');
                    }

                    $decaissements = $projet->bailleur->decaissements;
                    $decaissement = $projet->bailleur->decaissements->sum('montant');
                    $tauxDecaissement = 0;

                    if($montantFinancement)
                    {
                        $tauxDecaissement = round(($decaissement * 100) / $montantFinancement, 2);
                    }

                    $realisationPta = 0;
                    $realisationGlobale = 0;
                    $tef = 0;
                    $tefGlobal = 0;
                    $composantes = $projet->allComposantes;

                    foreach($composantes as $composante)
                    {
                        $activites = $composante->activites;

                        foreach($activites as $activite)
                        {
                            //$realisationPta += $activite->consommer($attributs['annee'], get_class(new Bailleur));
                            $realisationGlobale += $activite->consommer(null, get_class(new Bailleur));
                        }
                    }

                    if($ptab)
                    {
                        $tef = round(($realisationPta / $ptab) * 100, 3);
                    }

                    if($montantFinancement)
                    {
                        $tefGlobal = round(($realisationGlobale / $montantFinancement)* 100, 2);
                    }

                    array_push($table, [
                        'id' => $bailleur->profilable->id,
                        'sigle' => $bailleur->profilable->sigle,
                        'montantFinancement' => $montantFinancement,
                        'ptab' => $ptab,
                        'decaissement' => $decaissement,
                        'tauxDecaissement' => $tauxDecaissement . " %",
                        'realisationPta' => $realisationGlobale,
                        'tef' => $tef . " %",
                        'realisationGlobale' => $realisationGlobale,
                        'tefGlobale' => $tefGlobal . " %"
                    ]);
                }


            }

            return response()->json(['statut' => 'success', 'message' => null, 'data' => $table, 'statutCode' => Response::HTTP_OK], Response::HTTP_OK);
        }
        catch (\Throwable $th)
        {
            return response()->json(['statut' => 'error', 'message' => $th->getMessage(), 'errors' => []], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
}
