<?php

namespace App\Services;

use App\Http\Resources\ProjetResource;
use App\Http\Resources\ComposanteResource;
use App\Http\Resources\activites\ActivitesResource;
use App\Http\Resources\PapResource;
use App\Http\Resources\SitesResource;
use App\Http\Resources\bailleurs\BailleursResource;
use App\Http\Resources\cadre_de_mesure_rendement\CadreDeMesureRendementResource;
use App\Http\Resources\CategorieResource;
use App\Http\Resources\EActiviteResource;
use App\Http\Resources\enquetes_de_gouvernance\OrganisationsEnqueteResource;
use App\Http\Resources\MaitriseOeuvreResource;
use App\Http\Resources\mods\ModsResource;
use App\Http\Resources\OrganisationResource;
use App\Http\Resources\TacheResource;
use App\Http\Resources\PassationResource;
use App\Http\Resources\programmes\ProgrammesResource;
use App\Http\Resources\ProjetsResource;
use App\Http\Resources\user\UserResource;
use App\Http\Resources\user\UtilisateurResource;
use App\Jobs\MailRapportJob;
use App\Models\Bailleur;
use App\Models\EntrepriseExecutant;
use App\Models\MissionDeControle;
use App\Models\MOD;
use App\Models\EmailRapport;
use App\Models\Passation;
use App\Models\Projet;
use App\Models\TemplateRapport;
use App\Models\Unitee;
use App\Models\UniteeDeGestion;
use App\Models\User;
use App\Repositories\OrganisationRepository;
use App\Repositories\ProgrammeRepository;
use App\Traits\Helpers\HelperTrait;
use Core\Services\Contracts\BaseService;
use Core\Services\Interfaces\ProgrammeServiceInterface;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Response;
use Exception;
use GuzzleHttp\Client;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use App\Traits\Helpers\LogActivity;

/**
* Interface ProgrammeServiceInterface
* @package Core\Services\Interfaces
*/
class ProgrammeService extends BaseService implements ProgrammeServiceInterface
{

    use HelperTrait;

    /**
     * @var service
     */
    protected $repository;

    /**
     * ProgrammeService constructor.
     *
     * @param ProgrammeRepository $programmeRepository
     */
    public function __construct(ProgrammeRepository $programmeRepository)
    {
        parent::__construct($programmeRepository);
        $this->repository = $programmeRepository;
    }

