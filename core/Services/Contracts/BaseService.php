<?php

namespace Core\Services\Contracts;

use App\Models\Projet;
use App\Traits\Helpers\LogActivity;
use Core\Repositories\EloquentRepositoryInterface;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

/**
 * Base Service class implement AbstractServiceInterface
 * @package Core\Services\Contracts
 */
class BaseService implements AbstractServiceInterface{


    /**
     * @var repository
     */
    protected $repository;

    /**
     * BaseService constructor.
     *
     * @param BaseRepository $repository
     */
    public function __construct(EloquentRepositoryInterface $repository)
    {
        $this->repository = $repository;
    }


    /**
     * Créer une instance d'un Model
     *
     * @return Model
    */
    public function newInstance(): Model
    {
        return $this->repository->newInstance();
    }

    /**
     * Créer une instance d'un nouveau Model avec ces données.
     *
     * @return Model
     */
    public function new($payload): Model
    {
        return $this->repository->new($payload);
    }

    /**
     * Créer une instance d'un nouveau Model avec ces données.
     *
     * @return Model
     */
    public function fill($payload): Model
    {
        return $this->repository->fill($payload);
    }


    /**
     * Compter le nombre d'occurence de donnée existante d'une table.
     *
     * @return int
     */
    public function getCount(): int
    {
        return $this->repository->getCount();
    }


