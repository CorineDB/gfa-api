<?php

namespace App\Services;

use App\Http\Resources\indicateur_mod\IndicateurModResource;
use App\Http\Resources\indicateur_mod\IndicateurModsResource;
use App\Http\Resources\suivi_indicateur_mod\SuiviIndicateurModResource;
use App\Http\Resources\suivi_indicateur_mod\SuivisIndicateurModResource;
use App\Repositories\BailleurRepository;
use App\Repositories\CategorieRepository;
use App\Repositories\IndicateurModRepository;
use App\Repositories\UniteeMesureRepository;
use App\Repositories\UserRepository;
use App\Traits\Eloquents\DBStatementTrait;
use App\Traits\Helpers\LogActivity;
use Core\Services\Contracts\BaseService;
use Core\Services\Interfaces\IndicateurModServiceInterface;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

/**
* Interface IndicateurModServiceInterface
* @package Core\Services\Interfaces
*/
class IndicateurModService extends BaseService implements IndicateurModServiceInterface
{

    use DBStatementTrait;

    /**
     * @var service
     */
    protected $categorieRepository;
    protected $uniteeMesureRepository;
    protected $userRepository;
    protected $modRepository;
    protected $repository;

    /**
     * IndicateurModRepository constructor.
     *
     * @param IndicateurModRepository $indicateurModRepository
     */
    public function __construct(IndicateurModRepository $indicateurModRepository, UniteeMesureRepository $uniteeMesureRepository, CategorieRepository $categorieRepository, UserRepository $userRepository, BailleurRepository $modRepository)
    {
        parent::__construct($indicateurModRepository);
        $this->repository = $indicateurModRepository;
        $this->uniteeMesureRepository = $uniteeMesureRepository;
        $this->userRepository = $userRepository;
        $this->modRepository = $modRepository;
        $this->categorieRepository = $categorieRepository;
    }

