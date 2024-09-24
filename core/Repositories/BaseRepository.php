<?php

namespace Core\Repositories;

use App\Traits\Helpers\LogActivity;
use Exception;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

/**
 * BaseRepository qui implemente l'interface EloquentRepositoryInterface
 * @package Core\Services\Contracts
 */
class BaseRepository implements EloquentRepositoryInterface {

    /**
     * @var Model
     */
    protected $model;

    /**
     * BaseRepository constructor.
     *
     * @param Model $model
     */
    public function __construct(Model $model)
    {
        $this->model = $model;
    }


    /**
     * Créer une instance d'un Model.
     *
     *  @return Model
    */
    public function newInstance() : Model
    {
        return $this->model->newInstance();
    }

    /**
     * get model
     *
     * @return Model
     */
    public function getInstance(): Model
    {
        return $this->model;
    }

    /**
     * Créer une instance d'un nouveau Model avec ces données.
     *
     * @return Model
     */
    public function new($payload): Model
    {
        return new $this->model($payload);
    }

    /**
     * Créer une instance d'un nouveau Model avec ces données.
     *
     * @return Model
     */
    public function fill($payload): Model
    {
        return $this->model->fill($payload);
    }

    /**
     * Compter le nombre d'occurence de donnée existante d'une table.
     *
     * @return int
    */
    public function getCount(): int
    {
        return $this->model->count();
    }


    /**
     * @param array $columns
     * @param array $relations
     */
    public function paginate()
    {
        return $this->model->orderBy('created_at', 'desc')->paginate(10);
    }

    /**
     * Récupérer toutes les occurences de données existantes d'une table.
     * @param array $attributs
     * @param array $relations
     * @return Collection
     */
    public function all(array $attributs = ['*'], array $relations = []): Collection
    {
        return $this->model->with($relations)->orderByDesc('created_at')->get($attributs);
    }

    /**
     * Récupérer toutes les occurences de données partiellement supprimé d'une table.
     *
     * @return Collection
     */
    public function allTrashed(): Collection
    {
        return $this->model->onlyTrashed()->orderByDesc('created_at')->get();
    }


    /**
     * Filtrer toutes les occurences de données d'une table grâce à ses attributs.
     *
     * @param array $filtres => {
     *      attribut =>
     *      valeur =>
     *      operateur =>
     * }
     * @return Collection
     */
    public function allFiltredBy(array $filtres) : Collection {

        $collection = collect();

        foreach ($filtres as $key => $filtre) {

            if($key == 0)

                $collection = $this->model->where( $filtre['attribut'], $filtre['operateur'], $filtre['valeur'] );

            else $collection->where( $filtre['attribut'], $filtre['operateur'], $filtre['valeur'] );

        }

        return $collection->get();

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
        return $this->model->first();
    }


    /**
     * Rechercher une occurence de donnée d'une table grâce à l'attribut ID de la table.
     *
     * @param $modelId
     * @param array $attribut
     * @param array $relations
     * @param array $appends
     * @return Model
     */
    public function findById(
         $modelId,
        array $attribut = ['*'],
        array $relations = [],
        array $appends = []
    ): ?Model {

        if(is_object($modelId))
            $model = $modelId;

        else
            $model = $this->model->select($attribut)->with($relations)->findByKeyOrFail($modelId);

        if(!isset($model->id)) $model = null;

        return $model;

    }

        /**
     *
     *
     * @param string $relations
     * @param string $statut
     * @return Collection
     */

    public function etat(
        string $relations = 'statut',
        string $statut
    ): ?Collection {
        return $this->model->with([$relations => function($query) use($statut){$query->where('etat', $statut);}])->get();
    }


    /**
     * Rechercher une occurence de donnée d'une table grâce à l'un des attributs de la table.
     *
     * @param string $attributName
     * @param string $attributValue
     * @param array $columns
     * @param array $relations
     * @param array $appends
     * @return Model
     */
    public function findByAttribute(
        string $attributName,
        string $attributValue,
        array $columns = ['*'],
        array $relations = [],
        array $appends = []
    ): ?Model {
        if($attributName == "id") return null;

        return $this->model->select($columns)->where( $attributName, $attributValue )->first();
    }


