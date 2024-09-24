<?php

namespace App\Services;

use App\Events\NewNotification;
use App\Http\Resources\ComposanteResource;
use App\Http\Resources\DecaissementResource;
use App\Http\Resources\ObjectifSpecifiqueResource;
use App\Repositories\ProjetRepository;
use App\Repositories\BailleurRepository;
use App\Repositories\ProgrammeRepository;
use App\Models\Composante;
use App\Models\Projet;
use App\Models\Code;
use App\Http\Resources\ProjetResource;
use App\Http\Resources\ProjetsResource;
use App\Http\Resources\ProjetStatistiqueResource;
use App\Http\Resources\ResultatResource;
use App\Http\Resources\suivi\SuiviIndicateursResource;
use App\Http\Resources\suivis\SuivisResource;
use App\Http\Resources\user\UserResource;
use App\Jobs\GenererPta;
use App\Models\Activite;
use App\Models\Bailleur;
use App\Models\Decaissement;
use App\Models\Sinistre;
use App\Models\Suivi;
use App\Models\SuiviIndicateur;
use App\Models\UniteeDeGestion;
use App\Models\User;
use App\Notifications\FichierNotification;
use App\Traits\Helpers\HelperTrait;
use App\Traits\Helpers\IdTrait;
use App\Traits\Helpers\LogActivity;
use App\Traits\Helpers\Pta;
use Carbon\Carbon;
use Core\Services\Contracts\BaseService;
use Core\Services\Interfaces\ProjetServiceInterface;
use DateTime;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;
use Exception;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;

/**
* Interface UserServiceInterface
* @package Core\Services\Interfaces
*/
class ProjetService extends BaseService implements ProjetServiceInterface
{
    use IdTrait, Pta, HelperTrait;

    /**
     * @var service
     */
    protected $repository, $bailleurRepository, $programmeRepository;

    protected $suivis = [];

    /**
     * ProjetService constructor.
     *
     * @param ProjetRepository $projetRepository
     */
    public function __construct(ProjetRepository $projetRepository, BailleurRepository $bailleurRepository, ProgrammeRepository $programmeRepository)
    {
        parent::__construct($projetRepository);
        $this->repository = $projetRepository;
        $this->bailleurRepository = $bailleurRepository;
        $this->programmeRepository = $programmeRepository;
    }