    public function all(array $columns = ['*'], array $relations = []): JsonResponse
    {

        try {

            return response()->json(['statut' => 'success', 'message' => null, 'data' => IndicateurModResource::collection($this->repository->all()), 'statutCode' => Response::HTTP_OK], Response::HTTP_OK);

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

            if(!$indicateur = $this->repository->findById($idIndicateur)) throw new Exception("Indicateur inconnu", 500);

            $suivisIndicateur = $indicateur->valeursCible->where("annee", $year)->count();

            //$suivisIndicateur = $this->repository->all()->load("valeurCible")->pluck("valeurCible")->where(["cibleable_type" => "App\Models\Indicateur", "cibleable_id" => $idIndicateur, "annee" => $year])->count();

            return response()->json(['statut' => 'success', 'message' => null, 'data' => $suivisIndicateur > 0 ? true : false , 'statutCode' => Response::HTTP_OK], Response::HTTP_OK);

        } catch (\Throwable $th) {

            DB::rollBack();

            //throw $th;
            return response()->json(['statut' => 'error', 'message' => $th->getMessage(), 'errors' => [], 'statutCode' => Response::HTTP_INTERNAL_SERVER_ERROR], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    public function suivis($indicateurId, array $attributs = ['*'], array $relations = []): JsonResponse
    {
        try
        {
           if( !($indicateur = $this->repository->findById($indicateurId)) )  throw new Exception( "Cet indicateur n'existe pas", 500);

           $suivis = $indicateur->suivis->pluck("suivisIndicateur")->collapse()->sortByDesc("created_at");

            return response()->json(['statut' => 'success', 'message' => null, 'data' => SuivisIndicateurModResource::collection( $suivis ), 'statutCode' => Response::HTTP_OK], Response::HTTP_OK);
        }

        catch (\Throwable $th)
        {
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

           return response()->json(['statut' => 'success','message'=> null, 'data' => new IndicateurModResource($this->repository->findById($modelId, $columns, $relations, $appends)), 'statutCode' => Response::HTTP_OK],Response::HTTP_OK);

       } catch (\Throwable $th) {

           $message = $th->getMessage();

           $code = Response::HTTP_INTERNAL_SERVER_ERROR;

           if(str_contains($message, "No query results for model")){

               $message = "Aucun résultats";

               $code = Response::HTTP_NOT_FOUND;
           }

           return response()->json(['statut' => 'error','message'=> $message,'errors' => [], 'statutCode' => $code], $code);
       }

   }

    /**
     * Création d'un indicateurMod
     *
     *
     */
    public function create($attributs) : JsonResponse
    {
        DB::beginTransaction();

        try {

            if( array_key_exists('categorieId', $attributs) && !($attributs['categorieId'] = $this->categorieRepository->findById($attributs['categorieId'])) ) throw new Exception("Catégorie inconnue", 404);

            else if(isset($attributs['categorieId']) && $attributs['categorieId']->id) $attributs['categorieId'] = $attributs['categorieId']->id;

            else  $attributs['categorieId'] = 0;

            $indicateurMod = $this->repository->fill(array_merge($attributs, ["modId" => $attributs['modId'], "uniteeMesureId" => $attributs['uniteeMesureId'], "categorieId" => $attributs['categorieId'], "programmeId" => Auth::user()->programmeId]));

            $this->changeState(0);

            $indicateurMod->save();

            $this->changeState(1);

            /*
                if(isset($attributs['unitees_mesure']))
                {
                    $errors = [];

                    // Attacher les unite_mesures au indicateurMod, même ceux qui ne sont pas encore crée
                    foreach ($attributs['unitees_mesure'] as $value) {

                        if(is_string($value))
                        {

                            if( $unite_mesure = $this->uniteeMesureRepository->findById($value) ) {

                                $indicateurMod->unitees_mesure()->attach([$unite_mesure->id]);
                            }

                            else{
                                array_push( $errors['errors']['unitees_mesure'], "Unité de mésure {$value} inexistante");
                            }
                        }

                        elseif(isset($value['nom']))
                        {
                            $unite_mesure = $this->uniteeMesureRepository->findByAttribute("nom", $value['nom']);

                            if(!$unite_mesure) $unite_mesure = $this->nouvelleUniteeMesure($value['nom']);

                            $indicateurMod->unitees_mesure()->save($unite_mesure);
                        }
                        else;

                    }
                }
            */

            $acteur = Auth::check() ? Auth::user()->nom . " ". Auth::user()->prenom : "Inconnu";

            $message = $message ?? Str::ucfirst($acteur) . " a créé un " . strtolower(class_basename($indicateurMod));

            //LogActivity::addToLog("Enregistrement", $message, get_class($indicateurMod), $indicateurMod->id);

            DB::commit();

            return response()->json(['statut' => 'success', 'message' => null, 'data' => new IndicateurModResource($indicateurMod), 'statutCode' => Response::HTTP_OK], Response::HTTP_OK);

        } catch (\Throwable $th) {

            DB::rollback();

            //throw $th;
            return response()->json(['statut' => 'error', 'message' => $th->getMessage(), 'errors' => []], Response::HTTP_INTERNAL_SERVER_ERROR);
        }

    }

    public function update($indicateurMod, array $attributs) : JsonResponse
    {

        DB::beginTransaction();

        try {

            if( array_key_exists('categorieId', $attributs) && !($attributs['categorieId'] = $this->categorieRepository->findById($attributs['categorieId'])) ) throw new Exception("Catégorie inconnue", 404);

            else if(isset($attributs['categorieId']) && $attributs['categorieId']->id) $attributs['categorieId'] = $attributs['categorieId']->id;

            else $attributs['categorieId'] = 0;

            if(!is_object($indicateurMod)) $indicateurMod = $this->repository->findById($indicateurMod);

            if(!$indicateurMod) throw new Exception("Indicateur Mod introuvable", 404);

            $indicateurMod = $indicateurMod->fill(array_merge($attributs, ["modId" => $attributs['modId'], "uniteeMesureId" => $attributs['uniteeMesureId'], "categorieId" => $attributs['categorieId'], "programmeId" => Auth::user()->programmeId]));

            $this->changeState(0);

            $indicateurMod->save();

            $this->changeState(1);
            /*
                if(isset($attributs['unitees_mesure']))
                {

                    $errors = [];

                    $unite_mesures = [];

                    foreach ($attributs['unitees_mesure'] as $value) {

                        if(is_string($value))
                        {
                            if( $unite_mesure = $this->uniteeMesureRepository->findById($value) ) array_push($unite_mesures, $unite_mesure->id);
                            else
                            {
                                array_push( $errors['errors']['unitees_mesure'], "Unité de mésure {$value} inexistante");
                            }
                        }

                        elseif(isset($value['nom']))
                        {

                            $unite_mesure = $this->uniteeMesureRepository->findByAttribute("nom", $value['nom']);

                            if(!$unite_mesure) $unite_mesure = $this->nouvelleUniteeMesure($value['nom']);

                            array_push($unite_mesures, $unite_mesure->id);

                        }
                        else;

                    }

                    // Mettre à jour les unités de mésure d'un indicateurMod
                    $indicateurMod->unitees_mesure()->sync($unite_mesures);

                }
            */

            $acteur = Auth::check() ? Auth::user()->nom . " ". Auth::user()->prenom : "Inconnu";

            $message = $message ?? Str::ucfirst($acteur) . " a modifié un " . strtolower(class_basename($indicateurMod));

            //LogActivity::addToLog("Modification", $message, get_class($indicateurMod), $indicateurMod->id);

            DB::commit();

            return response()->json(['statut' => 'success', 'message' => "IndicateurMod modifié", 'data' => [], 'statutCode' => Response::HTTP_OK], Response::HTTP_OK);

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
