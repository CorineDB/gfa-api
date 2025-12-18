<?php

namespace App\Services;

use App\Events\NewNotification;
use App\Http\Resources\activites\ActivitesResource;
use App\Http\Resources\SuiviFinancierResource;
use App\Models\Activite;
use App\Models\Bailleur;
use App\Models\Gouvernement;
use App\Models\Organisation;
use App\Models\Projet;
use App\Models\SuiviFinancier;
use App\Models\UniteeDeGestion;
use App\Models\User;
use App\Notifications\SuiviFinancierNotification;
use App\Repositories\ActiviteRepository;
use App\Repositories\SuiviFinancierRepository;
use App\Traits\Helpers\HelperTrait;
use App\Traits\Helpers\LogActivity;
use App\Traits\Helpers\Pta;
use Box\Spout\Reader\Common\Creator\ReaderEntityFactory;
use Carbon\Carbon;
use Core\Services\Contracts\BaseService;
use Core\Services\Interfaces\SuiviFinancierServiceInterface;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;
use Exception;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

/**
 * Interface SuiviFinancierServiceInterface
 * @package Core\Services\Interfaces
 */
class SuiviFinancierService extends BaseService implements SuiviFinancierServiceInterface
{

    use Pta, HelperTrait;
    /**
     * @var service
     */
    protected $repository, $activiteRepository;

    /**
     * ProjetService constructor.
     *
     * @param SuiviFinancier $suiviFinancierRepository
     */
    public function __construct(SuiviFinancierRepository $suiviFinancierRepository, ActiviteRepository $activiteRepository)
    {
        parent::__construct($suiviFinancierRepository);
        $this->repository = $suiviFinancierRepository;
        $this->activiteRepository = $activiteRepository;
    }

