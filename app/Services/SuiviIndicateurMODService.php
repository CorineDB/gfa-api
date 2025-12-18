<?php

namespace App\Services;


use App\Http\Resources\suivi_indicateur_mod\SuiviIndicateurModResource;
use App\Http\Resources\suivi_indicateur_mod\SuivisIndicateurModResource;
use App\Models\SuiviIndicateurMOD;
use App\Repositories\IndicateurModRepository;
use App\Repositories\SuiviIndicateurMODRepository;
use App\Repositories\ValeurCibleIndicateurRepository;
use Carbon\Carbon;
use Core\Services\Contracts\BaseService;
use Core\Services\Interfaces\SuiviIndicateurMODServiceInterface;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

/**
* Interface SuiviIndicateurMODService
* @package Core\Services\Interfaces
*/
class SuiviIndicateurMODService extends BaseService implements SuiviIndicateurMODServiceInterface
{

    /**
     * @var repository
     */
    protected $valeurCibleIndicateurRepository;
    protected $repository;
    protected $indicateurRepository;

    /**
     * SuiviIndicateurMODRepository constructor.
     *
     * @param SuiviIndicateurMODRepository $suiviIndicateurMODRepository
     */
    public function __construct(SuiviIndicateurMODRepository $suiviIndicateurModRepository, IndicateurModRepository $indicateurModRepository, ValeurCibleIndicateurRepository $valeurCibleIndicateurRepository)
    {
        parent::__construct($suiviIndicateurModRepository);
        $this->repository = $suiviIndicateurModRepository;
        $this->indicateurModRepository = $indicateurModRepository;
        $this->valeurCibleIndicateurRepository = $valeurCibleIndicateurRepository;
    }

