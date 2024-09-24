<?php

namespace App\Services;

use App\Http\Resources\indicateur\IndicateurResource;
use App\Http\Resources\indicateur\IndicateursResource;
use App\Http\Resources\suivi\SuiviIndicateurResource;
use App\Http\Resources\suivis\SuivisResource;
use App\Models\Indicateur;
use App\Models\Unitee;
use App\Repositories\BailleurRepository;
use App\Repositories\CategorieRepository;
use App\Repositories\IndicateurRepository;
use App\Repositories\UniteeMesureRepository;
use App\Repositories\UserRepository;
use App\Traits\Eloquents\DBStatementTrait;
use App\Traits\Helpers\LogActivity;
use Core\Services\Contracts\BaseService;
use Core\Services\Interfaces\IndicateurServiceInterface;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

/**
 * Interface IndicateurServiceInterface
 * @package Core\Services\Interfaces
 */
class IndicateurService extends BaseService implements IndicateurServiceInterface
{

    use DBStatementTrait;

    /**
     * @var service
     */
    protected $categorieRepository;
    protected $uniteeMesureRepository;
    protected $userRepository;
    protected $bailleurRepository;
    protected $repository;

    /**
     * IndicateurRepository constructor.
     *
     * @param IndicateurRepository $indicateurRepository
     */
    public function __construct(IndicateurRepository $indicateurRepository, UniteeMesureRepository $uniteeMesureRepository, CategorieRepository $categorieRepository, UserRepository $userRepository, BailleurRepository $bailleurRepository)
    {
        parent::__construct($indicateurRepository);
        $this->repository = $indicateurRepository;
        $this->uniteeMesureRepository = $uniteeMesureRepository;
        $this->userRepository = $userRepository;
        $this->bailleurRepository = $bailleurRepository;
        $this->categorieRepository = $categorieRepository;
    }