    public function all(array $attributs = ['*'], array $relations = []): JsonResponse
    {
        try
        {

            return response()->json([';statut' => 'success', 'message' => null, 'data' => ProgrammesResource::collection($this->repository->all()), 'statutCode' => Response::HTTP_OK], Response::HTTP_OK);
        }
        catch (\Throwable $th)
        {
            return response()->json(['statut' => 'error', 'message' => $th->getMessage(), 'errors' => []], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    public function bailleurs($id) : JsonResponse
    {
        try
        {
            $bailleurs = [];

            if($id === 'undefined' || $id === null ){
                if(Auth::user()->hasRole("administrateur", "super-admin")){
                    $bailleurs = Bailleur::all();
                }
            }
            else{

                if(!($programme = $this->repository->findById(Auth::user()->programmeId))) throw new Exception( "Ce programme n'existe pas", 500);

                $bailleurs = $programme->bailleurs->load('profilable')->pluck("profilable");

            }

            return response()->json(['statut' => 'success', 'message' => null, 'data' => BailleursResource::collection($bailleurs), 'statutCode' => Response::HTTP_OK], Response::HTTP_OK);

        }
        catch (\Throwable $th)
        {
            return response()->json(['statut' => 'error', 'message' => $th->getMessage(), 'errors' => []], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    public function structures($id) : JsonResponse
    {
        try
        {
            $structures = [];

            if(!($programme = $this->repository->findById(Auth::user()->programmeId))) throw new Exception( "Ce programme n'existe pas", 500);


            array_push($structures, $programme->uniteeDeGestion);

            if($programme->missionDeControle)
            {
                array_push($structures, $programme->missionDeControle);
            }

            foreach($programme->mods as $mod)
            {
                if($mod)
                {
                    array_push($structures, $mod);
                }
            }

            foreach($programme->entreprisesExecutante as $entreprise)
            {
                if($entreprise)
                {
                    array_push($structures, $entreprise);
                }
            }

            foreach($programme->institutions as $institution)
            {
                if($institution)
                {
                    array_push($structures, $institution);
                }
            }

            return response()->json(['statut' => 'success', 'message' => null, 'data' => $structures, 'statutCode' => Response::HTTP_OK], Response::HTTP_OK);

        }
        catch (\Throwable $th)
        {
            return response()->json(['statut' => 'error', 'message' => $th->getMessage(), 'errors' => []], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    public function mods($id) : JsonResponse
    {
        try
        {
            $mods = [];

            if( $id !== null ) $programme = $this->repository->findById(Auth::user()->programmeId); //Retourner les données du premier projet

            if( !($programme) ) throw new Exception( "Ce programme n'existe pas", 500);

            $mods = $programme->mods->load('profilable')->pluck("profilable");

            return response()->json(['statut' => 'success', 'message' => null, 'data' => ModsResource::collection($mods), 'statutCode' => Response::HTTP_OK], Response::HTTP_OK);
        }
        catch (\Throwable $th)
        {
            return response()->json(['statut' => 'error', 'message' => $th->getMessage(), 'errors' => []], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    public function entreprisesExecutante($id) : JsonResponse
    {
        try
        {
            $entreprisesExecutante = [];

            if( $id !== null ) $programme = $this->repository->findById(Auth::user()->programmeId); //Retourner les données du premier projet

            if( !($programme) ) throw new Exception( "Ce programme n'existe pas", 500);

            if(Auth::user()->hasRole('bailleur'))
            {
                $entreprisesExecutante = [];

                foreach(Auth::user()->profilable->sites as $site)
                {
                    foreach($site->entreprisesExecutant as $entreprise)
                    {
                        array_push($entreprisesExecutant, $entreprise);
                    }
                }
            }

            else
            {
                $entreprisesExecutante = $programme->entreprisesExecutante->load('profilable')->pluck("profilable");
            }

            return response()->json(['statut' => 'success', 'message' => null, 'data' => UtilisateurResource::collection($entreprisesExecutante), 'statutCode' => Response::HTTP_OK], Response::HTTP_OK);
        }
        catch (\Throwable $th)
        {
            return response()->json(['statut' => 'error', 'message' => $th->getMessage(), 'errors' => []], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    public function projets($id) : JsonResponse
    {
        try
        {
            $projets = [];

            if( $id !== null ) $programme = $this->repository->findById(Auth::user()->programmeId); //Retourner les données du premier projet

            if( !($programme) ) throw new Exception( "Ce programme n'existe pas", 500);

            if(Auth::user()->profilable_type == get_class(new Bailleur))
                return response()->json([';statut' => 'success', 'message' => null, 'data' => Auth::user()->profilable->projets ? new ProjetResource((Auth::user()->profilable->projets)) : [], 'statutCode' => Response::HTTP_OK], Response::HTTP_OK);

            else if(Auth::user()->profilable_type == get_class(new EntrepriseExecutant()))
            {
                $projets = [];

                foreach(Auth::user()->profilable->bailleurs() as $bailleur)
                {
                    array_push($projets, $bailleur->projets);
                }
            }

            else
            {
                $projets = $programme->projets;
            }

            return response()->json(['statut' => 'success', 'message' => null, 'data' => ProjetsResource::collection($projets), 'statutCode' => Response::HTTP_OK], Response::HTTP_OK);
        }
        catch (\Throwable $th)
        {
            return response()->json(['statut' => 'error', 'message' => $th->getMessage(), 'errors' => []], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    public function composantes($id) : JsonResponse
    {
        try
        {
            if(!($programme = $this->repository->findById(Auth::user()->programmeId))) throw new Exception( "Ce programme n'existe pas", 500);

            $composantes = $programme->composantes();

            return response()->json(['statut' => 'success', 'message' => null, 'data' => ComposanteResource::collection($composantes), 'statutCode' => Response::HTTP_OK], Response::HTTP_OK);
        }
        catch (\Throwable $th)
        {
            return response()->json(['statut' => 'error', 'message' => $th->getMessage(), 'errors' => []], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    public function sousComposantes($id) : JsonResponse
    {

        try
        {

            if(!($programme = $this->repository->findById(Auth::user()->programmeId))) throw new Exception( "Ce programme n'existe pas", 500);

            $composantes = $programme->sousComposantes();

            return response()->json(['statut' => 'success', 'message' => null, 'data' => ComposanteResource::collection($composantes), 'statutCode' => Response::HTTP_OK], Response::HTTP_OK);
        }
        catch (\Throwable $th)
        {
            return response()->json(['statut' => 'error', 'message' => $th->getMessage(), 'errors' => []], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    public function activites($id) : JsonResponse
    {

        try
        {

            if(!($programme = $this->repository->findById(Auth::user()->programmeId))) throw new Exception( "Ce programme n'existe pas", 500);

            $activites = $programme->activites();

            return response()->json(['statut' => 'success', 'message' => null, 'data' => ActivitesResource::collection($activites), 'statutCode' => Response::HTTP_OK], Response::HTTP_OK);
        }
        catch (\Throwable $th)
        {
            return response()->json(['statut' => 'error', 'message' => $th->getMessage(), 'errors' => []], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    public function eActivites($id) : JsonResponse
    {

        try
        {

            if(!($programme = $this->repository->findById(Auth::user()->programmeId))) throw new Exception( "Ce programme n'existe pas", 500);

            $activites = $programme->eActivites;


            return response()->json(['statut' => 'success', 'message' => null, 'data' => EActiviteResource::collection($activites), 'statutCode' => Response::HTTP_OK], Response::HTTP_OK);
        }
        catch (\Throwable $th)
        {
            return response()->json(['statut' => 'error', 'message' => $th->getMessage(), 'errors' => []], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }


    public function taches($id) : JsonResponse
    {

        try
        {

            if(!($programme = $this->repository->findById(Auth::user()->programmeId))) throw new Exception( "Ce programme n'existe pas", 500);

            $taches = $programme->taches();

            return response()->json(['statut' => 'success', 'message' => null, 'data' => TacheResource::collection($taches), 'statutCode' => Response::HTTP_OK], Response::HTTP_OK);
        }
        catch (\Throwable $th)
        {
            return response()->json(['statut' => 'error', 'message' => $th->getMessage(), 'errors' => []], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    public function scopes($programmeId): JsonResponse
    {

        try
        {
            $ptabScopes = [];

            if(is_object($programmeId))
            {
                $ptabScopes = $programmeId->ptabScopes->sortByDesc('created_at')->values();
            }else{

                if(!($programme = $this->repository->findById(Auth::user()->programmeId))) throw new Exception( "Ce programme n'existe pas", 500);
                $ptabScopes = $programme->ptabScopes->sortByDesc('created_at')->values();
            }

            $ptabScopes = $ptabScopes->map(function($ptabScope){
                return [
                    "id" => $ptabScope->secure_id,
                    "nom" => $ptabScope->nom,
                    "slug" => $ptabScope->slug,
                    "created_at" => $ptabScope->created_at,
                    "programmeId" => $ptabScope->programme->secure_id
                ];
            });

            return response()->json(['statut' => 'success', 'message' => null, 'data' => $ptabScopes, 'statutCode' => Response::HTTP_OK], Response::HTTP_OK);
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

            if(!($programme = $this->repository->findById(Auth::user()->programmeId))) throw new Exception( "Ce programme n'existe pas", 500);

            if(Auth::user()->hasRole('bailleur'))
            {
                $decaissements = Auth::user()->profilable->projets(1)->decaissements;
            }

            else
            {
                $decaissements = $programme->decaissements() ;
            }

            return response()->json(['statut' => 'success', 'message' => null, 'data' => $decaissements, 'statutCode' => Response::HTTP_OK], Response::HTTP_OK);
        }
        catch (\Throwable $th)
        {
            return response()->json(['statut' => 'error', 'message' => $th->getMessage(), 'errors' => []], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    public function sinistres($id) : JsonResponse
    {

        try
        {


            if(!($programme = $this->repository->findById(Auth::user()->programmeId))) throw new Exception( "Ce programme n'existe pas", 500);

            if(Auth::user()->hasRole('bailleur'))
            {
                $sinistres = [];

                foreach(Auth::user()->profilable->sites as $site)
                {
                    foreach($site->sinistres->sortByDesc('created_at') as $sinistre)
                    {
                        array_push($sinistres, $sinistre);
                    }
                }
            }

            else
            {
                $sinistres = $programme->sinistres->sortByDesc('created_at');
            }

            $total = 0;

            foreach($sinistres as $sinistre)
            {
                $total += $sinistre->payer;
            }

            $sites = [];

            foreach($sinistres as $sinistre)
            {
                $controle = 1;
                $site = $sinistre->site;

                foreach($sites as $key => $s)
                {

                    if($s['id'] == $site->id)
                    {
                        $sites[$key]['total']+= $sinistre->payer;
                        $controle = 0;
                    }
                }

                if($controle)
                {
                    array_push($sites, [
                        'id' => $site->id,
                        'nom' => $site->nom,
                        'bailleur' => $site->bailleurs->first()->sigle,
                        'bailleurCouleur' => $site->bailleurs->first()->projets->couleur,
                        'total' => $sinistre->payer
                    ]);
                }
            }

            $data = [
                'sinistres' => PapResource::collection($sinistres),
                'total' => $total,
                'sites' => $sites
            ];

            return response()->json(['statut' => 'success', 'message' => null, 'data' => $data, 'statutCode' => Response::HTTP_OK], Response::HTTP_OK);
        }
        catch (\Throwable $th)
        {
            return response()->json(['statut' => 'error', 'message' => $th->getMessage(), 'errors' => []], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    public function sites($id) : JsonResponse
    {
        try
        {
            if(!($programme = $this->repository->findById($id))) throw new Exception( "Ce programme n'existe pas", 500);

            $sites = $programme->sites()->with(['projets'])->get();

            return response()->json(['statut' => 'success', 'message' => null, 'data' => SitesResource::collection($sites), 'statutCode' => Response::HTTP_OK], Response::HTTP_OK);
        }
        catch (\Throwable $th)
        {
            return response()->json(['statut' => 'error', 'message' => $th->getMessage(), 'errors' => []], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    public function categories($id) : JsonResponse
    {
        try
        {
            if(!($programme = $this->repository->findById($id))) throw new Exception( "Ce programme n'existe pas", 500);

            $categoriesProgramme = $programme->categories;

            return response()->json(['statut' => 'success', 'message' => null, 'data' => CategorieResource::collection($categoriesProgramme), 'statutCode' => Response::HTTP_OK], Response::HTTP_OK);
        }
        catch (\Throwable $th)
        {
            return response()->json(['statut' => 'error', 'message' => $th->getMessage(), 'errors' => []], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    public function cadre_de_mesure_rendement($id) : JsonResponse
    {
        try
        {
            if(!($programme = $this->repository->findById($id))) throw new Exception( "Ce programme n'existe pas", 500);

            $cadre_de_mesure_rendement = $programme->cadre_de_mesure_rendement;

            //return response()->json(['statut' => 'success', 'message' => null, 'data' => $cadre_de_mesure_rendement, 'statutCode' => Response::HTTP_OK], Response::HTTP_OK);
            return response()->json(['statut' => 'success', 'message' => null, 'data' => CadreDeMesureRendementResource::collection($cadre_de_mesure_rendement), 'statutCode' => Response::HTTP_OK], Response::HTTP_OK);

        }
        catch (\Throwable $th)
        {
            return response()->json(['statut' => 'error', 'message' => $th->getMessage(), 'errors' => []], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    public function scores_au_fil_du_temps($organisationId = null) : JsonResponse
    {
        try
        {
            $programme = auth()->user()->programme;

            if(auth()->user()->type=="organisation"){
                $organisationId = optional(auth()->user()->profilable)->id;
            }
            else if($organisationId == null){
                throw new Exception("Organisation introuvable dans le programme.", Response::HTTP_NOT_FOUND);
            }

            if($organisationId){

                if (!(($organisation = app(OrganisationRepository::class)->findById($organisationId)))) {
                    throw new Exception("Organisation introuvable dans le programme.", Response::HTTP_NOT_FOUND);
                }
                /* if (!(($organisation = app(OrganisationRepository::class)->findById($organisationId)) && $programme->evaluations_de_gouvernance_organisations($organisation->id)->first())) {
                    throw new Exception("Organisation introuvable dans le programme.", Response::HTTP_NOT_FOUND);
                } */
            }

            $scores = $programme->stats_evaluations_de_gouvernance_organisations($organisation->id)
                ->map(function ($organisation) use ($programme) {
                    //$evaluations_scores = $programme->evaluations_de_gouvernance->mapWithKeys(function ($evaluationDeGouvernance) use ($organisation) {

                    $evaluations_scores = $programme->enquetes_de_gouvernance->mapWithKeys(function ($evaluationDeGouvernance) use ($organisation) {
                        // Key-value pairing for each year with scores
                        $results = $organisation->profiles($evaluationDeGouvernance->id)->first()->resultat_synthetique ?? [];

                        return [$evaluationDeGouvernance->annee_exercice => $results];
                    });

                    // Merge evaluation scores with organizational metadata
                    return [
                        'id' => $organisation->secure_id,
                        'intitule' => $organisation->sigle . " - " . $organisation->user->nom,
                        'scores' => $evaluations_scores,
                    ];
                })
                ->values(); // Reset keys for a clean JSON output

            return response()->json(['statut' => 'success', 'message' => null, 'data' => $scores, 'statutCode' => Response::HTTP_OK], Response::HTTP_OK);

        }
        catch (\Throwable $th)
        {
            return response()->json(['statut' => 'error', 'message' => $th->getMessage(), 'errors' => []], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    public function scores_au_fil_du_temps_reviser($organisationId = null) : JsonResponse
    {
        try
        {
            $programme = auth()->user()->programme;

            if(auth()->user()->type=="organisation"){
                $organisationId = optional(auth()->user()->profilable)->id;
            }
            else if($organisationId == null){
                throw new Exception("Organisation introuvable dans le programme.", Response::HTTP_NOT_FOUND);
            }

            if($organisationId){

                if (!(($organisation = app(OrganisationRepository::class)->findById($organisationId)))) {
                    throw new Exception("Organisation introuvable dans le programme.", Response::HTTP_NOT_FOUND);
                }
            } else {
                 throw new Exception("Organisation introuvable dans le programme.", Response::HTTP_NOT_FOUND);
            }


            $scores_by_organisation = collect();

            if ($organisation) {
                // Fetch and sort all evaluations for the program
                $all_programme_evaluations = $programme->enquetes_de_gouvernance()
                    ->orderBy('annee_exercice', 'asc')
                    ->orderBy('debut', 'asc')
                    ->get();

                // Calculate scores for this specific organisation
                $evaluations_scores_by_year = $all_programme_evaluations->map(function ($programme_evaluation_de_gouvernance) use ($organisation) {
                    return [
                        'annee' => $programme_evaluation_de_gouvernance->annee_exercice,
                        'evaluation' => [
                            'id' => $programme_evaluation_de_gouvernance->secure_id,
                            'intitule' => $programme_evaluation_de_gouvernance->intitule,
                            'resultats' => $organisation->profiles($programme_evaluation_de_gouvernance->id)->first()->resultat_synthetique ?? []
                        ]
                    ];
                })->groupBy('annee') // Group by year
                ->map(function ($yearly_evaluations_data) {
                    // Return the list of evaluations for this year
                    return $yearly_evaluations_data->pluck('evaluation')->values();
                });

                // Merge evaluation scores with organizational metadata
                $scores_by_organisation->push([
                    'id' => $organisation->secure_id,
                    'intitule' => $organisation->sigle . " - " . $organisation->user->nom,
                    'scores' => $evaluations_scores_by_year, // Assign the grouped scores
                ]);
            }


            return response()->json(['statut' => 'success', 'message' => null, 'data' => $scores_by_organisation->values(), 'statutCode' => Response::HTTP_OK], Response::HTTP_OK);

        }
        catch (\Throwable $th)
        {
            return response()->json(['statut' => 'error', 'message' => $th->getMessage(), 'errors' => []], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    public function evaluations_organisations() : JsonResponse
    {
        try
        {
            $programme = auth()->user()->programme;

            $organisations = $programme->evaluations_de_gouvernance_organisations();

            return response()->json(['statut' => 'success', 'message' => null, 'data' => OrganisationResource::collection($organisations), 'statutCode' => Response::HTTP_OK], Response::HTTP_OK);

        }
        catch (\Throwable $th)
        {
            return response()->json(['statut' => 'error', 'message' => $th->getMessage(), 'errors' => []], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    public function stats_evaluations_de_gouvernance_organisations() : JsonResponse
    {
        try
        {
            $programme = auth()->user()->programme;

            $organisations = $programme->stats_evaluations_de_gouvernance_organisations();

            return response()->json(['statut' => 'success', 'message' => null, 'data' => OrganisationsEnqueteResource::collection($organisations), 'statutCode' => Response::HTTP_OK], Response::HTTP_OK);

        }
        catch (\Throwable $th)
        {
            return response()->json(['statut' => 'error', 'message' => $th->getMessage(), 'errors' => []], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }



    public function suiviFinanciers($id) : JsonResponse
    {

        try
        {
            $suiviFinanciers = [];

            if(!($programme = $this->repository->findById(Auth::user()->programmeId))) throw new Exception( "Ce programme n'existe pas", 500);

            if(Auth::user()->hasRole('bailleur'))
            {
                $projet = Projet::where('bailleurId', Auth::user()->profilable->id)->first();

                $activites = $projet->activites();

                foreach($activites as $activite)
                {
                    $suivi = $projet->bailleur->suiviFinanciers->where('activiteId', $activite->id)
                                                           ->where('trimestre', 1)
                                                           ->where('annee', date('Y'))
                                                           ->first();

                    if(!$suivi) continue;

                    $plan = $activite->planDeDecaissement(1, date('Y'));

                    $periode = [
                        "budjet" => $plan['pret'],
                        "consommer" => $suivi->consommer,
                        "disponible" => $plan['pret'] - $suivi->consommer,
                        "pourcentage" => $plan['pret'] != 0 ? round(($suivi->consommer*100)/$plan['pret'],2) : 0
                    ];

                    $planParAnnee = $activite->planDeDecaissementParAnnee(date('Y'));
                    $consommerParAnnee = $projet->bailleur->suiviFinanciers->where('activiteId', $activite->id)
                                                                        ->where('annee', date('Y'))
                                                                        ->sum('consommer');

                    $exercice = [
                        "budjet" => $planParAnnee['pret'],
                        "consommer" => $consommerParAnnee,
                        "disponible" => $planParAnnee['pret'] - $consommerParAnnee,
                        "pourcentage" => $planParAnnee['pret'] != 0 ? round(($consommerParAnnee*100)/$planParAnnee['pret'],2) : 0
                    ];

                    $planCumul = $activite->planDeDecaissements->sum('pret');
                    $consommerCumul = $projet->bailleur->suiviFinanciers->where('activiteId', $activite->id)
                                                                    ->sum('consommer');

                    $cumul = [
                        "budjet" => $planCumul,
                        "consommer" => $consommerCumul,
                        "disponible" => $planCumul - $consommerCumul,
                        "pourcentage" => $planCumul != 0 ? round(($consommerCumul*100)/$planCumul,2) : 0
                    ];

                    $objet = [
                        "bailleur" => $projet->bailleur->sigle,
                        "trimestre" => 1,
                        "annee" => date('Y'),
                        "activite" => new ActivitesResource($activite),
                        "periode" => $periode,
                        "exercice" => $exercice,
                        "cumul" => $cumul
                    ];

                    array_push($suiviFinanciers, $objet);
                }
            }

            else
            {
                $projets = Auth::user()->programme->projets;

                foreach($projets as $projet)
                {
                    $activites = $projet->activites();

                    foreach($activites as $activite)
                    {
                        $suivi = $projet->bailleur->suiviFinanciers->where('activiteId', $activite->id)
                                                               ->where('trimestre', 1)
                                                               ->where('annee', date('Y'))
                                                               ->first();

                        if(!$suivi) continue;

                        $plan = $activite->planDeDecaissement(1, date('Y'));

                        $periode = [
                            "budjet" => $plan['pret'],
                            "consommer" => $suivi->consommer,
                            "disponible" => $plan['pret'] - $suivi->consommer,
                            "pourcentage" => $plan['pret'] != 0 ? round(($suivi->consommer*100)/$plan['pret'],2) : 0 . " %"
                        ];

                        $planParAnnee = $activite->planDeDecaissementParAnnee(date('Y'));
                        $consommerParAnnee = $projet->bailleur->suiviFinanciers->where('activiteId', $activite->id)
                                                                            ->where('annee', date('Y'))
                                                                            ->sum('consommer');

                        $exercice = [
                            "budjet" => $planParAnnee['pret'],
                            "consommer" => $consommerParAnnee,
                            "disponible" => $planParAnnee['pret'] - $consommerParAnnee,
                            "pourcentage" => $planParAnnee['pret'] != 0 ? round(($consommerParAnnee*100)/$planParAnnee['pret'],2) : 0 . " %"
                        ];

                        $planCumul = $activite->planDeDecaissements->sum('pret');
                        $consommerCumul = $projet->bailleur->suiviFinanciers->where('activiteId', $activite->id)
                                                                        ->sum('consommer');

                        $cumul = [
                            "budjet" => $planCumul,
                            "consommer" => $consommerCumul,
                            "disponible" => $planCumul - $consommerCumul,
                            "pourcentage" => $planCumul != 0 ? round(($consommerCumul*100)/$planCumul,2) : 0 . " %"
                        ];

                        $objet = [
                            "bailleur" => $projet->bailleur->sigle,
                            "trimestre" => 1,
                            "annee" => date('Y'),
                            "activite" => new ActivitesResource($activite),
                            "periode" => $periode,
                            "exercice" => $exercice,
                            "cumul" => $cumul
                        ];

                        array_push($suiviFinanciers, $objet);
                    }
                }
            }

            $projets = [];

            foreach(Auth::user()->programme->suiviFinanciers as $suiviFinancier)
            {
                $controle = 1;
                $projet = $suiviFinancier->activite->composante->projet;

                foreach($projets as $key => $p)
                {

                    if($p['id'] == $projet->id)
                    {
                        $projets[$key]['total']+= $suiviFinancier->consommer;
                        $controle = 0;
                    }
                }

                if($controle)
                {
                    array_push($projets, [
                        'id' => $projet->id,
                        'nom' => $projet->nom,
                        'total' => $suiviFinancier->consommer
                    ]);
                }
            }

            $data = [
                'suiviFinanciers' => $suiviFinanciers,
                'total' => $programme->suiviFinanciers->sum('consommer'),
                'projets' => $projets,
                'annee' => date('Y')
            ];

            return response()->json(['statut' => 'success', 'message' => null, 'data' => $data, 'statutCode' => Response::HTTP_OK], Response::HTTP_OK);
        }
        catch (\Throwable $th)
        {
            return response()->json(['statut' => 'error', 'message' => $th->getMessage(), 'errors' => []], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    public function modPassations($id) : JsonResponse
    {

        try
        {

            if(!($programme = $this->repository->findById(Auth::user()->programmeId))) throw new Exception( "Ce programme n'existe pas", 500);

            $passations = Passation::where('programmeId', $programme->id)->
                                     where('passationable_type', get_class(new MOD()))->
                                     get();

            return response()->json(['statut' => 'success', 'message' => null, 'data' => PassationResource::collection($passations), 'statutCode' => Response::HTTP_OK], Response::HTTP_OK);
        }
        catch (\Throwable $th)
        {
            return response()->json(['statut' => 'error', 'message' => $th->getMessage(), 'errors' => []], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    public function missionDeControlePassations($id) : JsonResponse
    {

        try
        {

            if(!($programme = $this->repository->findById(Auth::user()->programmeId))) throw new Exception( "Ce programme n'existe pas", 500);

            $passations = Passation::where('programmeId', $programme->id)->
                                     where('passationable_type', get_class(new MissionDeControle()))->
                                     get();

            return response()->json(['statut' => 'success', 'message' => null, 'data' => PassationResource::collection($passations), 'statutCode' => Response::HTTP_OK], Response::HTTP_OK);
        }
        catch (\Throwable $th)
        {
            return response()->json(['statut' => 'error', 'message' => $th->getMessage(), 'errors' => []], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    public function maitriseOeuvres($id) : JsonResponse
    {

        try
        {

            if(!($programme = $this->repository->findById(Auth::user()->programmeId))) throw new Exception( "Ce programme n'existe pas", 500);

            $maitriseOeuvres = $programme->maitriseOeuvres;

            return response()->json(['statut' => 'success', 'message' => null, 'data' => MaitriseOeuvreResource::collection($maitriseOeuvres), 'statutCode' => Response::HTTP_OK], Response::HTTP_OK);
        }
        catch (\Throwable $th)
        {
            return response()->json(['statut' => 'error', 'message' => $th->getMessage(), 'errors' => []], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    public function users($id) : JsonResponse
    {

        try
        {

            if(!($programme = $this->repository->findById(Auth::user()->programmeId))) throw new Exception( "Ce programme n'existe pas", 500);

            $users = $programme->users;

            return response()->json(['statut' => 'success', 'message' => null, 'data' => UserResource::collection($users), 'statutCode' => Response::HTTP_OK], Response::HTTP_OK);
        }
        catch (\Throwable $th)
        {
            return response()->json(['statut' => 'error', 'message' => $th->getMessage(), 'errors' => []], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    public function entrepriseUsers($id) : JsonResponse
    {

        try
        {

            $programme = Auth::user()->programme;

            $entreprise = EntrepriseExecutant::findByKey($id);
            $user = $entreprise->user;

            $users = User::where('programmeId', $programme->id)->
                           where('profilable_type', $user->profilable_type)->
                           where('profilable_id', $user->profilable_id)->
                           where('id', '!=', $user->id)->
                           get();

            return response()->json(['statut' => 'success', 'message' => null, 'data' => UserResource::collection($users), 'statutCode' => Response::HTTP_OK], Response::HTTP_OK);
        }
        catch (\Throwable $th)
        {
            return response()->json(['statut' => 'error', 'message' => $th->getMessage(), 'errors' => []], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    public function kobo() : JsonResponse
    {

        try
        {
            $file = Storage::disk('local')->get('kobo/kobo.json');

            if($file != "")
            {

                $kobo = json_decode($file);

                return response()->json(['statut' => 'success', 'message' => null, 'data' => $kobo, 'statutCode' => Response::HTTP_OK], Response::HTTP_OK);
            }

            else
            {
                Artisan::call('kobo');

                $file = Storage::disk('local')->get('kobo/kobo.json');

                $kobo = json_decode($file);

                return response()->json(['statut' => 'success', 'message' => null, 'data' => $kobo, 'statutCode' => Response::HTTP_OK], Response::HTTP_OK);
            }
        }
        catch (\Throwable $th)
        {
            return response()->json(['statut' => 'error', 'message' => $th->getMessage(), 'errors' => []], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    public function koboUpdate() : JsonResponse
    {

        try
        {

            Artisan::call('kobo');

            $file = Storage::disk('local')->get('kobo/kobo.json');

            $kobo = json_decode($file);

            return response()->json(['statut' => 'success', 'message' => null, 'data' => $kobo, 'statutCode' => Response::HTTP_OK], Response::HTTP_OK);

        }
        catch (\Throwable $th)
        {
            return response()->json(['statut' => 'error', 'message' => $th->getMessage(), 'errors' => []], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    public function koboSuivie($attributs) : JsonResponse
    {

        try
        {

            $client = new Client();
            $url = $attributs['url'].'data';
            $json = [];

            do {
                $response = $client->get($url, [
                    'headers' => [
                        'Authorization' => "Token 3d4b08315551155a0e8cbccc395bc54ea77a97d5",
                        'Conten-Type' => "application/json",
                        "Accept" => "application/json"
                    ],
                ]);

                $responseJson = json_decode($response->getBody()->getContents());

                foreach($responseJson->results as $result)
                {
                    array_push($json, $result);
                }

                $url = $responseJson->next;



            } while ($url);


            return response()->json(['statut' => 'success', 'message' => null, 'data' => $json, 'statutCode' => Response::HTTP_OK], Response::HTTP_OK);

        }
        catch (\Throwable $th)
        {
            return response()->json(['statut' => 'error', 'message' => $th->getMessage(), 'errors' => []], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    public function koboPreview($attributs) : JsonResponse
    {

        try
        {

            $client = new Client();
            $url = $attributs['url'].'versions/'.$attributs['deployed_version_id'];

                $response = $client->get($url, [
                    'headers' => [
                        'Authorization' => "Token 3d4b08315551155a0e8cbccc395bc54ea77a97d5",
                        'Conten-Type' => "application/json",
                        "Accept" => "application/json"
                    ],
                ]);

                $responseJson = json_decode($response->getBody()->getContents());



            return response()->json(['statut' => 'success', 'message' => null, 'data' => $responseJson->content, 'statutCode' => Response::HTTP_OK], Response::HTTP_OK);

        }
        catch (\Throwable $th)
        {
            return response()->json(['statut' => 'error', 'message' => $th->getMessage(), 'errors' => []], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    public function dashboard() : JsonResponse
    {

        try
        {
            $stat = [];

            if(!($programme = $this->repository->findById(Auth::user()->programmeId))) throw new Exception( "Ce programme n'existe pas", 500);

            $nbreProjets = 0;
            $montantTotal = 0;
            $montantDecaisse = 0;
            $montantDepense = 0;
            $nbreActivite = 0;
            $nbreActiviteRealise = 0;
            $executionFinanciers = [];
            $paps = [];
            $teps = [];
            $indicateurs = [];
            $min = (int)Carbon::parse($programme->debut)->format("Y");
            $max = (int)Carbon::parse($programme->fin)->format("Y");

            foreach($programme->projets as $projet)
            {
                array_push($executionFinanciers, [
                    'sigle' => optional($projet->projetable->sigle) ?? "UG",
                    'montantTotal' => $projet->budgetNational + $projet->pret,
                    'montantDecaisse' => $projet->decaissements->pluck('montant')->sum(),
                    'montantDepense' => collect($projet->suiviFinanciers())->pluck('consommer')->sum()
                ]);

                $sites = $projet->sites()->where('sites.programmeId', $programme->id)->get();
                $paps = array_merge($paps, [
                    optional($projet->projetable->sigle) ?? "UG" => [
                        'annee' => [],
                        'montant' => [],
                        'nombre' => []
                    ]
                ]);

                $teps = array_merge($teps, [
                    optional($projet->projetable->sigle) ?? "UG" => [
                        'annee' => [],
                        'teps' => []
                    ]
                ]);

                for($i = $min; $i <= $max; $i++)
                {
                    array_push($paps[optional($projet->projetable->sigle) ?? "UG"]['annee'], $i);
                    array_push($teps[optional($projet->projetable->sigle) ?? "UG"]['annee'], $i);
                    $montant = 0;
                    $nombre = 0;

                    foreach($sites as $site)
                    {
                        foreach($site->sinistres->where('dateDePaiement', '>=', $i.'-01-01')->where('dateDePaiement', '<=', $i.'-12-31') as $sinistre)
                        {
                            $montant += $sinistre->payer;
                            $nombre++;
                        }
                    }
                    array_push($paps[optional($projet->projetable->sigle) ?? "UG"]['montant'], $montant);
                    array_push($paps[optional($projet->projetable->sigle) ?? "UG"]['nombre'], $nombre);

                    $total = 0;
                    $effectue = 0;

                    foreach($projet->allComposantes as $composante)
                    {
                        foreach($composante->activites as $activite)
                        {
                            if($activite->durees->last()->debut >= $i.'-01-01' && $activite->durees->last()->fin <= $i.'-12-31')
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

                    array_push($teps[optional($projet->projetable->sigle) ?? "UG"]['teps'], $total ? $effectue * 100 / $total : 0);
                }

            }

            foreach($executionFinanciers as $data)
            {
                $montantTotal += $data['montantTotal'];
                $montantDecaisse += $data['montantDecaisse'];
                $montantDepense += $data['montantDepense'];
                $nbreProjets++;
            }

            foreach($programme->activites() as $activite)
            {
                if($activite->statut == 2)
                {
                    $nbreActiviteRealise++;
                }

                $nbreActivite++;

            }

            foreach(Unitee::where('type', 1)->get() as $unitee)
            {
                foreach($unitee->indicateurs->where('programmeId', Auth::user()->programmeId) as $indicateur)
                {
                    $indicateurs = array_merge($indicateurs, [
                        $indicateur->secure_id => [
                            'indicateur' => $indicateur->nom,
                            'annee' => [],
                            'suivis' => []
                        ]
                    ]);

                    for($i = $min; $i <= $max; $i++)
                    {
                        array_push($indicateurs[$indicateur->secure_id]['annee'], $i);
                        $cumul = [];
                        $total = 0;

                        $data = $indicateur->suivis->where('annee', $i)->first();

                        if(!$data)
                        {
                            array_push($indicateurs[$indicateur->secure_id]['suivis'], $cumul);
                            continue;
                        }


                        foreach($data->suivisIndicateur[0]->valeurRealise as $key => $valeur)
                        {

                            $total = $valeur;

                            foreach($data->suivisIndicateur as $keytwo => $suivi)
                            {

                                if($keytwo)
                                {
                                    $total += $suivi->valeurRealise[$key];
                                }

                            }

                            array_push($cumul, $total);
                        }


                        array_push($indicateurs[$indicateur->secure_id]['suivis'], $cumul);
                    }
                }
            }

            $stat = [
                'nbreProjets' => $nbreProjets,
                //'nbreBailleur' => $programme->bailleurs->count(),
                //'nbreOscs' => $programme->projets->count(),//->loadCount(["projetable" => function($query) {$query->whereNot("projetable_type", UniteeDeGestion::class);}]),
                'nbreActivite' => $nbreActivite,
                "nbreActiviteRealise" => $nbreActiviteRealise,
                "montantTotal" => $montantTotal,
                "montantDecaisse" => $montantDecaisse,
                "montantDepense" => $montantDepense,
                "executionFinanciers" =>$executionFinanciers,
                "paps" => $paps,
                'teps' => $teps,
                "indicateurs" => $indicateurs
            ];

            return response()->json(['statut' => 'success', 'message' => null, 'data' => $stat, 'statutCode' => Response::HTTP_OK], Response::HTTP_OK);

        }
        catch (\Throwable $th)
        {
            return response()->json(['statut' => 'error', 'message' => $th->getMessage(), 'errors' => []], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    public function oldDashboard() : JsonResponse
    {

        try
        {
            $stat = [];

            if(!($programme = $this->repository->findById(Auth::user()->programmeId))) throw new Exception( "Ce programme n'existe pas", 500);

            $nbreProjets = 0;
            $montantTotal = 0;
            $montantDecaisse = 0;
            $montantDepense = 0;
            $nbreActivite = 0;
            $nbreActiviteRealise = 0;
            $executionFinanciers = [];
            $paps = [];
            $teps = [];
            $indicateurs = [];
            $min = (int)Carbon::parse($programme->debut)->format("Y");
            $max = (int)Carbon::parse($programme->fin)->format("Y");


            foreach($programme->projets as $projet)
            {
                array_push($executionFinanciers, [
                    'sigle' => $projet->bailleur->sigle,
                    'montantTotal' => $projet->budgetNational + $projet->pret,
                    'montantDecaisse' => $projet->decaissements->pluck('montant')->sum(),
                    'montantDepense' => collect($projet->suiviFinanciers())->pluck('consommer')->sum()
                ]);

                $sites = $projet->bailleur->sites()->where('programmeId', $programme->id)->get();
                $paps = array_merge($paps, [
                    $projet->bailleur->sigle => [
                        'annee' => [],
                        'montant' => [],
                        'nombre' => []
                    ]
                ]);

                $teps = array_merge($teps, [
                    $projet->bailleur->sigle => [
                        'annee' => [],
                        'teps' => []
                    ]
                ]);

                for($i = $min; $i <= $max; $i++)
                {
                    array_push($paps[$projet->bailleur->sigle]['annee'], $i);
                    array_push($teps[$projet->bailleur->sigle]['annee'], $i);
                    $montant = 0;
                    $nombre = 0;

                    foreach($sites as $site)
                    {
                        foreach($site->sinistres->where('dateDePaiement', '>=', $i.'-01-01')->where('dateDePaiement', '<=', $i.'-12-31') as $sinistre)
                        {
                            $montant += $sinistre->payer;
                            $nombre++;
                        }
                    }
                    array_push($paps[$projet->bailleur->sigle]['montant'], $montant);
                    array_push($paps[$projet->bailleur->sigle]['nombre'], $nombre);

                    $total = 0;
                    $effectue = 0;

                    foreach($projet->allComposantes as $composante)
                    {
                        foreach($composante->activites as $activite)
                        {
                            if($activite->durees->last()->debut >= $i.'-01-01' && $activite->durees->last()->fin <= $i.'-12-31')
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

                    array_push($teps[$projet->bailleur->sigle]['teps'], $total ? $effectue * 100 / $total : 0);
                }

            }
            foreach($executionFinanciers as $data)
            {
                $montantTotal += $data['montantTotal'];
                $montantDecaisse += $data['montantDecaisse'];
                $montantDepense += $data['montantDepense'];
                $nbreProjets++;
            }

            foreach($programme->activites() as $activite)
            {
                if($activite->statut == 2)
                {
                    $nbreActiviteRealise++;
                }

                $nbreActivite++;

            }

            foreach(Unitee::where('type', 1)->get() as $unitee)
            {
                foreach($unitee->indicateurs->where('programmeId', Auth::user()->programmeId) as $indicateur)
                {
                    $indicateurs = array_merge($indicateurs, [
                        $indicateur->secure_id => [
                            'indicateur' => $indicateur->nom,
                            'annee' => [],
                            'suivis' => []
                        ]
                    ]);

                    for($i = $min; $i <= $max; $i++)
                    {
                        array_push($indicateurs[$indicateur->secure_id]['annee'], $i);
                        $cumul = [];
                        $total = 0;

                        $data = $indicateur->suivis->where('annee', $i)->first();

                        if(!$data)
                        {
                            array_push($indicateurs[$indicateur->secure_id]['suivis'], $cumul);
                            continue;
                        }


                        foreach($data->suivisIndicateur[0]->valeurRealise as $key => $valeur)
                        {

                            $total = $valeur;

                            foreach($data->suivisIndicateur as $keytwo => $suivi)
                            {

                                if($keytwo)
                                {
                                    $total += $suivi->valeurRealise[$key];
                                }

                            }

                            array_push($cumul, $total);
                        }


                        array_push($indicateurs[$indicateur->secure_id]['suivis'], $cumul);
                    }
                }
            }

            $stat = [
                'nbreProjets' => $nbreProjets,
                'nbreBailleur' => $programme->bailleurs->count(),
                'nbreActivite' => $nbreActivite,
                "nbreActiviteRealise" => $nbreActiviteRealise,
                "montantTotal" => $montantTotal,
                "montantDecaisse" => $montantDecaisse,
                "montantDepense" => $montantDepense,
                "executionFinanciers" =>$executionFinanciers,
                "paps" => $paps,
                'teps' => $teps,
                "indicateurs" => $indicateurs
            ];

            return response()->json(['statut' => 'success', 'message' => null, 'data' => $stat, 'statutCode' => Response::HTTP_OK], Response::HTTP_OK);

        }
        catch (\Throwable $th)
        {
            return response()->json(['statut' => 'error', 'message' => $th->getMessage(), 'errors' => []], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    public function rapport(array $attributs) : JsonResponse
    {
        DB::beginTransaction();

        try
        {
            $user = Auth::user();
            $attributs = array_merge($attributs, ['userId' => $user->id, 'programmeId' => $user->programme->id]);

            $rapport = TemplateRapport::create($attributs);

            if(isset($attributs['document'])){
                $this->storeFile($attributs['document'], 'rapports/preuves', $rapport, null, 'preuves');
            }

            $acteur = Auth::check() ? Auth::user()->nom . " ". Auth::user()->prenom : "Inconnu";

            $message = $message ?? Str::ucfirst($acteur) . " a créé un " . strtolower(class_basename($rapport));

            //LogActivity::addToLog("Enregistrement", $message, get_class($rapport), $rapport->id);

            DB::commit();
            return response()->json(['statut' => 'success', 'message' => null, 'data' => $rapport, 'statutCode' => Response::HTTP_OK], Response::HTTP_OK);
        }
        catch (\Throwable $th)
        {
            DB::rollback();
            return response()->json(['statut' => 'error', 'message' => $th->getMessage(), 'errors' => []], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    public function updateRapport(array $attributs, $id) : JsonResponse
    {
        DB::beginTransaction();

        try
        {
            $user = Auth::user();
            $attributs = array_merge($attributs, ['userId' => $user->id, 'programmeId' => $user->programme->id]);

            $rapport = TemplateRapport::findByKey($id);

            if(!$rapport) throw new Exception( "Ce rapport n'existe pas", 404);

            $rapport = $rapport->fill($attributs);
            $rapport->save();

            if(isset($attributs['document'])){
                if($rapport->preuve){
                    $rapport->preuve->delete();
                }
                $this->storeFile($attributs['document'], 'rapports/preuves', $rapport, null, 'preuves');
            }

            $acteur = Auth::check() ? Auth::user()->nom . " ". Auth::user()->prenom : "Inconnu";

            $message = $message ?? Str::ucfirst($acteur) . " a modifié un " . strtolower(class_basename($rapport));

            //LogActivity::addToLog("Enregistrement", $message, get_class($rapport), $rapport->id);

            DB::commit();
            return response()->json(['statut' => 'success', 'message' => null, 'data' => $rapport, 'statutCode' => Response::HTTP_OK], Response::HTTP_OK);
        }
        catch (\Throwable $th)
        {
            DB::rollback();
            return response()->json(['statut' => 'error', 'message' => $th->getMessage(), 'errors' => []], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    public function deleteRapport($id) : JsonResponse
    {
        DB::beginTransaction();

        try
        {
            $rapport = TemplateRapport::findByKey($id);

            if(!$rapport) throw new Exception( "Ce rapport n'existe pas", 404);

            $preuve = $rapport->preuve;
            $rapport->delete();
            if($preuve){
                $preuve->delete();
            }

            $acteur = Auth::check() ? Auth::user()->nom . " ". Auth::user()->prenom : "Inconnu";

            $message = $message ?? Str::ucfirst($acteur) . " a supprimé un " . strtolower(class_basename($rapport));

            //LogActivity::addToLog("Enregistrement", $message, get_class($rapport), $rapport->id);

            DB::commit();
            return response()->json(['statut' => 'success', 'message' => null, 'data' => null, 'statutCode' => Response::HTTP_OK], Response::HTTP_OK);
        }
        catch (\Throwable $th)
        {
            DB::rollback();
            return response()->json(['statut' => 'error', 'message' => $th->getMessage(), 'errors' => []], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    public function rapportSendMail(array $attributs) : JsonResponse
    {
        DB::beginTransaction();

        try
        {
            $users = $attributs['destinataires'];

            $user = Auth::user();
            $attributs = array_merge($attributs, [
                'userId' => $user->id,
                'destinataires' => implode(', ', $attributs['destinataires']),
                'programmeId' => $user->programme->id
            ]);

            $email = EmailRapport::create($attributs);

            dispatch(new MailRapportJob($users, $attributs['rapport'], $attributs['objet']))->delay(now()->addSeconds(15));

            $acteur = Auth::check() ? Auth::user()->nom . " ". Auth::user()->prenom : "Inconnu";

            $message = $message ?? Str::ucfirst($acteur) . " a envoyé un rapport par mail";

            //LogActivity::addToLog("Enregistrement", $message, get_class(Auth::user()), Auth::user()->id);

            DB::commit();
            return response()->json(['statut' => 'success', 'message' => null, 'data' => Auth::user()->emailRapports, 'statutCode' => Response::HTTP_OK], Response::HTTP_OK);
        }
        catch (\Throwable $th)
        {
            DB::rollback();
            return response()->json(['statut' => 'error', 'message' => $th->getMessage(), 'errors' => []], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    public function rapports() : JsonResponse
    {

        try
        {

            return response()->json(['statut' => 'success', 'message' => null, 'data' => Auth::user()->rapports->load("preuve"), 'statutCode' => Response::HTTP_OK], Response::HTTP_OK);
        }
        catch (\Throwable $th)
        {
            return response()->json(['statut' => 'error', 'message' => $th->getMessage(), 'errors' => []], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    public function emailRapports() : JsonResponse
    {
        try
        {

            return response()->json(['statut' => 'success', 'message' => null, 'data' => Auth::user()->emailRapports->load("preuve"), 'statutCode' => Response::HTTP_OK], Response::HTTP_OK);
        }
        catch (\Throwable $th)
        {
            return response()->json(['statut' => 'error', 'message' => $th->getMessage(), 'errors' => []], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }


}