    public function all(array $columns = ['*'], array $relations = []): JsonResponse
    {

        try {

            return response()->json(['statut' => 'success', 'message' => null, 'data' => SuivisIndicateurModResource::collection($this->repository->all()), 'statutCode' => Response::HTTP_OK], Response::HTTP_OK);

        } catch (\Throwable $th) {

            DB::rollBack();

            //throw $th;
            return response()->json(['statut' => 'error', 'message' => $th->getMessage(), 'errors' => [], 'statutCode' => Response::HTTP_INTERNAL_SERVER_ERROR], Response::HTTP_INTERNAL_SERVER_ERROR);
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

       try
       {

           return response()->json(['statut' => 'success','message'=> null, 'data' => new SuiviIndicateurModResource($this->repository->findById($modelId, $columns, $relations, $appends)), 'statutCode' => Response::HTTP_OK],Response::HTTP_OK);

       }
       catch (\Throwable $th)
       {

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
    * Filtre suivi
    *
    * @param array $attributs
    * @return JsonResponse
    */
   public function filter($attributs) : JsonResponse
   {

       try {

            $suivisIndicateurMOD = SuiviIndicateurMOD::where('dateSuivie', $attributs['dateSuivie'])->get();

            if(isset($attributs['date_debut']) && $attributs['date_debut'] != null)
            {
                $suivisIndicateurMOD = $suivisIndicateurMOD->filter(function($suiviIndicateurMod) use ($attributs)
                {
                    return Carbon::parse($suiviIndicateurMod->created_at)->format("Y-m-d") >= Carbon::parse($attributs['date_debut'])->format("Y-m-d");
                });
            }

            if(isset($attributs['date_fin']) && $attributs['date_fin'] != null)
            {
                $suivisIndicateurMOD = $suivisIndicateurMOD->filter(function($suiviIndicateurMod) use ($attributs)
                {
                    return Carbon::parse($suiviIndicateurMod->created_at)->format("Y-m-d") <= Carbon::parse($attributs['date_fin'])->format("Y-m-d");
                });
            }

            if(array_key_exists('indicateurModId', $attributs) && isset($attributs['indicateurModId']))
            {
                $suivisIndicateurMOD = $suivisIndicateurMOD->filter(function($suiviIndicateurMod) use ($attributs){
                    return $suiviIndicateurMod->valeurCible->where(["cibleable_type" => "App\Models\IndicateurMod", "cibleable_id" => $attributs['indicateurModId']]);
                });
            }

            if(array_key_exists('modId', $attributs) && isset($attributs['modId']))
            {
                $suivisIndicateurMOD = $suivisIndicateurMOD->filter(function($suiviIndicateurMod) use ($attributs){
                    if($suiviIndicateurMod->valeurCible->cibleable)
                        return $suiviIndicateurMod->valeurCible->cibleable->where("modId", $attributs['modId']);
                })->values();
            }

            if(array_key_exists('categorieId', $attributs) && isset($attributs['categorieId']))
            {
                $suivisIndicateurMOD = $suivisIndicateurMOD->filter(function($suiviIndicateurMod) use ($attributs){
                    if($suiviIndicateurMod->valeurCible->cibleable)
                        return $suiviIndicateurMod->valeurCible->cibleable->where("categorieId", $attributs['categorieId']);
                })->values();
            }

           return response()->json(['statut' => 'success', 'message' => null, 'data' => SuivisIndicateurModResource::collection($suivisIndicateurMOD), 'statutCode' => Response::HTTP_OK], Response::HTTP_OK);

       } catch (\Throwable $th) {

           //throw $th;
           return response()->json(['statut' => 'error', 'message' => $th->getMessage(), 'errors' => []], Response::HTTP_INTERNAL_SERVER_ERROR);
       }

   }

   public function dateSuivie($attributs) : JsonResponse
   {

       try {

            $date = SuiviIndicateurMOD::where('trimestre', $attributs['trimestre'])->
                                     where('dateSuivie', '>=', $attributs['annee']."-01-01")->
                                     where('dateSuivie', '<=', $attributs['annee']."-12-31")->
                                     pluck('dateSuivie');

           return response()->json(['statut' => 'success', 'message' => null, 'data' => $date, 'statutCode' => Response::HTTP_OK], Response::HTTP_OK);

       } catch (\Throwable $th) {

           //throw $th;
           return response()->json(['statut' => 'error', 'message' => $th->getMessage(), 'errors' => []], Response::HTTP_INTERNAL_SERVER_ERROR);
       }

   }

    /**
     * Suivi trimestriel d'un indicateur
     *
     * @param array $attributs
     * @return JsonResponse
     */
    public function create($attributs) : JsonResponse
    {
        DB::beginTransaction();

        try {

            if( !($valeurCibleIndicateur = $this->valeurCibleIndicateurRepository->newInstance()->where("cibleable_id",$attributs['indicateurModId'])->where("annee",$attributs['annee'])->first()) ){

                if( !array_key_exists('valeurCible', $attributs) || !isset($attributs['valeurCible'])) throw new Exception("Veuillez préciser la valeur cible de l'année {$attributs['annee']} de ce suivi.", 400);

                $valeurCibleIndicateur = $this->valeurCibleIndicateurRepository->fill(array_merge($attributs, ["cibleable_id" => $attributs['indicateurModId'], "cibleable_type" => "App\\Models\\IndicateurMod"]));

                $valeurCibleIndicateur->save();

            }

            if(!array_key_exists('dateSuivie', $attributs))
            {

                switch ($attributs['trimestre']) {
                    case 1:
                        $attributs = array_merge($attributs, ['dateSuivie' => $attributs['annee']."-03-31 ".date('h:i:s')]);
                        break;

                    case 2:
                        $attributs = array_merge($attributs, ['dateSuivie' => $attributs['annee']."-06-30 ".date('h:i:s')]);
                        break;

                    case 3:
                        $attributs = array_merge($attributs, ['dateSuivie' => $attributs['annee']."-09-30 ".date('h:i:s')]);
                        break;

                    case 4:
                        $attributs = array_merge($attributs, ['dateSuivie' => $attributs['annee']."-12-31 ".date('h:i:s')]);
                        break;

                    default:
                        # code...
                        break;
                }
            }

            $suiviIndicateurMOD = $this->repository->fill(array_merge($attributs, ["valeurCibleId" => $valeurCibleIndicateur->id, "trimestre" => now()->quarter]));

            $suiviIndicateurMOD->save();

            if(isset($attributs['commentaire']))
            {
                $attributsCommentaire = ['contenu' => $attributs['commentaire'], 'auteurId' => Auth::id()];

                $suiviIndicateurMOD->commentaires()->create($attributsCommentaire);
            }

            DB::commit();

            return response()->json(['statut' => 'success', 'message' => null, 'data' => new SuivisIndicateurModResource($suiviIndicateurMOD), 'statutCode' => Response::HTTP_OK], Response::HTTP_OK);

        }
        catch (\Throwable $th)
        {

            DB::rollback();

            //throw $th;
            return response()->json(['statut' => 'error', 'message' => $th->getMessage(), 'errors' => []], Response::HTTP_INTERNAL_SERVER_ERROR);
        }

    }

    /**
     * Suivi trimestriel d'un indicateur
     *
     * @param array $attributs
     * @return JsonResponse
     */
    public function update($suiviIndicateurMOD, $attributs) : JsonResponse
    {
        DB::beginTransaction();

        try {

            if(is_string($suiviIndicateurMOD))
            {
                $suiviIndicateurMOD = $this->repository->findById($suiviIndicateurMOD);
            }
            else
            {
                $suiviIndicateurMOD = $suiviIndicateurMOD;
            }

            if( array_key_exists('valeurCible', $attributs) && isset($attributs['valeurCible']))
            {
                $suiviIndicateurMOD->valeurCible->valeurCible = $attributs['valeurCible'];

                $suiviIndicateurMOD->valeurCible->save();
            }

            if( array_key_exists('annee', $attributs) && isset($attributs['annee']))
            {
                if($suiviIndicateurMOD->valeurCible->annee != $attributs['annee'])
                {
                    if( !($valeurCibleIndicateur = $this->valeurCibleIndicateurRepository->newInstance()->where("cibleable_id", $suiviIndicateurMOD->valeurCible->cibleable_id)->where("annee",$attributs['annee'])->first()) )
                    {

                        if( !array_key_exists('valeurCible', $attributs) || !isset($attributs['valeurCible'])) throw new Exception("Veuillez préciser la valeur cible de l'année {$attributs['annee']} de ce suivi.", 400);

                        $valeurCibleIndicateur = $this->valeurCibleIndicateurRepository->fill(array_merge($attributs, ["cibleable_id" => $suiviIndicateurMOD->valeurCible->cibleable_id, "cibleable_type" => "App\\Models\\Indicateur"]));

                        $valeurCibleIndicateur->save();

                        $suiviIndicateurMOD->valeurCibleId = $valeurCibleIndicateur->id;

                        $suiviIndicateurMOD->save();
                    }

                    else{
                        $suiviIndicateurMOD->valeurCible->annee = $attributs['annee'];

                        $suiviIndicateurMOD->valeurCible->save();
                    }

                    $suiviIndicateurMOD->trimestre = now()->quarter;

                }
            }

            $suiviIndicateurMOD->valeurRealise = $attributs['valeurRealise'];

            $suiviIndicateurMOD->save();

            $suiviIndicateurMOD = $suiviIndicateurMOD->fresh();

            if(isset($attributs['commentaire']))
            {
                $attributsCommentaire = ['contenu' => $attributs['commentaire'], 'auteurId' => Auth::id()];

                $suiviIndicateurMOD->commentaires()->create($attributsCommentaire);
            }

            DB::commit();

            return response()->json(['statut' => 'success', 'message' => null, 'data' => new SuiviIndicateurModResource($suiviIndicateurMOD), 'statutCode' => Response::HTTP_OK], Response::HTTP_OK);

        } catch (\Throwable $th) {

            DB::rollback();

            //throw $th;
            return response()->json(['statut' => 'error', 'message' => $th->getMessage(), 'errors' => []], Response::HTTP_INTERNAL_SERVER_ERROR);
        }

    }

}
