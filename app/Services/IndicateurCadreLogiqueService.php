<?php

namespace App\Services;

use App\Http\Resources\indicateur_cadre_logique\IndicateurCadreLogiqueResource;
use App\Http\Resources\indicateur_cadre_logique\IndicateursCadreLogiqueResource;
use App\Models\ObjectifSpecifique;
use App\Models\Resultat;
use App\Repositories\BailleurRepository;
use App\Repositories\CategorieRepository;
use App\Repositories\IndicateurCadreLogiqueRepository;
use App\Repositories\UniteeMesureRepository;
use App\Repositories\UserRepository;
use App\Traits\Eloquents\DBStatementTrait;
use App\Traits\Helpers\LogActivity;
use Core\Services\Contracts\BaseService;
use Core\Services\Interfaces\IndicateurCadreLogiqueServiceInterface;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;

/**
* @package Core\Services\Interfaces
*/
class IndicateurCadreLogiqueService extends BaseService implements IndicateurCadreLogiqueServiceInterface
{

    use DBStatementTrait;

    /**
     * @var service
     */
    protected $repository;

    /**
     * IndicateurCadreLogiqueRepository constructor.
     *
     * @param IndicateurCadreLogiqueRepository $indicateurCadreLogiqueRepository
     */
    public function __construct(IndicateurCadreLogiqueRepository $indicateurCadreLogiqueRepository, UniteeMesureRepository $uniteeMesureRepository, CategorieRepository $categorieRepository, UserRepository $userRepository, BailleurRepository $bailleurRepository)
    {
        parent::__construct($indicateurCadreLogiqueRepository);
        $this->repository = $indicateurCadreLogiqueRepository;
    }

    public function all(array $columns = ['*'], array $relations = []): JsonResponse
    {

        try {

            return response()->json(['statut' => 'success', 'message' => null, 'data' => IndicateursCadreLogiqueResource::collection($this->repository->all()), 'statutCode' => Response::HTTP_OK], Response::HTTP_OK);

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

       try {

           return response()->json(['statut' => 'success','message'=> null, 'data' => new IndicateurCadreLogiqueResource($this->repository->findById($modelId, $columns, $relations, $appends)), 'statutCode' => Response::HTTP_OK],Response::HTTP_OK);

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
     * Création d'un indicateur
     *
     *
     */
    public function create($attributs) : JsonResponse
    {
        DB::beginTransaction();

        try {

            if(!(array_key_exists('programmeId', $attributs)) &&
               !(array_key_exists('projetId', $attributs))  &&
               !(array_key_exists('resultatId', $attributs))  &&
               !(array_key_exists('objectifId', $attributs)))  throw new Exception( "Aucune rubrique choisis pour l'objectif specifique", 500);

            if(array_key_exists('programmeId', $attributs))
            {
                $programme = $this->programmeRepository->findById($attributs['programmeId']);

                $indicateurCadreLogique = $programme->indicateurs_cadre_logique()->create($attributs);
            }

            else if(array_key_exists('resultatId', $attributs))
            {
                $resultat = Resultat::find($attributs['resultatId']);

                $indicateurCadreLogique = $resultat->indicateurs_cadre_logique()->create($attributs);
            }

            else if(array_key_exists('objectifId', $attributs))
            {
                $objectif = ObjectifSpecifique::find($attributs['objectifId']);

                $indicateurCadreLogique = $objectif->indicateurs_cadre_logique()->create($attributs);
            }

            else
            {
                $projet = $this->projetRepository->findById($attributs['projetId']);

                $indicateurCadreLogique = $projet->indicateurs_cadre_logique()->create($attributs);
            }

            $acteur = Auth::check() ? Auth::user()->nom . " ". Auth::user()->prenom : "Inconnu";

            $message = $message ?? Str::ucfirst($acteur) . " a créé un " . strtolower(class_basename($indicateurCadreLogique));

            //LogActivity::addToLog("Enregistrement", $message, get_class($indicateurCadreLogique), $indicateurCadreLogique->id);

            DB::commit();

            return response()->json(['statut' => 'success', 'message' => "Indicateur crée", 'data' => new IndicateurCadreLogiqueResource($indicateurCadreLogique), 'statutCode' => Response::HTTP_OK], Response::HTTP_OK);

        } catch (\Throwable $th) {

            DB::rollback();

            //throw $th;
            return response()->json(['statut' => 'error', 'message' => $th->getMessage(), 'errors' => []], Response::HTTP_INTERNAL_SERVER_ERROR);
        }

    }

    public function update($indicateurCadreLogique, array $attributs) : JsonResponse
    {

        DB::beginTransaction();

        try {

            if($attributs['type'])
            {
                $type = $attributs['type'] != 'objectif_speficique' ? 'App\Models\\' . ucfirst(strtolower($attributs["type"]))  : 'App\Models\ObjectifSpecifique';
            }

            $indicateurCadreLogique = $this->repository->fill(array_merge($attributs, ["indicatable_type" => $type]));

            $indicateurCadreLogique->save();

            $acteur = Auth::check() ? Auth::user()->nom . " ". Auth::user()->prenom : "Inconnu";

            $message = $message ?? Str::ucfirst($acteur) . " a modifié un " . strtolower(class_basename($indicateurCadreLogique));

            //LogActivity::addToLog("Modification", $message, get_class($indicateurCadreLogique), $indicateurCadreLogique->id);

            DB::commit();

            return response()->json(['statut' => 'success', 'message' => "Indicateur cadre logique modifié", 'data' => [], 'statutCode' => Response::HTTP_OK], Response::HTTP_OK);

        } catch (\Throwable $th) {

            DB::rollback();

            //throw $th;
            return response()->json(['statut' => 'error', 'message' => $th->getMessage(), 'errors' => []], Response::HTTP_INTERNAL_SERVER_ERROR);
        }

    }

}