    /**
     * Rechercher toutes les occurences de données et même ceux qui ont été partiellement supprimé d'une table grâce à l'attribut ID de la table.
     *
     * @param $modelId
     * @return Model
     */
    public function findTrashedById($modelId): ?Model
    {

        if(is_object($modelId))
            $model = $modelId;

        else
            $model = $this->model->withTrashed()->findByKeyOrFail($modelId);

        if(!isset($model->id)) $model = null;

        return $model;

        //return $this->model->withTrashed()->findOrFail($modelId);
    }

    /**
     * Rechercher toutes les occurences de données qui ont été partiellement supprimé d'une table grâce à l'attribut ID de la table.
     *
     * @param $modelId
     * @return Model
     */
    public function findOnlyTrashedById($modelId): ?Model
    {
        if(is_object($modelId))
            $model = $modelId;

        else
            $model = $this->model->onlyTrashed()->findByKeyOrFail($modelId);

        if(!isset($model->id)) $model = null;

        return $model;
        //return $this->model->onlyTrashed()->findOrFail($modelId);
    }

    /**
     * Créer une nouvelle occurence de données dans une table.
     *
     * @param array $payload
     * @param string $message
     * @return Model
     */
    public function create(array $payload, $message = null): ?Model
    {
        DB::beginTransaction();

        try {

            $model = $this->model->create($payload);

            $model->fresh();

            DB::commit();

            return $model;

        } catch (\Throwable $th) {

            DB::rollback();

            //throw $th;
            throw new Exception( $th->getMessage(), 500);

        }
    }

    /**
     * Mettre à jour une occurence de données existante d'une table grâce à l'attribut ID de la table.
     *
     * @param $modelId
     * @param array $payload
     * @param string $message
     * @return bool
     */
    public function update($modelId, array $payload, $message = null): bool
    {
        DB::beginTransaction();

        try {

            $model = $this->findById($modelId);

            if( !($model->update($payload)) ) throw new Exception( "Erreur pendant le processus de mis à jour", 500);

            DB::commit();

            return true;

        } catch (\Throwable $th) {

            DB::rollback();

            //throw $th;
            throw new Exception( $th->getMessage(), 500);

        }
    }

    /**
     * Supprimer partiellement une occurence de données existante d'une table grâce à l'attribut ID de la table.
     *
     * @param $modelId
     * @param string $message
     * @return bool
     */
    public function deleteById($modelId, $message = null): bool
    {
        DB::beginTransaction();

        try {

            $model = $this->findById($modelId);

            if( !($model->delete()) ) throw new Exception( "Erreur pendant l'opération de suppression. Veuillez réssayer plutard", 500);

            DB::commit();

            return true;
        }
        catch (\Throwable $th)
        {
            DB::rollback();

            //throw $th;
            throw new Exception( $th->getMessage(), 500);

        }
    }

    /**
     * Restaurer une occurence de données partiellement supprimé d'une table grâce à l'attribut ID de la table.
     * Restore model by id.
     *
     * @param $modelId
     * @param string $message
     * @return bool
     */
    public function restoreById($modelId, $message = null): bool
    {
        return $this->findOnlyTrashedById($modelId)->restore();
    }

    /**
     * Supprimer définitivement une occurence de données existante d'une table grâce à l'attribut ID de la table.
     *
     * @param $modelId
     * @param string $message
     * @return bool
     */
    public function permanentlyDeleteById($modelId, $message = null): bool
    {
        DB::beginTransaction();

        try {

            $model = $this->findById($modelId);

            $this->findTrashedById($modelId)->forceDelete();

            DB::commit();

            $acteur = Auth::check() ? Auth::user()->nom . " ". Auth::user()->prenom : "Inconnu";

            $message = $message ?? Str::ucfirst($acteur) . " a supprimé définitivement un " . strtolower(class_basename($model));

            LogActivity::addToLog("Suppression", $message, get_class($model), $model->id);

            return true;

        }
        catch (\Throwable $th)
        {
            DB::rollback();

            //throw $th;
            throw new Exception( $th->getMessage(), 500);

        }
    }

}