    public function all(array $attributs = ['*'], array $relations = []): JsonResponse
    {
        try {
            $suiviFinanciers = [];

            $projet = null;

            if (Auth::user()->hasRole('organisation') || (get_class(auth()->user()->profilable) == Organisation::class)) {
                $projet = Auth::user()->profilable->projet;
            } else if (Auth::user()->hasRole("unitee-de-gestion") || (get_class(auth()->user()->profilable) == UniteeDeGestion::class)) {
                $projet = Auth::user()->programme->projets;
            }

            $suiviFinanciers = $this->filterData($projet);

            return response()->json(['statut' => 'success', 'message' => null, 'data' => $suiviFinanciers, 'statutCode' => Response::HTTP_OK], Response::HTTP_OK);
        } catch (\Throwable $th) {
            return response()->json(['statut' => 'error', 'message' => $th->getMessage(), 'errors' => []], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    public function filtre(array $attributs): JsonResponse
    {
        try {

            $projet = null;

            if (Auth::user()->hasRole('organisation') || (get_class(auth()->user()->profilable) == Organisation::class)) {
                $projet = Auth::user()->profilable->projet;
            } else if (Auth::user()->hasRole("unitee-de-gestion") || (get_class(auth()->user()->profilable) == UniteeDeGestion::class)) {
                
                if (isset($attributs['projetId'])) {
                    $projet = Auth::user()->programme->projets()->where('id', $attributs['projetId'])->first();
                } else {
                    $projet = Auth::user()->programme->projets;
                }
            }

            $suiviFinanciers = $this->filterData($projet, $attributs);

            return response()->json(['statut' => 'success', 'message' => null, 'data' => $suiviFinanciers, 'statutCode' => Response::HTTP_OK], Response::HTTP_OK);

            $suiviFinanciers = [];

            $bailleur = Bailleur::find($attributs['bailleurId']);

            $projet = Projet::where('bailleurId', $attributs['bailleurId'])->first();

            $activites = $projet->activites();

            foreach ($activites as $activite) {
                $suivi = $projet->bailleur->suiviFinanciers->where('activiteId', $activite->id)
                    ->where('trimestre', $attributs['trimestre'])
                    ->where('annee', $attributs['annee'])
                    ->first();

                if (!$suivi) continue;

                $plan = $activite->planDeDecaissement($attributs['trimestre'], $attributs['annee']);

                $periode = [
                    "budget" => $plan['pret'],
                    "consommer" => $suivi->consommer,
                    "disponible" => $plan['pret'] - $suivi->consommer,
                    "pourcentage" => $plan['pret'] != 0 ? round(($suivi->consommer * 100) / $plan['pret'], 2) : 0 . " %"
                ];

                $planParAnnee = $activite->planDeDecaissementParAnnee($attributs['annee']);
                $consommerParAnnee = $projet->bailleur->suiviFinanciers->where('activiteId', $activite->id)
                    ->where('annee', $attributs['annee'])
                    ->sum('consommer');

                $exercice = [
                    "budget" => $planParAnnee['pret'],
                    "consommer" => $consommerParAnnee,
                    "disponible" => $planParAnnee['pret'] - $consommerParAnnee,
                    "pourcentage" => $planParAnnee['pret'] != 0 ? round(($consommerParAnnee * 100) / $planParAnnee['pret'], 2) : 0 . " %"
                ];

                $planCumul = $activite->planDeDecaissements->sum('pret');
                $consommerCumul = $projet->bailleur->suiviFinanciers->where('activiteId', $activite->id)
                    ->sum('consommer');

                $cumul = [
                    "budget" => $planCumul,
                    "consommer" => $consommerCumul,
                    "disponible" => $planCumul - $consommerCumul,
                    "pourcentage" => $planCumul != 0 ? round(($consommerCumul * 100) / $planCumul, 2) : 0 . " %"
                ];

                $objet = [
                    "bailleur" => $projet->bailleur->sigle,
                    "trimestre" => $attributs['trimestre'],
                    "annee" => $attributs['annee'],
                    "activite" => new ActivitesResource($activite),
                    "periode" => $periode,
                    "exercice" => $exercice,
                    "cumul" => $cumul
                ];

                array_push($suiviFinanciers, $objet);
            }

            $programme = Auth::user()->programme;
            $projets = [];

            foreach ($programme->suiviFinanciers as $suiviFinancier) {
                $controle = 1;
                $projet = $suiviFinancier->activite->composante->projet;

                foreach ($projets as $key => $p) {

                    if ($p['id'] == $projet->id) {
                        $projets[$key]['total'] += $suiviFinancier->consommer;
                        $controle = 0;
                    }
                }

                if ($controle) {
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
                'annee' => $attributs['annee'],
                'bailleur' => $bailleur->sigle
            ];

            return response()->json(['statut' => 'success', 'message' => null, 'data' => $data, 'statutCode' => Response::HTTP_OK], Response::HTTP_OK);
        } catch (\Throwable $th) {

            DB::rollBack();

            //throw $th;
            return response()->json(['statut' => 'error', 'message' => $th->getMessage(), 'errors' => [], 'statutCode' => Response::HTTP_INTERNAL_SERVER_ERROR], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /* public function filtre(array $attributs): JsonResponse
    {

        try {

            $suiviFinanciers = [];

            $bailleur = Bailleur::find($attributs['bailleurId']);

            $projet = Projet::where('bailleurId', $attributs['bailleurId'])->first();

            $activites = $projet->activites();

            foreach($activites as $activite)
            {
                $suivi = $projet->bailleur->suiviFinanciers->where('activiteId', $activite->id)
                                                           ->where('trimestre', $attributs['trimestre'])
                                                           ->where('annee', $attributs['annee'])
                                                           ->first();

                if(!$suivi) continue;

                $plan = $activite->planDeDecaissement($attributs['trimestre'], $attributs['annee']);

                $periode = [
                    "budget" => $plan['pret'],
                    "consommer" => $suivi->consommer,
                    "disponible" => $plan['pret'] - $suivi->consommer,
                    "pourcentage" => $plan['pret'] != 0 ? round(($suivi->consommer*100)/$plan['pret'],2) : 0 . " %"
                ];

                $planParAnnee = $activite->planDeDecaissementParAnnee($attributs['annee']);
                $consommerParAnnee = $projet->bailleur->suiviFinanciers->where('activiteId', $activite->id)
                                                                        ->where('annee', $attributs['annee'])
                                                                        ->sum('consommer');

                $exercice = [
                    "budget" => $planParAnnee['pret'],
                    "consommer" => $consommerParAnnee,
                    "disponible" => $planParAnnee['pret'] - $consommerParAnnee,
                    "pourcentage" => $planParAnnee['pret'] != 0 ? round(($consommerParAnnee*100)/$planParAnnee['pret'],2) : 0 . " %"
                ];

                $planCumul = $activite->planDeDecaissements->sum('pret');
                $consommerCumul = $projet->bailleur->suiviFinanciers->where('activiteId', $activite->id)
                                                                    ->sum('consommer');

                $cumul = [
                    "budget" => $planCumul,
                    "consommer" => $consommerCumul,
                    "disponible" => $planCumul - $consommerCumul,
                    "pourcentage" => $planCumul != 0 ? round(($consommerCumul*100)/$planCumul,2) : 0 . " %"
                ];

                $objet = [
                    "bailleur" => $projet->bailleur->sigle,
                    "trimestre" => $attributs['trimestre'],
                    "annee" => $attributs['annee'],
                    "activite" => new ActivitesResource($activite),
                    "periode" => $periode,
                    "exercice" => $exercice,
                    "cumul" => $cumul
                ];

                array_push($suiviFinanciers, $objet);
            }

            $programme = Auth::user()->programme;
            $projets = [];

            foreach($programme->suiviFinanciers as $suiviFinancier)
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
                'annee' => $attributs['annee'],
                'bailleur' => $bailleur->sigle
            ];


            return response()->json(['statut' => 'success', 'message' => null, 'data' => $data, 'statutCode' => Response::HTTP_OK], Response::HTTP_OK);

        } catch (\Throwable $th) {

            DB::rollBack();

            //throw $th;
            return response()->json(['statut' => 'error', 'message' => $th->getMessage(), 'errors' => [], 'statutCode' => Response::HTTP_INTERNAL_SERVER_ERROR], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    } */


    public function create(array $attributs): JsonResponse
    {
        DB::beginTransaction();

        try {
            $controle = 1;
            $attributs = array_merge($attributs, ['programmeId' => Auth::user()->programmeId]);

            $activite = Activite::find($attributs['activiteId']);

            if ($activite->statut < 0) {
                throw new Exception("L'activite n'a pas encore démarré", 500);
            }

            $durees = $activite->durees;
            foreach ($durees as $duree) {
                $debutTab = explode('-', $duree->debut);
                $finTab = explode('-', $duree->fin);

                if ($debutTab[0] <= $attributs['annee'] && $finTab[0] >= $attributs['annee']) {
                    $controle = 0;
                    break;
                }
            }

            if ($controle) {
                throw new Exception("L'activite n'a aucune durée d'execution dans l'année precisé", 500);
            }

            /*if($activite->statut != 0 && $activite->statut != 1 )
                throw new Exception("L'activite n'est ni en cours ni en retard, le suivi ne peux etre faire", 500);*/

            if ((Carbon::parse($activite->dureeActivite->debut)->year <= $attributs['annee'] - 1)) {
                $passTrimestresCount = $this->repository->allFiltredBy([
                    ['attribut' => 'activiteId', 'operateur' => '=', 'valeur' => $attributs['activiteId']],
                    ['attribut' => 'annee', 'operateur' => '=', 'valeur' => $attributs['annee'] - 1]
                ])
                    ->pluck('trimestre')->count();

                if ($passTrimestresCount == 0) {
                    throw new Exception("Vous devez d'abord faire le suivi de l'annee " . ($attributs['annee'] - 1), 500);
                }
            }

            if (!array_key_exists('dateDeSuivi', $attributs)) {
                $trimestres = $this->repository->allFiltredBy([
                    ['attribut' => 'activiteId', 'operateur' => '=', 'valeur' => $attributs['activiteId']],
                    ['attribut' => 'annee', 'operateur' => '=', 'valeur' => $attributs['annee']]
                ])
                    ->pluck('trimestre');


                // Ensure $trimestres is an array
                $trimestresArray = is_array($trimestres) ? $trimestres : $trimestres->toArray();

                if (in_array($attributs['trimestre'], $trimestresArray)) {
                    throw new Exception(" suivi du trimestre {$attributs['trimestre']} a été déja effectufié", 500);
                }
            }

            /*if($attributs['type'])
                $trimestres = $this->repository->allFiltredBy([['attribut' => 'activiteId', 'operateur' => '=', 'valeur' => $attributs['activiteId']],
                                                           ['attribut' => 'annee', 'operateur' => '=', 'valeur' => $attributs['annee']],
                                                           ['attribut' => 'suivi_financierable_type', 'operateur' => '=', 'valeur' => get_class(new Gouvernement())]])
                                           ->pluck('trimestre');

            else
                $trimestres = $this->repository->allFiltredBy([['attribut' => 'activiteId', 'operateur' => '=', 'valeur' => $attributs['activiteId']],
                                                           ['attribut' => 'annee', 'operateur' => '=', 'valeur' => $attributs['annee']],
                                                           ['attribut' => 'suivi_financierable_type', 'operateur' => '=', 'valeur' => get_class(new Bailleur())]])
                                           ->pluck('trimestre');*/

            // if(!(count($trimestres)) && $attributs['trimestre'] != 1)
            //     throw new Exception("Vous devez d'abord faire le suivi du trimestre 1", 500);



            /*$nombreDeTrimestre = count($trimestres);

            if($nombreDeTrimestre == 0 && $attributs['trimestre'] > 1) throw new Exception("Vous devez d'abord faire le suivi du premier trimestre", 500);

            else if( $nombreDeTrimestre == 0 && $attributs['trimestre'] == 1 );

            else
            {
                $max = max($trimestres->all());
                if($attributs['trimestre'] <= $max)
                    throw new Exception("Le suivi du trimestre {$attributs['trimestre']} a déjà été effectué", 500);

                else if($attributs['trimestre'] > $max+1)
                {
                    $max = $max+1;
                    throw new Exception("Vous devez d'abord faire le suivi du trimestre {$max}", 500);
                }

                else if( $nombreDeTrimestre + 1 > 4)
                    throw new Exception("Le suivi ne peut qu'être faire sur les 4 trimestres d'une année.", 1);
            }*/

            //$plan = $activite->planDeDecaissement($attributs['trimestre'], $attributs['annee']);

            if (!array_key_exists('dateDeSuivi', $attributs)) {

                switch ($attributs['trimestre']) {
                    case 1:
                        $attributs = array_merge($attributs, ['dateDeSuivi' => $attributs['annee'] . "-03-31 " . date('h:i:s')]);
                        break;

                    case 2:
                        $attributs = array_merge($attributs, ['dateDeSuivi' => $attributs['annee'] . "-06-30 " . date('h:i:s')]);
                        break;

                    case 3:
                        $attributs = array_merge($attributs, ['dateDeSuivi' => $attributs['annee'] . "-09-30 " . date('h:i:s')]);
                        break;

                    case 4:
                        $attributs = array_merge($attributs, ['dateDeSuivi' => $attributs['annee'] . "-12-31 " . date('h:i:s')]);
                        break;

                    default:
                        # code...
                        break;
                }
            }


            /*if($attributs['type'])
            {
                $gouvernement = $activite->composante->projet->programme->gouvernement;

                if(!($gouvernement)) throw new Exception("Gouvernement n'existe pas, veillez creer son compte", 1);
                $suiviFinancier = $gouvernement->suiviFinanciers()->create($attributs);
            }

            else
            {
                $bailleur = $activite->composante->projet->bailleur;
                $suiviFinancier = $bailleur->suiviFinanciers()->create($attributs);
            }*/

            $suiviFinancier = $this->repository->create($attributs);

            $data['texte'] = "Un suivi financier vient d'etre faire";
            $data['id'] = $suiviFinancier->id;
            $data['auteurId'] = Auth::user()->id;
            $notification = new SuiviFinancierNotification($data);

            $allUsers = User::where('programmeId', Auth::user()->programmeId);
            foreach ($allUsers as $user) {
                if ($user->hasPermissionTo('alerte-suivi-financier')) {
                    $user->notify($notification);

                    $notification = $user->notifications->last();

                    event(new NewNotification($this->formatageNotification($notification, $user)));
                }
            }

            DB::commit();
            return response()->json(['statut' => 'success', 'message' => null, 'data' => new SuiviFinancierResource($suiviFinancier), 'statutCode' => Response::HTTP_OK], Response::HTTP_OK);
        } catch (\Throwable $th) {
            DB::rollback();
            return response()->json(['statut' => 'error', 'message' => $th->getMessage(), 'errors' => []], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    public function trismestreASsuivre(array $attributs): JsonResponse
    {
        $trimestreAll = [1, 2, 3, 4];
        $trimestres = $this->repository->allFiltredBy([
            ['attribut' => 'activiteId', 'operateur' => '=', 'valeur' => $attributs['activiteId']],
            ['attribut' => 'annee', 'operateur' => '=', 'valeur' => $attributs['annee']],
            ['attribut' => 'suivi_financierable_type', 'operateur' => '=', 'valeur' => get_class(new Bailleur())]
        ])->pluck('trimestre');
        $diff1 = array_diff($trimestreAll, $trimestres);

        return response()->json(['statut' => 'success', 'message' => null, 'data' =>  $diff1, 'statutCode' => Response::HTTP_OK], Response::HTTP_OK);
    }

    public function update($suiviFinancierId, array $attributs): JsonResponse
    {
        DB::beginTransaction();

        try {
            if (array_key_exists('programmeId', $attributs))
                unset($attributs['programmeId']);

            if (!array_key_exists('activiteId', $attributs)) {
                throw new Exception("L\identifiant de l'activite n'a pas ete renseigne.", 500);
            }

            $activite = Activite::find($attributs['activiteId']);

            if ($activite->composante->projet->programmeId != Auth::user()->programmeId) throw new Exception("Cette activite n'est pas dans le programme en cours", 500);

            $durees = $activite->durees;
            foreach ($durees as $duree) {
                $debutTab = explode('-', $duree->debut);
                $finTab = explode('-', $duree->fin);

                if ($debutTab[0] <= $attributs['annee'] && $finTab[0] >= $attributs['annee']) {
                    $controle = 0;
                    break;
                }
            }

            if ($controle) {
                throw new Exception("L'activite n'a aucune durée d'execution dans l'année precisé", 500);
            }

            if (!is_object($suiviFinancierId)) {
                if (!($suivi = $this->repository->findById($suiviFinancierId))) throw new Exception("Cet suivi n'existe pas", 500);
            } else {
                $suivi = $suiviFinancierId;
            }

            if ($suivi->activiteId !== $activite->id) throw new Exception("Ce suivi n'est pas celui de cette activite", 500);

            if (array_key_exists('trimestre', $attributs)) unset($attributs['trimestre']);
            if (array_key_exists('annee', $attributs)) unset($attributs['annee']);
            if (array_key_exists('dateDeSuivi', $attributs)) unset($attributs['dateDeSuivi']);

            $suivi->fill($attributs)->save();

            $suivi->refresh();

            $acteur = Auth::check() ? Auth::user()->nom . " " . Auth::user()->prenom : "Inconnu";

            $message = $message ?? Str::ucfirst($acteur) . " a modifié un " . strtolower(class_basename($activite));

            //LogActivity::addToLogog("Modification", $message, get_class($activite), $activite->id);

            DB::commit();

            return response()->json(['statut' => 'success', 'message' => null, 'data' => $suivi, 'statutCode' => Response::HTTP_OK], Response::HTTP_OK);
        } catch (\Throwable $th) {
            DB::rollback();
            return response()->json(['statut' => 'error', 'message' => $th->getMessage(), 'errors' => []], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    public function importation_old(array $attributs): JsonResponse
    {
        DB::beginTransaction();

        try {
            $programmeId = Auth::user()->programmeId;

            $file = $attributs['fichier'];

            if ($file->getClientOriginalExtension() != 'xls')
                throw new Exception("Le fichier doit être au format xls", 500);

            $filenameWithExt = $file->getClientOriginalName();
            $filename = strtolower(str_replace(' ', '-', time() . '-' . $filenameWithExt));
            $path = "importation/" . $filename;
            Storage::disk('public')->put($path, $file->getContent());


            if (($open = fopen(public_path("storage/" . $path), "r")) !== FALSE) {
                $i = 1;

                $header = fgetcsv($open, 1000, " ");
                if (array_diff($header, ["codePta", "trimestre", "annee", "consommer", "source"]))
                    throw new Exception("L'en-tete du fichier n'est pas valide", 500);
                $i++;

                while (($data = fgetcsv($open, 1000, " ")) !== FALSE) {
                    $attributs = [];
                    $controle = 1;

                    if (count($data) != 5)
                        throw new Exception("Fichier invalide: ligne {$i} incorrecte", 500);

                    $activite = $this->searchByCodePta(new Activite, $data[0]);

                    if ($activite == null)
                        throw new Exception("Activite avec le code pta : {$data[0]} introuvable, ligne {$i}", 500);

                    $durees = $activite->durees;
                    foreach ($durees as $duree) {
                        $debutTab = explode('-', $duree->debut);
                        $finTab = explode('-', $duree->fin);

                        if ($debutTab[0] <= $data[2] && $finTab[0] >= $data[2]) {
                            $controle = 0;
                            break;
                        }
                    }

                    if ($controle)
                        throw new Exception("L'activite n'a aucune durée d'execution dans l'année precisé, ligne {$i}", 500);

                    if ($activite->statut != 0 && $activite->statut != 1)
                        throw new Exception("L'activite n'est ni en cours ni en retard, le suivi ne peux etre faire, ligne {$i}", 500);

                    if ($data[4] == 'bn')
                        $trimestres = $this->repository->allFiltredBy([
                            ['attribut' => 'activiteId', 'operateur' => '=', 'valeur' => $activite->id],
                            ['attribut' => 'annee', 'operateur' => '=', 'valeur' => $data[2]],
                            ['attribut' => 'suivi_financierable_type', 'operateur' => '=', 'valeur' => get_class(new Gouvernement())]
                        ])
                            ->pluck('trimestre');

                    else
                        $trimestres = $this->repository->allFiltredBy([
                            ['attribut' => 'activiteId', 'operateur' => '=', 'valeur' => $activite->id],
                            ['attribut' => 'annee', 'operateur' => '=', 'valeur' => $data[2]],
                            ['attribut' => 'suivi_financierable_type', 'operateur' => '=', 'valeur' => get_class(new Bailleur())]
                        ])
                            ->pluck('trimestre');

                    $trimestres = $this->repository->allFiltredBy([
                        ['attribut' => 'activiteId', 'operateur' => '=', 'valeur' => $activite->id],
                        ['attribut' => 'annee', 'operateur' => '=', 'valeur' => $data[2]]
                    ])
                        ->pluck('trimestre');

                    if (!(count($trimestres)) && $data[1] != 1)
                        throw new Exception("Vous devez d'abord faire le suivi du trimestre 1, ligne {$i}", 500);


                    $nombreDeTrimestre = count($trimestres);

                    if ($nombreDeTrimestre == 0 && $data[1] > 1) throw new Exception("Vous devez d'abord faire le suivi du premier trimestre, ligne {$i}", 500);

                    else if ($nombreDeTrimestre == 0 && $data[1] == 1);

                    else {
                        $max = max($trimestres->all());
                        if ($data[1] <= $max)
                            throw new Exception("Le suivi du trimestre {$data[1]} a déjà été effectué, ligne {$i}", 500);

                        else if ($data[1] > $max + 1) {
                            $max = $max + 1;
                            throw new Exception("Vous devez d'abord faire le suivi du trimestre {$max}, ligne {$i}", 500);
                        } else if ($nombreDeTrimestre + 1 > 4)
                            throw new Exception("Le suivi ne peut qu'être faire sur les 4 trimestres d'une année, ligne {$i}.", 1);
                    }

                    $attributs = array_merge($attributs, [
                        'activiteId' => $activite->id,
                        'trimestre' => $data[1],
                        'annee' => $data[2],
                        'consommer' => $data[3],
                        'programmeId' => $programmeId
                    ]);

                    if ($data[4] == 'bn') {
                        $gouvernement = $activite->composante->projet->programme->gouvernement;

                        if (!($gouvernement)) throw new Exception("Gouvernement n'existe pas, veillez creer son compte, ligne {$i}", 1);

                        $gouvernement->suiviFinanciers()->create($attributs);
                    } else {
                        $bailleur = $activite->composante->projet->bailleur;
                        $bailleur->suiviFinanciers()->create($attributs);
                    }

                    $i++;
                }
            }

            DB::commit();

            fclose($open);

            return response()->json(['statut' => 'success', 'message' => null, 'data' => "Importation reussir", 'statutCode' => Response::HTTP_OK], Response::HTTP_OK);
        } catch (\Throwable $th) {
            DB::rollback();

            return response()->json(['statut' => 'error', 'message' => $th->getMessage(), 'errors' => []], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    public function importation(array $attributs): JsonResponse
    {
        DB::beginTransaction();

        try {
            $programmeId = Auth::user()->programmeId;

            $file = $attributs['fichier'];

            if ($file->getClientOriginalExtension() != 'xlsx')
                throw new Exception("Le fichier doit être au format xlsx", 500);

            $filenameWithExt = $file->getClientOriginalName();
            $filename = strtolower(str_replace(' ', '-', time() . '-' . $filenameWithExt));
            $path = "importation/" . $filename;
            Storage::disk('public')->put($path, $file->getContent());

            $trimestresFichier = [];

            $reader = ReaderEntityFactory::createReaderFromFile(public_path("storage/" . $path));

            $reader->open(public_path("storage/" . $path));

            foreach ($reader->getSheetIterator() as $sheet) {
                foreach ($sheet->getRowIterator() as $key => $row) {
                    /* validation de l'en-tete du fichier et recuperation des trimestres */
                    if ($key == 1) {
                        $cells = $row->getCells();
                        foreach ($cells as $cellule => $cell) {
                            switch ($cellule + 1) {
                                case 1:
                                    if (strtolower($cell->getValue()) != "cod")
                                        throw new Exception("Cellule 1 de la ligne 1 invalide ", 500);
                                    break;
                                case 2:
                                    if (strtolower($cell->getValue()) != "codepta")
                                        throw new Exception("Cellule 2 de la ligne 1 invalide ", 500);
                                    break;
                                case 3:
                                    if (strtolower($cell->getValue()) != "annee")
                                        throw new Exception("Cellule 3 de la ligne 1 invalide ", 500);
                                    break;
                                case 4:
                                    if (strtolower($cell->getValue()) != "source")
                                        throw new Exception("Cellule 4 de la ligne 1 invalide ", 500);
                                    break;
                                case 5:
                                    if (strtolower($cell->getValue()) != "codact")
                                        throw new Exception("Cellule 5 de la ligne 1 invalide ", 500);
                                    break;
                                case 6:
                                    if (strtolower($cell->getValue()) != "activites")
                                        throw new Exception("Cellule 6 de la ligne 1 invalide ", 500);
                                    break;
                                default:
                                    if (strtolower($cell->getValue()) == "consommé")
                                        array_push($trimestresFichier, $cellule + 1);
                                    break;
                            }
                        }
                    } else {
                        $attributs = [];
                        $cells = $row->getCells();

                        foreach ($trimestresFichier as $keys => $trimestre) {
                            $attributs = array_merge($attributs, ['trimestre' => $keys + 1]);
                            $attributs = array_merge($attributs, ['programmeId' => $programmeId]);

                            foreach ($cells as $cellule => $cell) {
                                switch ($cellule + 1) {
                                    case 2:
                                        if ($cell->getValue() == "")
                                            throw new Exception("Cellule 2 de la ligne {$key} invalide ", 500);

                                        $attributs = array_merge($attributs, ['codePta' => $cell->getValue()]);
                                        break;
                                    case 3:
                                        if ($cell->getValue() == "")
                                            throw new Exception("Cellule 3 de la ligne {$key} invalide ", 500);

                                        $attributs = array_merge($attributs, ['annee' => $cell->getValue()]);
                                        break;
                                    case 4:
                                        if ($cell->getValue() == "")
                                            throw new Exception("Cellule 4 de la ligne {$key} invalide ", 500);

                                        $attributs = array_merge($attributs, ['source' => $cell->getValue()]);
                                        break;
                                    case $trimestre:
                                        if ($cell->getValue() == "")
                                            throw new Exception("Cellule {$trimestre} de la ligne {$key} invalide ", 500);

                                        $attributs = array_merge($attributs, ['consommer' => $cell->getValue()]);
                                        break;

                                    default:
                                        //throw new Exception("Taille de la ligne 3 invalide, cela doit contenir 4 cellule ", 500);
                                        break;
                                }
                            }

                            $controle = 1;

                            $activite = $this->searchByCodePta(new Activite, $attributs['codePta']);

                            if ($activite == null)
                                throw new Exception("Activite avec le code pta : {$attributs['codePta']} introuvable, ligne {$key}", 500);

                            $attributs = array_merge($attributs, ['activiteId' => $activite->id]);

                            $durees = $activite->durees;
                            foreach ($durees as $duree) {
                                $debutTab = explode('-', $duree->debut);
                                $finTab = explode('-', $duree->fin);

                                if ($debutTab[0] <= $attributs['annee'] && $finTab[0] >= $attributs['annee']) {
                                    $controle = 0;
                                    break;
                                }
                            }

                            if ($controle)
                                throw new Exception("L'activite n'a aucune durée d'execution dans l'année precisé", 500);


                            /*if($activite->statut != 0 && $activite->statut != 1 )
                                throw new Exception("L'activite n'est ni en cours ni en retard, le suivi ne peux etre faire", 500);*/

                            if ($activite->statut < 0)
                                throw new Exception("L'activite n'a pas encore démarré", 500);

                            if ($attributs['source'] == 'bn')
                                $trimestres = $this->repository->allFiltredBy([
                                    ['attribut' => 'activiteId', 'operateur' => '=', 'valeur' => $attributs['activiteId']],
                                    ['attribut' => 'annee', 'operateur' => '=', 'valeur' => $attributs['annee']],
                                    ['attribut' => 'suivi_financierable_type', 'operateur' => '=', 'valeur' => get_class(new Gouvernement())]
                                ])
                                    ->pluck('trimestre');

                            else
                                $trimestres = $this->repository->allFiltredBy([
                                    ['attribut' => 'activiteId', 'operateur' => '=', 'valeur' => $attributs['activiteId']],
                                    ['attribut' => 'annee', 'operateur' => '=', 'valeur' => $attributs['annee']],
                                    ['attribut' => 'suivi_financierable_type', 'operateur' => '=', 'valeur' => get_class(new Bailleur())]
                                ])
                                    ->pluck('trimestre');

                            /*if(!(count($trimestres)) && $attributs['trimestre'] != 1)
                                throw new Exception("Vous devez d'abord faire le suivi du trimestre 1", 500);


                            $nombreDeTrimestre = count($trimestres);

                            if($nombreDeTrimestre == 0 && $attributs['trimestre'] > 1) throw new Exception("Vous devez d'abord faire le suivi du premier trimestre", 500);

                            else if( $nombreDeTrimestre == 0 && $attributs['trimestre'] == 1 );

                            else
                            {
                                $max = max($trimestres->all());
                                if($attributs['trimestre'] <= $max)
                                    continue;

                                else if($attributs['trimestre'] > $max+1)
                                {
                                    $max = $max+1;
                                    throw new Exception("Vous devez d'abord faire le suivi du trimestre {$max}.", 500);
                                }

                                else if( $nombreDeTrimestre + 1 > 4)
                                    throw new Exception("Le suivi ne peut qu'être faire sur les 4 trimestres d'une année.", 1);
                            }*/

                            //$plan = $activite->planDeDecaissement($attributs['trimestre'], $attributs['annee']);

                            if (!array_key_exists('dateSuivie', $attributs)) {

                                switch ($attributs['trimestre']) {
                                    case 1:
                                        $attributs = array_merge($attributs, ['dateSuivie' => $attributs['annee'] . "-03-31 " . date('h:i:s')]);
                                        break;

                                    case 2:
                                        $attributs = array_merge($attributs, ['dateSuivie' => $attributs['annee'] . "-06-30 " . date('h:i:s')]);
                                        break;

                                    case 3:
                                        $attributs = array_merge($attributs, ['dateSuivie' => $attributs['annee'] . "-09-30 " . date('h:i:s')]);
                                        break;

                                    case 4:
                                        $attributs = array_merge($attributs, ['dateSuivie' => $attributs['annee'] . "-12-31 " . date('h:i:s')]);
                                        break;

                                    default:
                                        # code...
                                        break;
                                }
                            }

                            if ($attributs['source'] == 'bn') {
                                $gouvernement = $activite->composante->projet->programme->gouvernement;

                                if (!($gouvernement)) throw new Exception("Gouvernement n'existe pas, veillz creer son compte", 1);
                                $suiviFinancier = $gouvernement->suiviFinanciers()->create($attributs);
                            } else {
                                $bailleur = $activite->composante->projet->bailleur;
                                $suiviFinancier = $bailleur->suiviFinanciers()->create($attributs);
                            }


                            $data['texte'] = "Un suivi financier vient d'etre faire";
                            $data['id'] = $suiviFinancier->id;
                            $data['auteurId'] = Auth::user()->id;
                            $notification = new SuiviFinancierNotification($data);

                            $allUsers = User::where('programmeId', Auth::user()->programmeId);
                            foreach ($allUsers as $user) {
                                if ($user->hasPermissionTo('alerte-suivi-financier')) {
                                    $user->notify($notification);

                                    $notification = $user->notifications->last();

                                    event(new NewNotification($this->formatageNotification($notification, $user)));
                                }
                            }
                        }
                    }
                }
            }

            DB::commit();

            $reader->close();

            return response()->json(['statut' => 'success', 'message' => null, 'data' => "Importation reussir", 'statutCode' => Response::HTTP_OK], Response::HTTP_OK);
        } catch (\Throwable $th) {
            DB::rollback();

            return response()->json(['statut' => 'error', 'message' => $th->getMessage(), 'errors' => []], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    protected function filterData($projet, array $filterData = null)
    {

        $suiviFinanciers = [];

        if ($projet) {

            if (is_null($filterData) || !isset($filterData['annee']) || is_null($filterData['annee']) || empty($filterData['annee'])) {
                $filterData['annee'] = Carbon::now()->year;
            }

            if (is_null($filterData) || !isset($filterData['trimestre']) || is_null($filterData['trimestre']) || empty($filterData['trimestre'])) {
                $filterData['trimestre'] = $this->getCurrentTrimestre();
            }

            if ($projet instanceof \Illuminate\Database\Eloquent\Model) {

                $activites = $projet->activites();

                if (isset($filterData['activiteId'])) {
                    $activites = array_filter($activites, function($activite) use($filterData) {
                        return $activite['id'] == $filterData['activiteId'];
                    });
                }

                $suiviFinanciers = $this->getSuiviFinancier($activites, $filterData);
            } else if (($projet instanceof \Illuminate\Database\Eloquent\Collection) || (is_array($projet))) {
                $suiviFinanciers = $projet->flatMap(function ($item) use ($filterData) {
                    $activites = $item->activites();

                    if (isset($filterData['activiteId'])) {
                        $activites = array_filter($activites, function($activite) use($filterData) {
                            return $activite['id'] == $filterData['activiteId'];
                        });
                    }

                    return $this->getSuiviFinancier($activites, $filterData);
                });
            }

            $programme = Auth::user()->programme;
            $projets = [];

            foreach ($programme->suiviFinanciers as $suiviFinancier) {
                $controle = 1;

                // Check if activite exists
                if (!$suiviFinancier->activite) {
                    Log::warning("Activite not found for suiviFinancier ID: {$suiviFinancier->id}");
                    continue; // Skip this iteration
                }

                // Check if composante exists
                if (!$suiviFinancier->activite->composante) {
                    Log::warning("Composante not found for activite ID: {$suiviFinancier->activite->id}");
                    continue; // Skip this iteration
                }

                // Check if projet exists
                if (!$suiviFinancier->activite->composante->projet) {
                    Log::warning("Projet not found for composante ID: {$suiviFinancier->activite->composante->id}");
                    continue; // Skip this iteration
                }

                // Now it's safe to access projet
                $projet = $suiviFinancier->activite->composante->projet;

                foreach ($projets as $key => $p) {

                    if ($p['id'] == $projet->id) {
                        $projets[$key]['total'] += $suiviFinancier->consommer;
                        $controle = 0;
                    }
                }

                if ($controle) {
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
                'annee' => isset($filterData['annee']) ? $filterData['annee'] : null,
                //'bailleur' => $bailleur->sigle
            ];

            $suiviFinanciers = $data;
        }

        return $suiviFinanciers;
    }

    protected function getSuiviFinancier($activites, array $filterData = null)
    {
        $suiviFinanciers = [];
        $valideActivites = [];

        foreach ($activites as $value) {
            if ($this->verifiePlageDuree($value, $filterData)) {
                array_push($valideActivites, $value);
            }
        }

        if (is_null($filterData) || !isset($filterData['annee']) || is_null($filterData['annee']) || empty($filterData['annee'])) {
            $filterData['annee'] = Carbon::now()->year;
        }

        if (is_null($filterData) || !isset($filterData['trimestre']) || is_null($filterData['trimestre']) || empty($filterData['trimestre'])) {
            $filterData['trimestre'] = $this->getCurrentTrimestre();
        }

        foreach ($valideActivites as $activite) {
            $suivi = $activite->suiviFinanciers()->when($filterData != null, function ($query) use ($filterData) {
                $query->where('trimestre', $filterData['trimestre'])->where('annee', $filterData['annee']);
            })->first();

            if ($filterData) {
                $plan = $activite->planDeDecaissement(isset($filterData['trimestre']) ? $filterData['trimestre'] : null, isset($filterData['annee']) ? $filterData['annee'] : null);
                $planParAnnee = $activite->planDeDecaissementParAnnee(isset($filterData['annee']) ? $filterData['annee'] : null);

            } else {
                $plan = $activite->planDeDecaissement();
                $planParAnnee = $activite->planDeDecaissementParAnnee();
            }

            $consommerParAnnee = $activite->suiviFinanciers()->when($filterData != null, function ($query) use ($filterData) {
                $query->where('annee', $filterData['annee']);
            })->get()->sum('consommer');

            $exercice = [
                "budget" => ($planParAnnee['budgetNational'] + $planParAnnee['pret']),
                "consommer" => $consommerParAnnee,
                "disponible" => ($planParAnnee['budgetNational'] + $planParAnnee['pret']) - $consommerParAnnee,
                "pourcentage" => ($planParAnnee['budgetNational'] != 0 || $planParAnnee['pret'] != 0) ? round(($consommerParAnnee * 100) / ($planParAnnee['budgetNational'] + $planParAnnee['pret']), 2) : 0 . " %"
            ];

            $sumBudgetNational = $activite->planDeDecaissements->sum('budgetNational');
            $sumPret = $activite->planDeDecaissements->sum('pret');

            $planCumul = $sumBudgetNational + $sumPret;

            $consommerCumul = $activite->suiviFinanciers->sum('consommer');

            $cumul = [
                "budget" => $planCumul,
                "consommer" => $consommerCumul,
                "disponible" => $planCumul - $consommerCumul,
                "pourcentage" => $planCumul != 0 ? round(($consommerCumul * 100) / $planCumul, 2) : 0 . " %"
            ];

            if ($suivi) {

                $periode = [
                    "budget" => ($plan['budgetNational'] + $plan['pret']),
                    "consommer" => $suivi->consommer,
                    "disponible" => ($plan['budgetNational'] + $plan['pret']) - $suivi->consommer,
                    "pourcentage" => ($plan['budgetNational'] != 0 || $plan['pret'] != 0) ? round(($suivi->consommer * 100) / ($plan['budgetNational'] + $plan['pret']), 2) : 0 . " %"
                ];

                $objet = [
                    //"bailleur" => $projet->bailleur->sigle,
                    "trimestre" => isset($filterData['trimestre']) ? $filterData['trimestre'] : 1,
                    "annee" => isset($filterData['annee']) ? $filterData['annee'] : Carbon::now()->year,
                    "activite" => new ActivitesResource($activite),
                    "periode" => $periode,
                    "exercice" => $exercice,
                    "cumul" => $cumul
                ];
            } else {
                $objet = [
                    //"bailleur" => $projet->bailleur->sigle,
                    "trimestre" => isset($filterData['trimestre']) ? $filterData['trimestre'] : 1,
                    "annee" => isset($filterData['annee']) ? $filterData['annee'] : Carbon::now()->year,
                    "activite" => new ActivitesResource($activite),
                    "periode" => [
                        "budget" => ($plan['budgetNational'] + $plan['pret']),
                        "consommer" => 0,
                        "disponible" => ($plan['budgetNational'] + $plan['pret']) - 0,
                        "pourcentage" => "0 %"
                    ],
                    "exercice" => $exercice,
                    "cumul" => $cumul
                ];
            }

            array_push($suiviFinanciers, $objet);
        }

        return $suiviFinanciers;
    }
}
