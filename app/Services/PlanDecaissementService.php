<?php

namespace App\Services;

use App\Http\Resources\plans\PlansDecaissementResource;
use App\Jobs\GenererPta;
use App\Models\Organisation;
use App\Models\UniteeDeGestion;
use App\Repositories\ActiviteRepository;
use App\Repositories\PlanDecaissementRepository;
use App\Traits\Helpers\LogActivity;
use Core\Services\Contracts\BaseService;
use Core\Services\Interfaces\PlanDecaissementServiceInterface;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use DateTime;
use Exception;

/**
 * Interface PlanDecaissementServiceInterface
 * @package Core\Services\Interfaces
 */
class PlanDecaissementService extends BaseService implements PlanDecaissementServiceInterface
{
    /**
     * @var service
     */
    protected $repository,
        $activiteRepository;

    /**
     * ProjetService constructor.
     *
     * @param PlanDecaissement $planDecaissementRepository
     */
    public function __construct(PlanDecaissementRepository $planDecaissementRepository, ActiviteRepository $activiteRepository)
    {
        parent::__construct($planDecaissementRepository);
        $this->repository = $planDecaissementRepository;
        $this->activiteRepository = $activiteRepository;
    }

    public function all(array $attributs = ['*'], array $relations = []): JsonResponse
    {
        try {
            $planDecaissement = [];

            $planDecaissement = null;

            if (Auth::user()->hasRole('organisation') || (get_class(auth()->user()->profilable) == Organisation::class)) {
                $planDecaissement = $this->repository->all();
            } else if (Auth::user()->hasRole('unitee-de-gestion') || (get_class(auth()->user()->profilable) == UniteeDeGestion::class)) {
                $planDecaissement = $this->repository->all();
            }

            return response()->json(['statut' => 'success', 'message' => null, 'data' => PlansDecaissementResource::collection($planDecaissement), 'statutCode' => Response::HTTP_OK], Response::HTTP_OK);
        } catch (\Throwable $th) {
            return response()->json(['statut' => 'error', 'message' => $th->getMessage(), 'errors' => []], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    public function create(array $attributs): JsonResponse
    {
        DB::beginTransaction();

        try {
            $controle = 1;

            if (!($activite = $this->activiteRepository->findById($attributs['activiteId']))) {
                throw new Exception("Cette activité n'existe pas", 404);
            }

            $attributs = array_merge($attributs, ['activiteId' => $activite->id]);

            $durees = $activite->durees;
            $trimestreValide = false;
            $validTrimestres = [];

            /* foreach ($durees as $duree) {
                $debutTab = explode('-', $duree->debut);
                $finTab = explode('-', $duree->fin);

                if ($debutTab[0] <= $attributs['annee'] && $finTab[0] >= $attributs['annee']) {
                    $controle = 0;

                    // Vérification du trimestre couvert par la durée
                    // Convert months to trimestres
                    $moisDebut = (int) $debutTab[1];
                    $moisFin = (int) $finTab[1];

                    $trimestreDebut = ceil($moisDebut / 3);
                    $trimestreFin = ceil($moisFin / 3);

                    for ($i = $trimestreDebut; $i <= $trimestreFin; $i++) {
                        $validTrimestres[] = $i;
                    }

                    if ($attributs['trimestre'] >= $trimestreDebut && $attributs['trimestre'] <= $trimestreFin) {
                        $trimestreValide = true;
                        break;
                    }
                }
            } */

            foreach ($activite->durees as $duree) {
                $debut = new DateTime($duree->debut);
                $fin = new DateTime($duree->fin);

                $anneeDebut = (int) $debut->format('Y');
                $anneeFin = (int) $fin->format('Y');

                if ($anneeDebut <= $attributs['annee'] && $anneeFin >= $attributs['annee']) {
                    $controle = false;

                    // Début et fin dans l’année ciblée
                    $startMonth = ($anneeDebut < $attributs['annee']) ? 1 : (int) $debut->format('m');
                    $endMonth = ($anneeFin > $attributs['annee']) ? 12 : (int) $fin->format('m');

                    $trimestreDebut = ceil($startMonth / 3);
                    $trimestreFin = ceil($endMonth / 3);

                    for ($t = $trimestreDebut; $t <= $trimestreFin; $t++) {
                        if (!in_array($t, $validTrimestres)) {
                            $validTrimestres[] = $t;
                        }
                    }

                    if ($attributs['trimestre'] >= $trimestreDebut && $attributs['trimestre'] <= $trimestreFin) {
                        $trimestreValide = true;
                        break;
                    }
                }
            }

            if ($controle) {
                throw new Exception("L'activité n'a aucune durée d'exécution dans l'année précisée", 500);
            }

            if (!$trimestreValide) {
                throw new Exception("Le trimestre sélectionné ne correspond pas à la période d'exécution de l'activité", 500);
            }

            // Get registered trimestres for this activity in the selected year
            $trimestres = $this
                ->repository
                ->allFiltredBy([
                    ['attribut' => 'activiteId', 'operateur' => '=', 'valeur' => $attributs['activiteId']],
                    ['attribut' => 'annee', 'operateur' => '=', 'valeur' => $attributs['annee']]
                ])
                ->pluck('trimestre');

            /*
             * if(!(count($trimestres)) && $attributs['trimestre'] != 1){
             *     throw new Exception("Vous devez d'abord faire le plan du trimestre 1", 500);
             * }
             *
             * $nombreDeTrimestre = count($trimestres);
             *
             * if($nombreDeTrimestre === 0){
             *     $max = 0;
             * }
             * else{
             *     $max = max($trimestres->all());
             * }
             *
             * if($nombreDeTrimestre == 0 && $attributs['trimestre'] > 1){
             *     throw new Exception("Vous devez d'abord faire le plan du premier trimestre", 500);
             * }
             *
             * else if( $nombreDeTrimestre == 0 && $attributs['trimestre'] == 1 );
             *
             * else
             * {
             *     if($attributs['trimestre'] <= $max){
             *         throw new Exception("Le plan du trimestre {$attributs['trimestre']} a déjà été effectué", 500);
             *     }
             *     else if($attributs['trimestre'] > $max+1)
             *     {
             *         $max = $max+1;
             *         throw new Exception("Vous devez d'abord faire le plan du trimestre {$max}", 500);
             *     }
             *
             *     else if( $nombreDeTrimestre + 1 > 4)
             *         throw new Exception("Le plan ne peut qu'être faire sur les 4 trimestres d'une année.", 1);
             * }
             */

            $registeredTrimestres = $trimestres->all();

            // Allow only if the selected trimestre is within valid execution periods
            if (!in_array($attributs['trimestre'], $validTrimestres)) {
                throw new Exception("L'activité ne peut pas être exécutée pour le trimestre {$attributs['trimestre']} " . json_encode($validTrimestres), 500);
            }

            // Allow skipping trimestres if execution periods allow it
            $maxRegistered = empty($registeredTrimestres) ? 0 : max($registeredTrimestres);

            if ($maxRegistered > 0 && $attributs['trimestre'] > $maxRegistered + 1) {
                throw new Exception("Vous devez d'abord enregistrer le trimestre " . $maxRegistered + 1 . " avant d'ajouter T" . $attributs['trimestre'], 500);
            }

            $planDecaissement = $this->repository->create(array_merge($attributs, ['programmeId' => auth()->user()->programmeId]));

            $acteur = Auth::check() ? Auth::user()->nom . ' ' . Auth::user()->prenom : 'Inconnu';

            $message = $message ?? Str::ucfirst($acteur) . ' a créé un ' . strtolower(class_basename($planDecaissement));

            // LogActivity::addToLog("Modification", $message, get_class($planDecaissement), $planDecaissement->id);

            DB::commit();

            GenererPta::dispatch(Auth::user()->programme)->delay(5);

            return response()->json(['statut' => 'success', 'message' => null, 'data' => new PlansDecaissementResource($planDecaissement), 'statutCode' => Response::HTTP_OK], Response::HTTP_OK);
        } catch (\Throwable $th) {
            DB::rollback();
            return response()->json(['statut' => 'error', 'message' => $th->getMessage(), 'errors' => []], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    public function update($planDecaissementId, array $attributs): JsonResponse
    {
        DB::beginTransaction();

        try {
            if (!($activite = $this->activiteRepository->findById($attributs['activiteId'])))
                throw new Exception("Cette activité n'existe pas", 500);
            $attributs = array_merge($attributs, ['activiteId' => $activite->id]);

            $controle = 1;

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

            if (!($plan = $this->repository->findById($planDecaissementId)))
                throw new Exception("Cet plan n'existe pas", 500);

            $trimestres = $this
                ->repository
                ->allFiltredBy([['attribut' => 'activiteId', 'operateur' => '=', 'valeur' => $attributs['activiteId']],
                    ['attribut' => 'annee', 'operateur' => '=', 'valeur' => $attributs['annee']]])
                ->pluck('trimestre');

            if (!(count($trimestres)) && $attributs['trimestre'] != 1)
                throw new Exception("Vous devez d'abord faire le plan du trimestre 1", 500);

            if (!($plan->trimestre == $attributs['trimestre']))
                throw new Exception('Vous pouvez pas modifier le trimestre', 500);

            $planDecaissement = $this->repository->findById($planDecaissementId);

            $planDecaissement = $planDecaissement->fill($attributs);

            $planDecaissement->save();

            $acteur = Auth::check() ? Auth::user()->nom . ' ' . Auth::user()->prenom : 'Inconnu';

            $message = $message ?? Str::ucfirst($acteur) . ' a modifié un ' . strtolower(class_basename($planDecaissement));

            // LogActivity::addToLog("Modification", $message, get_class($planDecaissement), $planDecaissement->id);

            DB::commit();

            $planDecaissement = $planDecaissement->fresh();

            GenererPta::dispatch(Auth::user()->programme)->delay(5);

            return response()->json(['statut' => 'success', 'message' => null, 'data' => new PlansDecaissementResource($planDecaissement), 'statutCode' => Response::HTTP_OK], Response::HTTP_OK);
        } catch (\Throwable $th) {
            DB::rollback();
            return response()->json(['statut' => 'error', 'message' => $th->getMessage(), 'errors' => []], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
}