    public function all(array $columns = ['*'], array $relations = []): JsonResponse
    {

        try {

            $indicateurs = [];

            if (Auth::user()->hasRole('bailleur')) {
                $indicateurs = /* Cache::remember('indicateurs-bailleur'.Auth::user()->profilable->id, 60, function(){*/
                    Indicateur::where('programmeId', Auth::user()->programmeId)
                    ->where('bailleurId', Auth::user()->profilable->id)
                    ->get();
                //});
            } else {
                
                $indicateurs = [];
                $bailleurs = Auth::user()->programme->bailleurs->load('profilable')->pluck("profilable");

                foreach ($bailleurs as $bailleur) {
                    $bindicateurs = Indicateur::where('programmeId', Auth::user()->programmeId)
                        ->where('bailleurId', $bailleur->id)
                        ->orderBy('created_at', 'asc')
                        ->get();

                    foreach ($bindicateurs as $indicateur) {
                        array_push($indicateurs, $indicateur);
                    }
                }
            }


            //$indicateurs = Indicateur::where('programmeId', Auth::user()->programmeId)->get();

            return response()->json(['statut' => 'success', 'message' => null, 'data' => IndicateursResource::collection($indicateurs), 'statutCode' => Response::HTTP_OK], Response::HTTP_OK);
        } catch (\Throwable $th) {

            DB::rollBack();

            //throw $th;
            return response()->json(['statut' => 'error', 'message' => $th->getMessage(), 'errors' => [], 'statutCode' => Response::HTTP_INTERNAL_SERVER_ERROR], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    public function filtre(array $attributs): JsonResponse
    {

        try {

            if (!array_key_exists('nom', $attributs)) {
                $attributs = array_merge($attributs, ['nom' => '']);
            }

            if (!array_key_exists('anneeDeBase', $attributs)) {
                $attributs = array_merge($attributs, ['anneeDeBase' => '']);
            }

            if (!array_key_exists('uniteeDeMesureId', $attributs)) {
                $attributs = array_merge($attributs, ['uniteeDeMesureId' => '']);
            }

            if (!array_key_exists('categorieId', $attributs)) {
                $attributs = array_merge($attributs, ['categorieId' => '']);
            }

            if (!array_key_exists('bailleurId', $attributs)) {
                $attributs = array_merge($attributs, ['bailleurId' => '']);
            }

            $indicateurs = Indicateur::where('nom', "like", "%{$attributs['nom']}%")->where('anneeDeBase', 'like', "%{$attributs['anneeDeBase']}%")->where('uniteeMesureId', 'like', "%{$attributs['uniteeDeMesureId']}%")->where('categorieId', 'like', "%{$attributs['categorieId']}%")->where('bailleurId', 'like', "%{$attributs['bailleurId']}%")->get();

            return response()->json(['statut' => 'success', 'message' => null, 'data' => IndicateursResource::collection($indicateurs), 'statutCode' => Response::HTTP_OK], Response::HTTP_OK);
        } catch (\Throwable $th) {

            DB::rollBack();

            //throw $th;
            return response()->json(['statut' => 'error', 'message' => $th->getMessage(), 'errors' => [], 'statutCode' => Response::HTTP_INTERNAL_SERVER_ERROR], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * Verife suivi.
     *
     * @param  $userId
     * @return Illuminate\Http\JsonResponse
     */
    public function checkSuivi($idIndicateur, $year): JsonResponse
    {


        try {

            if (!$indicateur = $this->repository->findById($idIndicateur)) throw new Exception("Indicateur inconnu", 500);

            $suivisIndicateur = $indicateur->valeursCible->where("annee", $year)->count();

            //$suivisIndicateur = $this->repository->all()->load("valeurCible")->pluck("valeurCible")->where(["cibleable_type" => "App\Models\Indicateur", "cibleable_id" => $idIndicateur, "annee" => $year])->count();

            return response()->json(['statut' => 'success', 'message' => null, 'data' => $suivisIndicateur > 0 ? true : false, 'statutCode' => Response::HTTP_OK], Response::HTTP_OK);
        } catch (\Throwable $th) {

            DB::rollBack();

            //throw $th;
            return response()->json(['statut' => 'error', 'message' => $th->getMessage(), 'errors' => [], 'statutCode' => Response::HTTP_INTERNAL_SERVER_ERROR], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    public function suivis($indicateurId, array $attributs = ['*'], array $relations = []): JsonResponse
    {
        try {
            if (!($indicateur = $this->repository->findById($indicateurId)))  throw new Exception("Cet indicateur n'existe pas", 500);

            $suivis = $indicateur->suivis->pluck("suivisIndicateur")->collapse()->sortByDesc("created_at");

            return response()->json(['statut' => 'success', 'message' => null, 'data' => SuiviIndicateurResource::collection($suivis), 'statutCode' => Response::HTTP_OK], Response::HTTP_OK);
        } catch (\Throwable $th) {
            return response()->json(['statut' => 'error', 'message' => $th->getMessage(), 'errors' => []], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * Rechercher une occurence de donnée d'une table grâce à l'attribut ID de la table.
     *
     * @param $modelId
     * @param array $columns
     * @param array $relations
     * @param array $appends
     * @return Illuminate\Http\JsonResponse
     */
    public function findById(
        $modelId,
        array $columns = ['*'],
        array $relations = [],
        array $appends = []
    ): JsonResponse {

        try {

            return response()->json(['statut' => 'success', 'message' => null, 'data' => new IndicateurResource($this->repository->findById($modelId, $columns, $relations, $appends)), 'statutCode' => Response::HTTP_OK], Response::HTTP_OK);
        } catch (\Throwable $th) {

            $message = $th->getMessage();

            $code = Response::HTTP_INTERNAL_SERVER_ERROR;

            if (str_contains($message, "No query results for model")) {

                $message = "Aucun résultats";

                $code = Response::HTTP_NOT_FOUND;
            }

            return response()->json(['statut' => 'error', 'message' => $message, 'errors' => [], 'statutCode' => $code], $code);
        }
    }

    /**
     * Création d'un indicateur
     *
     *
     */
    public function create($attributs): JsonResponse
    {
        DB::beginTransaction();

        try {

            $programme = Auth::user()->programme;

            $attributs = array_merge($attributs, ['programmeId' => $programme->id]);

            if (array_key_exists('categorieId', $attributs) && !($attributs['categorieId'] = $this->categorieRepository->findById($attributs['categorieId']))) throw new Exception("Catégorie inconnue", 404);

            else if (isset($attributs['categorieId']) && $attributs['categorieId']->id) $attributs['categorieId'] = $attributs['categorieId']->id;

            else  $attributs['categorieId'] = 0;

            $unitee = Unitee::find($attributs['uniteeMesureId']);

            if ($unitee->type && !ctype_digit($attributs['valeurDeBase'])) {
                throw new Exception("La valeur de base ne doit pas contenir de lettre à cause de l'unitée de mesure sélectionnée", 422);
            }

            $indicateur = $this->repository->fill(array_merge($attributs, ["bailleurId" => $attributs['bailleurId'], "uniteeMesureId" => $attributs['uniteeMesureId'], "categorieId" => $attributs['categorieId']]));

            $this->changeState(0);

            $indicateur->save();

            $this->changeState(1);

            $acteur = Auth::check() ? Auth::user()->nom . " " . Auth::user()->prenom : "Inconnu";

            $message = $message ?? Str::ucfirst($acteur) . " a créé un " . strtolower(class_basename($indicateur));

            LogActivity::addToLog("Modification", $message, get_class($indicateur), $indicateur->id);

            DB::commit();

            Cache::forget('indicateurs');


            return response()->json(['statut' => 'success', 'message' => null, 'data' => new IndicateurResource($indicateur), 'statutCode' => Response::HTTP_OK], Response::HTTP_OK);
        } catch (\Throwable $th) {

            DB::rollback();

            //throw $th;
            return response()->json(['statut' => 'error', 'message' => $th->getMessage(), 'errors' => []], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    public function update($indicateur, array $attributs): JsonResponse
    {

        DB::beginTransaction();

        try {

            if (is_string($indicateur)) {
                $indicateur = $this->repository->findById($indicateur);
            } else {
                $indicateur = $indicateur;
            }

            $programme = Auth::user()->programme;

            $attributs = array_merge($attributs, ['programmeId' => $programme->id]);

            $indicateur = $indicateur->fill($attributs);

            $this->changeState(0);

            $indicateur->save();

            $this->changeState(1);

            $acteur = Auth::check() ? Auth::user()->nom . " " . Auth::user()->prenom : "Inconnu";

            $message = $message ?? Str::ucfirst($acteur) . " a modifié un " . strtolower(class_basename($indicateur));

            LogActivity::addToLog("Modification", $message, get_class($indicateur), $indicateur->id);

            DB::commit();

            return response()->json(['statut' => 'success', 'message' => "Indicateur modifié", 'data' => [], 'statutCode' => Response::HTTP_OK], Response::HTTP_OK);
        } catch (\Throwable $th) {

            DB::rollback();

            //throw $th;
            return response()->json(['statut' => 'error', 'message' => $th->getMessage(), 'errors' => []], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    private function nouvelleUniteeMesure($nom)
    {
        return $this->uniteeMesureRepository->create(["nom" => $nom]);
    }
}