    /**
     * @param array $columns
     * @param array $relations
     * @return Illuminate\Http\JsonResponse
     */
    public function paginate(): JsonResponse
    {
        try {
            return response()->json(['statut' => 'success','message'=> null, 'data' => $this->repository->paginate(), 'statutCode' => Response::HTTP_OK],Response::HTTP_OK);
        } catch (\Throwable $th) {
            return response()->json(['statut' => 'error','message'=> $th->getMessage(),'errors' => [], 'statutCode' => Response::HTTP_INTERNAL_SERVER_ERROR], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }


    /**
     * Récupérer toutes les occurences de données existantes d'une table.
     *
     * @param array $columns
     * @param array $relations
     * @return Illuminate\Http\JsonResponse
     */
    public function all(array $columns = ['*'], array $relations = []): JsonResponse
    {
        try {
            return response()->json(['statut' => 'success','message'=> null, 'data' => $this->repository->all($columns, $relations), 'statutCode' => Response::HTTP_OK],Response::HTTP_OK);
        } catch (\Throwable $th) {
            return response()->json(['statut' => 'error','message'=> $th->getMessage(),'errors' => [], 'statutCode' => Response::HTTP_INTERNAL_SERVER_ERROR], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * Filtrer toutes les occurences de données d'une table grâce à ses attributs.
     *
     * @param array $filtres
     * @param array $columns
     * @param array $relations
     * 
     * @return Illuminate\Http\JsonResponse
     */
    public function allFiltredBy(array $filtres, array $columns = ['*'], array $relations = []) : JsonResponse
    {
        try {

            /*$collection = new \Illuminate\Database\Eloquent\Collection();

            foreach ($filtres as $key => $filtre) {

                if($key == 0)

                    $collection = $this->repository->model->where( $filtre['attribut'], $filtre['operateur'], $filtre['valeur'] );

                else $collection->where( $filtre['attribut'], $filtre['operateur'], $filtre['valeur'] );

            }*/

            return response()->json(['statut' => 'success','message'=> null, 'data' => $this->repository->filterBy($filtres, $columns, $relations), 'statutCode' => Response::HTTP_OK],Response::HTTP_OK);
        } catch (\Throwable $th) {
            return response()->json(['statut' => 'error','message'=> $th->getMessage(),'errors' => [], 'statutCode' => Response::HTTP_INTERNAL_SERVER_ERROR], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * Récupérer toutes les occurences de données partiellement supprimé d'une table.
     *
     * @return Illuminate\Http\JsonResponse
     */
    public function allTrashed(): JsonResponse{

        try {
            return response()->json(['statut' => 'success','message'=> null, 'data' => $this->repository->allTrashed(), 'statutCode' => Response::HTTP_OK],Response::HTTP_OK);
        } catch (\Throwable $th) {
            return response()->json(['statut' => 'error','message'=> $th->getMessage(),'errors' => [], 'statutCode' => Response::HTTP_INTERNAL_SERVER_ERROR], Response::HTTP_INTERNAL_SERVER_ERROR);
        }

    }

    /**
     * Récupérer le premier élément de la resource.
     *
     * @param array $columns
     * @param array $relations
     * @param array $appends
     * @return Model
     */
    public function firstItem(
        array $columns = ['*'],
        array $relations = [],
        array $appends = []
    ): ?Model
    {
        return $this->repository->firstItem();
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

            return response()->json(['statut' => 'success','message'=> null, 'data' => $this->repository->findById($modelId, $columns, $relations, $appends), 'statutCode' => Response::HTTP_OK],Response::HTTP_OK);

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
     * Rechercher une occurence de donnée d'une table grâce à l'un des attributs de la table.
     *
     * @param string $attributName
     * @param string $attributValue
     * @param array $columns
     * @param array $relations
     * @param array $appends
     * @return Illuminate\Http\JsonResponse
     */
    public function findByAttribute(
        string $attributName,
        string $attributValue,
        array $columns = ['*'],
        array $relations = [],
        array $appends = []
    ): JsonResponse {

        try {
            return response()->json(['statut' => 'success','message'=> null, 'data' => $this->repository->findByAttribute($attributName, $attributValue, $columns, $relations, $appends), 'statutCode' => Response::HTTP_OK],Response::HTTP_OK);
        } catch (\Throwable $th) {
            return response()->json(['statut' => 'error','message'=> $th->getMessage(),'errors' => [], 'statutCode' => Response::HTTP_INTERNAL_SERVER_ERROR], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }


    /**
     * Rechercher toutes les occurences de données et même ceux qui ont été partiellement supprimé d'une table grâce à l'attribut ID de la table.
     *
     * @param $modelId
     * @return Illuminate\Http\JsonResponse
     */
    public function findTrashedById($modelId): JsonResponse
    {
        try {

            return response()->json(['statut' => 'success','message'=> null, 'data' => $this->repository->findTrashedById($modelId), 'statutCode' => Response::HTTP_OK], Response::HTTP_OK);

        } catch (\Throwable $th) {
            return response()->json(['statut' => 'error','message'=> $th->getMessage(),'errors' => [], 'statutCode' => Response::HTTP_INTERNAL_SERVER_ERROR], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * Rechercher toutes les occurences de données qui ont été partiellement supprimé d'une table grâce à l'attribut ID de la table.
     *
     * @param $modelId
     * @return Illuminate\Http\JsonResponse
     */
    public function findOnlyTrashedById($modelId): JsonResponse
    {
        try {
            return response()->json(['statut' => 'success','message'=> null, 'data' => $this->repository->findOnlyTrashedById($modelId), 'statutCode' => Response::HTTP_OK], Response::HTTP_OK);
        } catch (\Throwable $th) {
            return response()->json(['statut' => 'error','message'=> $th->getMessage(),'errors' => [], 'statutCode' => Response::HTTP_INTERNAL_SERVER_ERROR], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * Créer une nouvelle occurence de données dans une table.
     *
     * @param array $payload
     * @param string $message
     * @return Illuminate\Http\JsonResponse
     */
    public function create(array $payload): JsonResponse
    {
        try {

            $model = $this->repository->create($payload);

            $acteur = Auth::check() ? Auth::user()->nom . " ". Auth::user()->prenom : "Inconnu";

            $message = $message ?? Str::ucfirst($acteur) . " a crée un " . strtolower(class_basename($model));

            //LogActivity::addToLog("Enrégistrement", $message, get_class($model), $model->id);

            return response()->json(['statut' => 'success','message'=> null, 'data' => $model, 'statutCode' => Response::HTTP_OK], Response::HTTP_OK);

        } catch (\Throwable $th) {
            return response()->json(['statut' => 'error','message'=> $th->getMessage(),'errors' => [], 'statutCode' => Response::HTTP_INTERNAL_SERVER_ERROR], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * Mettre à jour une occurence de données existante d'une table grâce à l'attribut ID de la table.
     *
     * @param $modelId
     * @param array $payload
     * @param string $message
     * @return Illuminate\Http\JsonResponse
     */
    public function update($modelId, array $payload): JsonResponse
    {
        try {


            $model = $this->repository->findById($modelId);

            $this->repository->update($modelId, $payload);

            $model->fresh();

            $acteur = Auth::check() ? Auth::user()->nom . " ". Auth::user()->prenom : "Inconnu";

            $message = $message ?? Str::ucfirst($acteur) . " a modifié un " . strtolower(class_basename($model));

            //LogActivity::addToLog("Modification", $message, get_class($model), $model->id);


            return response()->json(['statut' => 'success','message'=> null, 'data' => $model, 'statutCode' => Response::HTTP_OK], Response::HTTP_OK);

        } catch (\Throwable $th) {
            return response()->json(['statut' => 'error','message'=> $th->getMessage(),'errors' => [], 'statutCode' => Response::HTTP_INTERNAL_SERVER_ERROR], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }


    /**
     * Prolongement de la date de debut et fin
     *
     * @param $modelId
     * @param string $message
     * @return Illuminate\Http\JsonResponse
     */
    public function prolonger($modelId, $attributs)
    {

        try
        {

            $model = $this->repository->findById($modelId);

            if(!array_key_exists('debut', $attributs)){
                $attributs['debut'] = $model->debut;
            }

            if(!array_key_exists('fin', $attributs)){
                $attributs['fin'] = $model->fin;
            }

            $model->durees()->create($attributs);

            if( $model instanceof Projet){

                $model->fill($attributs);
            
                $model->save();
    
                $model = $model->fresh();
            }

            $acteur = Auth::check() ? Auth::user()->nom . " ". Auth::user()->prenom : "Inconnu";

            $message = $message ?? Str::ucfirst($acteur) . " a prolonger la date de fin du module " . strtolower(class_basename($model)) . ' : ' . $model->nom;

            //LogActivity::addToLog("Prolongement de date", $message, get_class($model), $model->id);

            return response()->json(['statut' => 'success','message'=> null, 'data' => $model, 'statutCode' => Response::HTTP_OK], Response::HTTP_OK);
        }
        catch (\Throwable $th)
        {
            return response()->json(['statut' => 'error', 'message'=> $th->getMessage(), 'errors' => [], 'statutCode' => Response::HTTP_INTERNAL_SERVER_ERROR], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * Supprimer partiellement une occurence de données existante d'une table grâce à l'attribut ID de la table.
     *
     * @param $modelId
     * @param string $message
     * @return Illuminate\Http\JsonResponse
     */
    public function deleteById($modelId, $message = null): JsonResponse
    {
        try {

            $model = $this->repository->findById($modelId);

            $model->delete($modelId);

            $acteur = Auth::check() ? Auth::user()->nom . " ". Auth::user()->prenom : "Inconnu";

            $message = $message ?? Str::ucfirst($acteur) . " a supprimé un " . strtolower(class_basename($model));

            //LogActivity::addToLog("Suppression", $message, get_class($model), $model->id);

            return response()->json(['statut' => 'success','message'=> null, 'data' => $model , 'statutCode' => Response::HTTP_OK], Response::HTTP_OK);

        } catch (\Throwable $th) {
            return response()->json(['statut' => 'error','message'=> $th->getMessage(),'errors' => [], 'statutCode' => Response::HTTP_INTERNAL_SERVER_ERROR], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * Restaurer une occurence de données partiellement supprimé d'une table grâce à l'attribut ID de la table.
     *
     * @param $modelId
     * @param string $message
     * @return Illuminate\Http\JsonResponse
     */
    public function restoreById($modelId, $message = null): JsonResponse
    {
        try {

            return response()->json(['statut' => 'success','message'=> null, 'data' => $this->repository->restoreById($modelId), 'statutCode' => Response::HTTP_OK], Response::HTTP_OK);

        } catch (\Throwable $th) {
            return response()->json(['statut' => 'error','message'=> $th->getMessage(),'errors' => [], 'statutCode' => Response::HTTP_INTERNAL_SERVER_ERROR], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * Supprimer définitivement une occurence de données existante d'une table grâce à l'attribut ID de la table.
     *
     * @param $modelId
     * @param string $message
     * @return Illuminate\Http\JsonResponse
     */
    public function permanentlyDeleteById($modelId, $message = null): JsonResponse
    {
        try {

            return response()->json(['statut' => 'success','message'=> null, 'data' => $this->repository->permanentlyDeleteById($modelId), 'statutCode' => Response::HTTP_OK], Response::HTTP_OK);

        } catch (\Throwable $th) {
            return response()->json(['statut' => 'error','message'=> $th->getMessage(),'errors' => [], 'statutCode' => Response::HTTP_INTERNAL_SERVER_ERROR], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

}
