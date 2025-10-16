<?php

namespace App\Services;

use App\Events\NewNotification;
use App\Http\Resources\cadre_de_mesure_rendement\CadreDeMesureRendementResource;
use App\Http\Resources\cadre_de_mesure_rendement\MesureRendementProjetResource;
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
use App\Models\EntrepriseExecutant;
use App\Models\Organisation;
use App\Models\Sinistre;
use App\Models\Site;
use App\Models\Suivi;
use App\Models\SuiviIndicateur;
use App\Models\UniteeDeGestion;
use App\Models\User;
use App\Notifications\FichierNotification;
use App\Repositories\EntrepriseExecutantRepository;
use App\Repositories\OrganisationRepository;
use App\Repositories\SiteRepository;
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
use Illuminate\Database\Eloquent\Collection;
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
            if(Auth::user()->hasRole('bailleur')){
                $projets = Auth::user()->profilable->projets;

            }
            else if(Auth::user()->hasRole('organisation') || ( get_class(auth()->user()->profilable) == Organisation::class)){
                $projets = optional(Auth::user()->profilable)->projet ?? null;
            }
            else if(!(Auth::user()->hasRole('administrateur') || auth()->user()->profilable_type == "App\\Models\\Administrateur")){
                $projets = Auth::user()->programme->projets;
            }
            else{
                $projets = [];
            }

            return response()->json(['statut' => 'success', 'message' => null, 'data' => $projets instanceof Collection ? ProjetResource::collection($projets) : ($projets ? new ProjetResource($projets) : null), 'statutCode' => Response::HTTP_OK], Response::HTTP_OK);
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

            if(isset($attributs['organisationId']) && !empty($attributs['organisationId'])){
                if(!($organisation = app(OrganisationRepository::class)->findById($attributs['organisationId']))) {
                    throw new Exception( "Cette organisation n'existe pas", 500);
                }

                if($organisation->user->programmeId !== Auth::user()->programme->id){
                    throw new Exception( "Cette organisation ne fait pas partir de ce programme", 500);
                }

                if(($projet = $organisation->projet)) {
                    throw new Exception( "Cette organisation est déja associé a un projet dans le programme", 500);
                }

                $owner = $organisation;
            }
            else{
                if(auth()->user()->type != 'unitee-de-gestion'){
                    throw new Exception("Vous n'avez pas les permissions pour effectuer cette action", 1);

                }

                $owner = auth()->user()->profilable;
            }

            $attributs = array_merge($attributs, ['programmeId' => Auth::user()->programme->id, 'statut' => -1]);

            $projet = $organisation->projet()->create($attributs);

            $projet = $projet->fresh();

            if($organisation->fonds()->count()){
                $organisationFond = $organisation->fonds->first()->pivot;
                $organisationFond->budgetAllouer = $attributs['pret'];
                $organisationFond->save();
            }

            if(isset($attributs['sites'])){

                $programme = Auth::user()->programme;

                $sites = [];
                foreach($attributs['sites'] as $id)
                {
                    if(!($site = app(SiteRepository::class)->findById($id))) throw new Exception("Site introuvable", Response::HTTP_NOT_FOUND);

                    array_push($sites, $site->id);
                }

                $projet->sites()->attach($sites, ["programmeId" => $programme->id]);

            }

            /*$statut = ['etat' => -2];

            $statuts = $projet->statuts()->create($statut);*/

            if(isset($attributs['image']))
            {
                $old_image = $projet->chemin;

                $this->storeFile($attributs['image'], 'projets/preview', $projet, null, 'logo');

                if($old_image != null){

                    unlink(public_path("storage/" . $old_image));

                    //$old_image->delete();
                }
            }

            if(isset($attributs['fichier']))
            {
                foreach ($attributs['fichier'] as $key => $file) {

                    $fichier = $this->storeFile($file, 'projets/pieces_jointes', $projet, null, 'fichier');

                    if(array_key_exists('sharedId', $attributs))
                    {
                        foreach($attributs['sharedId'] as $id)
                        {
                            $user = User::findByKey($id);

                            if($user)
                            {
                                $this->storeFile($file, 'projets/pieces_jointes', $projet, null, 'fichier', ['fichierId' => $fichier->id, 'userId' => $user->id]);
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
                }
            }

            $i = 0;

            /*while(array_key_exists('fichier'.$i, $attributs))
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

            if(isset($attributs['fichier']))
            {
                foreach($attributs['fichier'] as $fichier)
                {
                    $this->storeFile($fichier, 'projets', $projet, null, 'fichier');
                }
            }*/

            $projet->refresh();

            $acteur = Auth::check() ? Auth::user()->nom . " ". Auth::user()->prenom : "Inconnu";

            $message = $message ?? Str::ucfirst($acteur) . " a créé un " . strtolower(class_basename($projet));

            //LogActivity::addToLog("Enregistrement", $message, get_class($projet), $projet->id);

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

            if(array_key_exists('programmeId', $attributs))
            {
                if(!($programme = $this->programmeRepository->findById($attributs['programmeId']))) throw new Exception( "Ce programme n'existe pas", 500);

                if($programme->debut > $attributs['debut']) throw new Exception( "La date de début du projet est antérieur à celui du programme", 500);

                if($programme->fin < $attributs['fin']) throw new Exception( "La date de fin du projet est supérieur à celui du programme", 500);

            }
            else{
                $programme = Auth::user()->programme;
            }

            if((!is_object($projetId ))){
                $projet = $this->repository->findById($projetId);
            }
            else {
                $projet = $projetId;
            }

            // Gestion du changement d'organisation
            if(isset($attributs['organisationId']) && !empty($attributs['organisationId'])){
                // Condition 1: La nouvelle organisation doit exister dans le système
                if(!($nouvelleOrganisation = app(OrganisationRepository::class)->findById($attributs['organisationId']))) {
                    throw new Exception("Cette organisation n'existe pas", 500);
                }

                // Condition 2: La nouvelle organisation doit appartenir au même programme que le projet
                if($nouvelleOrganisation->user->programmeId !== $projet->programmeId){
                    throw new Exception("Cette organisation ne fait pas partie du même programme que le projet", 500);
                }

                // Condition 3: La nouvelle organisation ne doit pas déjà avoir un projet
                if($nouvelleOrganisation->projet && $nouvelleOrganisation->projet->id !== $projet->id){
                    throw new Exception("Cette organisation est déjà associée à un projet dans le programme", 500);
                }

                // Condition 4: Le projet ne doit pas avoir de composantes, activités ou données liées
                if($projet->composantes()->count() > 0){
                    throw new Exception("Impossible de changer l'organisation : le projet possède déjà des composantes", 500);
                }

                // Condition 5: Vérifier que le pret du projet ne dépasse pas le fond disponible
                if($nouvelleOrganisation->fonds()->count()){
                    $fond = $nouvelleOrganisation->fonds->first();
                    $pretProjet = isset($attributs['pret']) ? $attributs['pret'] : $projet->pret;

                    // Vérifier que le pret ne dépasse pas le fond disponible
                    if($pretProjet > $fond->fondDisponible){
                        throw new Exception("Le montant de la subvention du projet ne peut pas dépasser le fond disponible", 500);
                    }

                    // Calculer la somme des budgets déjà alloués aux autres organisations de ce fond
                    $totalBudgetAlloue = DB::table('fond_organisations')
                        ->where('fondId', $fond->id)
                        ->whereNull('deleted_at')
                        ->sum('budgetAllouer');

                    // Vérifier que la somme totale ne dépasse pas le fond disponible
                    if(($totalBudgetAlloue + $pretProjet) > $fond->fondDisponible){
                        throw new Exception("La somme des budgets alloués dépasse le fond disponible", 500);
                    }
                }

                // Récupérer l'ancienne organisation
                $ancienneOrganisation = $projet->projetable_type === 'App\Models\Organisation' ? $projet->projetable : null;

                // Mettre à jour le budgetAllouer dans la table pivot fond_organisation
                if($ancienneOrganisation && $ancienneOrganisation->fonds()->count()){
                    // Remettre à 0 le budget alloué de l'ancienne organisation
                    $ancienneOrganisationFond = $ancienneOrganisation->fonds->first()->pivot;
                    $ancienneOrganisationFond->budgetAllouer = 0;
                    $ancienneOrganisationFond->save();
                }

                // Mettre à jour le projetable (polymorphic relationship)
                $projet->projetable_type = 'App\Models\Organisation';
                $projet->projetable_id = $nouvelleOrganisation->id;

                // Allouer le budget à la nouvelle organisation
                if($nouvelleOrganisation->fonds()->count()){
                    $nouvelleOrganisationFond = $nouvelleOrganisation->fonds->first()->pivot;
                    $nouvelleOrganisationFond->budgetAllouer = isset($attributs['pret']) ? $attributs['pret'] : $projet->pret;
                    $nouvelleOrganisationFond->save();
                }

                // Retirer organisationId des attributs car on a déjà géré le changement
                unset($attributs['organisationId']);
            }

            $projet = $projet->fill($attributs);

            $projet->save();

            $organisation = $projet->projetable_type === 'App\Models\Organisation' ? $projet->projetable : null;

            // Mise à jour du budget alloué si l'organisation existe et que le pret a changé
            if($organisation && $organisation->fonds()->count()){
                $organisationFond = $organisation->fonds->first()->pivot;
                $organisationFond->budgetAllouer = $projet->pret;
                $organisationFond->save();
            }

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

                $this->storeFile($attributs['image'], 'projets/preview', $projet, null, 'logo');

                if($old_image != null){

                    if(file_exists(public_path("storage/" . $old_image->chemin)))
                    {
                        unlink(public_path("storage/" . $old_image->chemin));

                        if($old_image){
                            $old_image->delete();
                        }
                    }
                }
            }

            if(isset($attributs['fichier']))
            {

                $piecesJointes = $projet->fichiers();
                foreach ($attributs['fichier'] as $key => $file) {

                    $fichier = $this->storeFile($file, 'projets/pieces_jointes', $projet, null, 'fichier');

                    if(array_key_exists('sharedId', $attributs))
                    {
                        foreach($attributs['sharedId'] as $id)
                        {
                            $user = User::findByKey($id);

                            if($user)
                            {
                                $this->storeFile($file, 'projets/pieces_jointes', $projet, null, 'fichier', ['fichierId' => $fichier->id, 'userId' => $user->id]);
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
                }

                foreach ($piecesJointes as $key => $pieceJointe) {
                    if($pieceJointe != null){

                        if(file_exists(public_path("storage/" . $pieceJointe->chemin)))
                        {
                            unlink(public_path("storage/" . $pieceJointe->chemin));

                            if($pieceJointe){
                                $pieceJointe->delete();
                            }
                        }
                    }
                }
            }

            if(isset($attributs['sites'])){

                $programme = Auth::user()->programme;

                $sites = [];
                foreach($attributs['sites'] as $id)
                {
                    if(!($site = app(SiteRepository::class)->findById($id))) throw new Exception("Site introuvable", Response::HTTP_NOT_FOUND);

                    $sites[$site->id] = ['programmeId' => $programme->id];
                }

                $projet->sites()->sync($sites);

            }

            $projet = $projet->fresh();

            $statuts = $projet->statut;

            $acteur = Auth::check() ? Auth::user()->nom . " ". Auth::user()->prenom : "Inconnu";

            $message = $message ?? Str::ucfirst($acteur) . " a modifié un " . strtolower(class_basename($projet));

            //LogActivity::addToLog("Modification", $message, get_class($projet), $projet->id);

            DB::commit();

            GenererPta::dispatch(Auth::user()->programme)->delay(5);

            return response()->json(['statut' => 'success', 'message' => "Projet {$projet->nom} a ete bien mis a jour", 'data' => new ProjetResource($projet), 'statutCode' => Response::HTTP_OK], Response::HTTP_OK);
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

                $site = optional($projet->sites()->where('sites.programmeId', $projet->programmeId)->first());

                $allprojet = $projet->programme->projets;

                $totale = 0;
                $owners = [];

                foreach($allprojet as $p)
                {
                    $totale += $p->tep;
                    array_push($owners, optional($p->projetable->sigle) ?? "UG");
                }

                $teps = [];

                foreach($allprojet as $p)
                {
                    array_push($teps, round($totale ? ($p->tep*100)/$totale : 0, 2));
                }

                $stats = [
                    "projet_manager" => $projet->projetable->user->type == 'organisation' ? $projet->projetable->sigle : "UG" . " - " . $projet->projetable->user->nom . $projet->projetable->user->prenom,
                    "tep_allProjets" => [
                        'bailleurs' => $owners,
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
                        $query->where("cibleable_type", "App\\Models\\Indicateur")->whereIn('cibleable_id', $projet->indicateurs->pluck('id'));
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
                    //"total_decaissement_bailleur" => $projet->decaissements->where('decaissementable_type', get_class(new Bailleur()))->sum('montant'),
                    //"entreprises" => $projet->bailleur->entrepriseExecutants(),
                    "sites" => $projet->sites,
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

            if( $id !== 'undefined' &&   $id != null ) $projet = $this->repository->findById($id); //Retourner les données du premier projet

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

            //LogActivity::addToLog("Prolongement de date", $message, get_class($projet), $projet->id);

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
                        array_push($tef, round(($suivi/$montantFinancement) *100, 2));
                    }

                    else
                    {
                        array_push($tef, round((($suivi/$montantFinancement) *100) + $tef[count($tef)-1], 2));
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

    public function mesure_rendement($id) : JsonResponse
    {
        try
        {
            if(!($projet = $this->repository->findById($id))) throw new Exception( "Ce projet n'existe pas", 500);

            $mesure_rendement_projet = auth()->user()->programme->mesure_rendement_projet($projet->id)->get();

            //return response()->json(['statut' => 'success', 'message' => null, 'data' => $mesure_rendement_projet, 'statutCode' => Response::HTTP_OK], Response::HTTP_OK);
            return response()->json(['statut' => 'success', 'message' => null, 'data' => MesureRendementProjetResource::collection($mesure_rendement_projet), 'statutCode' => Response::HTTP_OK], Response::HTTP_OK);

        }
        catch (\Throwable $th)
        {
            return response()->json(['statut' => 'error', 'message' => $th->getMessage(), 'errors' => []], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
}