    public function all(array $attributs = ['*'], array $relations = []): JsonResponse
    {
        try
        {
            if(Auth::user()->hasRole('bailleur'))
                return response()->json([';statut' => 'success', 'message' => null, 'data' => new ProjetResource((Auth::user()->profilable->projets)), 'statutCode' => Response::HTTP_OK], Response::HTTP_OK);

            return response()->json([';statut' => 'success', 'message' => null, 'data' => ProjetsResource::collection($this->repository->all()), 'statutCode' => Response::HTTP_OK], Response::HTTP_OK);
        }
        catch (\Throwable $th)
        {
            return response()->json(['statut' => 'error', 'message' => $th->getMessage(), 'errors' => []], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    public function create(array $attributs) : JsonResponse
    {
        DB::beginTransaction();

        try
        {
            $attributs = array_merge($attributs, ['programmeId' => Auth::user()->programme->id]);
            if(!($bailleur = $this->bailleurRepository->findById($attributs['bailleurId']))) {
                throw new Exception( "Ce bailleur n'existe pas", 500);
            }

            if(($projet = $bailleur->projets)) throw new Exception( "Ce bailleur a déja un projet dans le programme", 500);

            if(!($programme = $this->programmeRepository->findById($attributs['programmeId']))) throw new Exception( "Ce programme n'existe pas", 500);

            $attributs = array_merge($attributs, ['bailleurId' => $bailleur->id, 'programmeId' => $programme->id,]);

            $attributs = array_merge($attributs, ['statut' => -2]);

            $projet = $this->repository->create($attributs);

            $projet = $projet->fresh();

            /*$statut = ['etat' => -2];

            $statuts = $projet->statuts()->create($statut);*/

            if(isset($attributs['image']))
            {
                $old_image = $projet->chemin;

                $this->storeFile($attributs['image'], 'image', $projet, null, 'logo');

                if($old_image != null){

                    unlink(public_path("storage/" . $old_image));

                    $old_image->delete();
                }
            }

            $i = 0;

            while(array_key_exists('fichier'.$i, $attributs))
            {
                if($attributs['fichier'.$i]->getClientOriginalExtension() != 'jpg'  &&
                   $attributs['fichier'.$i]->getClientOriginalExtension() != 'png' &&
                   $attributs['fichier'.$i]->getClientOriginalExtension() != 'jpeg' &&
                   $attributs['fichier'.$i]->getClientOriginalExtension() != 'docx' &&
                   $attributs['fichier'.$i]->getClientOriginalExtension() != 'pdf')
                    throw new Exception("Le fichier doit être au format jpg, png, jpeg, docx ou pdf", 500);

                $fichier = $this->storeFile($attributs['fichier'.$i], 'projets', $projet, null, 'fichier');

                if(array_key_exists('sharedId', $attributs))
                {
                    foreach($attributs['sharedId'] as $id)
                    {
                        $user = User::findByKey($id);

                        if($user)
                        {
                            $this->storeFile($attributs['fichier'.$i], 'projets', $projet, null, 'fichier', ['fichierId' => $fichier->id, 'userId' => $user->id]);
                        }

                        $data['texte'] = "Un fichier vient d'etre partagé avec vous dans le dossier projet";
                        $data['id'] = $fichier->id;
                        $data['auteurId'] = Auth::user()->id;
                        $notification = new FichierNotification($data);

                        $user->notify($notification);

                        $notification = $user->notifications->last();

                        event(new NewNotification($this->formatageNotification($notification, $user)));
                    }
                }

                $i++;
            }

            /*if(isset($attributs['fichier']))
            {
                foreach($attributs['fichier'] as $fichier)
                {
                    $this->storeFile($fichier, 'projets', $projet, null, 'fichier');
                }
            }*/

            $projet = $projet->fresh();

            $acteur = Auth::check() ? Auth::user()->nom . " ". Auth::user()->prenom : "Inconnu";

            $message = $message ?? Str::ucfirst($acteur) . " a créé un " . strtolower(class_basename($projet));

            LogActivity::addToLog("Enregistrement", $message, get_class($projet), $projet->id);

            DB::commit();

            GenererPta::dispatch(Auth::user()->programme)->delay(5);

            return response()->json(['statut' => 'success', 'message' => null, 'data' => new ProjetResource($projet), 'statutCode' => Response::HTTP_OK], Response::HTTP_OK);
        }
        catch (\Throwable $th)
        {
            DB::rollback();
            return response()->json(['statut' => 'error', 'message' => $th->getMessage(), 'errors' => []], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    public function update($projetId, array $attributs) : JsonResponse
    {
        DB::beginTransaction();

        try
        {
            /*if(array_key_exists('bailleurId', $attributs))
                if(!($bailleur = $this->bailleurRepository->findById($attributs['bailleurId']))) throw new Exception( "Ce bailleur n'existe pas", 500);*/

            unset($attributs['bailleurId']);

            if(array_key_exists('programeId', $attributs))
            {
                if(!($programme = $this->programmeRepository->findById($attributs['programmeId']))) throw new Exception( "Ce programme n'existe pas", 500);

                if($programme->debut > $attributs['debut']) throw new Exception( "La date de début du projet est antérieur à celui du programme", 500);

                if($programme->fin < $attributs['fin']) throw new Exception( "La date de fin du projet est supérieur à celui du programme", 500);

            }

            if((!is_object($projetId )))
                $projet = $this->repository->findById($projetId);
            else {
                $projet = $projetId;
            }

            $projet = $projet->fill($attributs);

            $projet->save();

            if(array_key_exists('statut', $attributs) && $attributs['statut'] === -1 ){

                if(!Auth::user()->hasPermissionTo('validation')) throw new Exception( "Vous n'avez pas la permission de faire la validation", 500);

                $statut = $projet->statut;

                $this->verifieStatut($statut, $attributs['statut']);

                /*$statut = ['etat' => $attributs['statut']];

                $statuts = $projet->statuts()->create($statut);*/

            }

            if(array_key_exists('image', $attributs))
            {
                $old_image = $projet->image();

                $fichier = $this->storeFile($attributs['image'], 'image', $projet, null, 'logo');


                if($old_image != null){

                    if(file_exists(public_path("storage/" . $old_image->chemin)))
                    {
                        unlink(public_path("storage/" . $old_image->chemin));

                        $old_image->delete();
                    }
                }
            }

            $projet = $projet->fresh();

            $statuts = $projet->statut;

            $acteur = Auth::check() ? Auth::user()->nom . " ". Auth::user()->prenom : "Inconnu";

            $message = $message ?? Str::ucfirst($acteur) . " a modifié un " . strtolower(class_basename($projet));

            LogActivity::addToLog("Modification", $message, get_class($projet), $projet->id);

            DB::commit();

            GenererPta::dispatch(Auth::user()->programme)->delay(5);

            return response()->json(['statut' => 'success', 'message' => $attributs, 'data' => new ProjetResource($projet), 'statutCode' => Response::HTTP_OK], Response::HTTP_OK);
        }
        catch (\Throwable $th)
        {
            DB::rollback();
            return response()->json(['statut' => 'error', 'message' => $th->getMessage(), 'errors' => []], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    public function statistiques($id){

        try
        {

                $projet = $this->repository->findById($id);

                $projet = new ProjetStatistiqueResource($projet);

                $decaissementObtenu = Decaissement::where('projetId', $projet->id)
                                        ->where('date', '>=', date('Y')."-01-01")
                                        ->where('date', '<=', date('Y')."-12-31")
                                        ->where('decaissementable_type', get_class(new Bailleur))
                                        ->orderBy('date', 'asc')
                                        ->sum('montant');

                $budgetNationalObtenu = Decaissement::where('projetId', $projet->id)
                                        ->where('date', '>=', date('Y')."-01-01")
                                        ->where('date', '<=', date('Y')."-12-31")
                                        ->where('decaissementable_type', '!=', get_class(new Bailleur))
                                        ->orderBy('date', 'asc')
                                        ->sum('montant');

                /*
                    $stats = [
                        "tep" => $projet->tep,
                        "tef" => $projet->tef,
                        "composantesT" => $projet->sum(function ($projet) {
                            return $projet->composantes->sum('tep');
                        }),
                        "composantes" => $projet->composantes->map(function ($composante) {
                            return [
                                "nom" =>  $composante->nom,
                                "tep" =>  $composante->tep,
                                "activites_count" =>  $composante->activites->count()
                            ];
                        })

                    ];
                */
                $fin = new DateTime($projet->fin);
                $now = new DateTime(date('Y-m-d'));

                $tauxDecaissement =  $projet->tauxDeDecaissementParAnnee();

                $suivis = [];

                foreach($projet->activites() as $activite)
                {
                    array_push($suivis, [
                        "id"            => $activite->secure_id,
                        "poidsActuel"   => $activite->poidActuel,
                        "nom"           => $activite->nom,
                        "statut"        => $activite->statut
                    ]);
                }

                $tefs =  $projet->tefParAnnee();

                $site = optional($projet->bailleur->sites()->where('programmeId', $projet->programmeId)->first());

                $allprojet = $projet->programme->projets;

                $totale = 0;
                $bailleurs = [];

                foreach($allprojet as $p)
                {
                    $totale += $p->tep;
                    array_push($bailleurs, $p->bailleur->sigle);
                }

                $teps = [];

                foreach($allprojet as $p)
                {
                    array_push($teps, round($totale ? ($p->tep*100)/$totale : 0, 2));
                }

                $stats = [
                    "tep_allProjets" => [
                        'bailleurs' => $bailleurs,
                        'percent' => $teps
                    ],
                    "stats_composantes" => $projet->composantes->map(function ($composante) {
                        return [
                             "nom" =>  $composante->nom,
                             "tep" =>  round($composante->tep, 2),
                             "activites_count" =>  $composante->activites->count()
                        ];
                    }),

                    "suivis_indicateurs" => SuiviIndicateur::whereHas('valeurCible', function($query) use ($projet){
                        $query->where("cibleable_type", "App\\Models\\Indicateur")->whereIn('cibleable_id', $projet->bailleur->indicateurs->pluck('id'));
                    })->get()->map(function($suiviIndicateur){

                        return [
                            "id"                    => $suiviIndicateur->secure_id,
                            "trimestre"             => $suiviIndicateur->trimestre,
                            "annee"                 => $suiviIndicateur->valeurCible->annee,
                            "valeurRealise"         => $suiviIndicateur->valeurRealise[0],
                            "commentaire"           => $suiviIndicateur->commentaire,
                            "valeurCible"           => $suiviIndicateur->valeurCible->valeurCible[0],
                            "indicateur"            => $suiviIndicateur->valeurCible->cibleable->nom,
                            "created_at"            => Carbon::parse($suiviIndicateur->created_at)->format("Y-m-d")

                        ];
                    }),

                    "suivis" => $suivis,

                    "taux_decaissement" => [
                        "taux" => round($tauxDecaissement[count($tauxDecaissement) - 1]['taux'], 2),
                        "percent" => round(count($tauxDecaissement) === 1 ? $tauxDecaissement[0]['taux'] : $tauxDecaissement[count($tauxDecaissement) - 1]['taux'] - $tauxDecaissement[count($tauxDecaissement) - 2]['taux'], 2)
                    ],

                    "taux_financier" => [
                        "tef" => round($tefs[count($tefs) - 1]['tef'], 2),
                        "percent" => round(count($tefs) === 1 ? $tefs[0]['tef'] : $tefs[count($tefs) - 1]['tef'] - $tefs[count($tefs) - 2]['tef'], 2)
                    ],

                    "total_realisation" => $projet->tef  + (!$site ? 0 : Sinistre::where('siteId', $site->id)->where('programmeId', $projet->programmeId)->sum('payer')),
                    "total_decaissement_bailleur" => $projet->decaissements->where('decaissementable_type', get_class(new Bailleur()))->sum('montant'),
                    "entreprises" => $projet->bailleur->entrepriseExecutants(),
                    "sites" => $projet->bailleur->sites,
                    "equipes" => UserResource::collection(User::where('programmeId', $projet->programme->id)->
                                                where('profilable_type', get_class(new  UniteeDeGestion()))->
                                                where('profilable_id', $projet->programme->uniteeDeGestion->profilable->id)->
                                                /*where('id', '!=', $user->id)->*/
                                                get()),
                    "nbrJourRestant" => $now->diff($fin)->days,
                    "decaissementPrevu" => $projet->planDeDecaissementParAnnee(date('Y'))['pret'],
                    "decaissementObtenu" => $decaissementObtenu,
                    "budgetNationalPrevu" => $projet->planDeDecaissementParAnnee(date('Y'))['budgetNational'],
                    "budgetNationalObtenu" => $budgetNationalObtenu,
                ];

                $data  = array_merge( json_decode($projet->toJson(), true), $stats);




            return response()->json(['statut' => 'success', 'message' => null, 'data' => $data, 'statutCode' => Response::HTTP_OK], Response::HTTP_OK);
        }
        catch (\Throwable $th)
        {
            return response()->json(['statut' => 'error', 'message' => $th->getMessage(), 'errors' => []], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    public function composantes($id = null) : JsonResponse
    {

        try
        {
            $composantes = [];

            if( $id !== 'undefined' ) $projet = $this->repository->findById($id); //Retourner les données du premier projet

            else $projet = $this->repository->firstItem(); //Retourner les données du premier projet

            if(!$projet) throw new Exception( "Ce projet n'existe pas", 500);

            $composantes = $this->triPta($projet->composantes);

            return response()->json(['statut' => 'success', 'message' => null, 'data' => ComposanteResource::collection($composantes), 'statutCode' => Response::HTTP_OK], Response::HTTP_OK);
        }
        catch (\Throwable $th)
        {
            return response()->json(['statut' => 'error', 'message' => $th->getMessage(), 'errors' => []], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    public function decaissements($id) : JsonResponse
    {

        try
        {

            if(!($projet = $this->repository->findById($id))) throw new Exception( "Ce projet n'existe pas", 500);

            $decaissements = $projet->decaissements;

            return response()->json(['statut' => 'success', 'message' => null, 'data' => DecaissementResource::collection($decaissements), 'statutCode' => Response::HTTP_OK], Response::HTTP_OK);
        }
        catch (\Throwable $th)
        {
            return response()->json(['statut' => 'error', 'message' => $th->getMessage(), 'errors' => []], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    public function findById($projetId, array $attribut = ['*'], array $relations = [], array $appends = []): JsonResponse
    {
        try
        {
            $projet = $this->repository->findById($projetId);

            if(isset($projet))
            {
                return response()->json(['statut' => 'success', 'message' => null, 'data' => new ProjetResource($projet), 'statutCode' => Response::HTTP_OK], Response::HTTP_OK);
            }

            else throw new Exception("Cet projet n'existe pas", 400);
        }

        catch (\Throwable $th)
        {
            return response()->json(['statut' => 'error', 'message' => $th->getMessage(), 'errors' => []], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    public function decaissementParAnnee($id, $attributs) : JsonResponse
    {

        try
        {

            if(!($projet = $this->repository->findById($id))) throw new Exception( "Ce projet n'existe pas", 500);

            $total = Decaissement::where('projetId', $projet->id)
                                 ->where('date', '>=', $attributs['annee'].'-01-01')
                                 ->where('date', '<', ($attributs['annee']+1).'-01-01')
                                 ->sum('montant');

            return response()->json(['statut' => 'success', 'message' => null, 'data' => $total, 'statutCode' => Response::HTTP_OK], Response::HTTP_OK);
        }
        catch (\Throwable $th)
        {
            return response()->json(['statut' => 'error', 'message' => $th->getMessage(), 'errors' => []], Response::HTTP_INTERNAL_SERVER_ERROR);
        }

    }

    public function prolonger($projetId, $attributs)
    {

        DB::beginTransaction();

        try
        {
            $projet = $this->repository->findById($projetId);

            $projet->durees()->create(['debut' => $projet->debut, 'fin' => $attributs['fin']]);

            $projet->fin = $attributs['fin'];

            $projet->save();

            $projet = $projet->fresh();

            $acteur = Auth::check() ? Auth::user()->nom . " ". Auth::user()->prenom : "Inconnu";

            $message = $message ?? Str::ucfirst($acteur) . " a prolonger la date de fin du projet " . $projet->nom;

            LogActivity::addToLog("Prolongement de date", $message, get_class($projet), $projet->id);

            DB::commit();

            return response()->json(['statut' => 'success', 'message' => null, 'data' => new ProjetResource($projet), 'statutCode' => Response::HTTP_OK], Response::HTTP_OK);
        }
        catch (\Throwable $th)
        {
            DB::rollback();

            return response()->json(['statut' => 'error', 'message' => $th->getMessage(), 'errors' => []], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }


    public function tef($id, $attributs) : JsonResponse
    {

        try
        {
            if(!($projet = $this->repository->findById($id))) throw new Exception( "Ce projet n'existe pas", 500);

            $montantFinancement = $projet->pret + $projet->budgetNational;

            $activites = $projet->activites();
            $suivis = [];
            $tef = [];

            if($attributs['type'])
            {

                foreach($activites as $activite)
                {
                    array_push($suivis, $activite->suiviFinanciers(date('Y'), null)->sum('consommer')) ;
                   // dd($activite->suiviFinanciers(date('Y'), null));

                }

                foreach($suivis as $suivi)
                {
                    if(!count($tef))
                    {
                        //dd(count($suivis));
                        array_push($tef, ($suivi/$montantFinancement) *100);
                    }

                    else
                    {
                        array_push($tef, (($suivi/$montantFinancement) *100) + $tef[count($tef)-1]);
                    }
                }
            }

            return response()->json(['statut' => 'success', 'message' => null, 'data' => $tef, 'statutCode' => Response::HTTP_OK], Response::HTTP_OK);
        }
        catch (\Throwable $th)
        {
            return response()->json(['statut' => 'error', 'message' => $th->getMessage(), 'errors' => []], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    public function cadreLogique($id) : JsonResponse
    {

        try
        {
            if(!($projet = $this->repository->findById($id))) throw new Exception( "Ce projet n'existe pas", 500);

            $cadreLogique = [
                'objectifGlobaux' => ObjectifSpecifiqueResource::collection($projet->objectifGlobauxes),
                'objectifSpecifique' => ObjectifSpecifiqueResource::collection($projet->objectifSpecifiques),
                'resultat' => ResultatResource::collection($projet->resultats)
            ];

            return response()->json(['statut' => 'success', 'message' => null, 'data' => $cadreLogique, 'statutCode' => Response::HTTP_OK], Response::HTTP_OK);
        }
        catch (\Throwable $th)
        {
            return response()->json(['statut' => 'error', 'message' => $th->getMessage(), 'errors' => []], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
}
