<?php

namespace Core\Repositories;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;

/**
 * Interface EloquentRepositoryInterface
 * @package Core\Repositories
 */
interface EloquentRepositoryInterface {

    /**
     * get model
     *
     * @return Model
     */
    public function getInstance(): Model;

    /**
     * Créer une instance d'un Model
     *
     * @return Model
     */
    public function newInstance(): Model;


    /**
     * Créer une instance d'un nouveau Model avec ces données
     * @return Model
     */
    public function new($payload): Model;

    /**
     * Charger les données relatives au attributs de la table
     * @return Model
     */
    public function fill($payload): Model;


    /**
     * Compter le nombre d'occurence de donnée existante d'une table.
     *
     * @return int
     */
    public function getCount(): int;

    /**
     * @param array $columns
     * @param array $relations
     */
    public function paginate();

    /**
     * Récupérer toutes les occurences de données existantes d'une table.
     *
     * @param array $columns
     * @param array $relations
     * @return Collection
     */
    public function all(array $columns = ['*'], array $relations = []): Collection;

    /**
     * Récupérer toutes les occurences de données partiellement supprimé d'une table.
     *
     * @return Collection
     */
    public function allTrashed(): Collection;


    /**
     * Filtrer toutes les occurences de données d'une table grâce à ses attributs.
     *
     * @param array $filtres
     * @return Collection
     */
    public function allFiltredBy(array $filtres) : Collection;


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
    ): ?Model;

    /**
     * Rechercher une occurence de donnée d'une table grâce à l'attribut ID de la table.
     *
     * @param $modelId
     * @param array $columns
     * @param array $relations
     * @param array $appends
     * @return Model
     */
    public function findById(
        $modelId,
        array $columns = ['*'],
        array $relations = [],
        array $appends = []
    ): ?Model;


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
    ): ?Model;


    /**
     * Rechercher toutes les occurences de données et même ceux qui ont été partiellement supprimé d'une table grâce à l'attribut ID de la table.
     *
     * @param $modelId
     * @return Model
     */
    public function findTrashedById($modelId): ?Model;

    /**
     * Rechercher toutes les occurences de données qui ont été partiellement supprimé d'une table grâce à l'attribut ID de la table.
     *
     * @param $modelId
     * @return Model
     */
    public function findOnlyTrashedById($modelId): ?Model;

    /**
     * Créer une nouvelle occurence de données dans une table.
     *
     * @param array $payload
     * @return Model
     */
    public function create(array $payload, $message = null): ?Model;

    /**
     * Mettre à jour une occurence de données existante d'une table grâce à l'attribut ID de la table.
     *
     * @param $modelId
     * @param array $payload
     * @return bool
     */
    public function update($modelId, array $payload, $message = null): bool;

    /**
     * Supprimer partiellement une occurence de données existante d'une table grâce à l'attribut ID de la table.
     *
     * @param $modelId
     * @return bool
     */
    public function deleteById($modelId, $message = null): bool;

    /**
     * Restaurer une occurence de données partiellement supprimé d'une table grâce à l'attribut ID de la table.
     *
     * @param $modelId
     * @return bool
     */
    public function restoreById($modelId, $message = null): bool;

    /**
     * Supprimer définitivement une occurence de données existante d'une table grâce à l'attribut ID de la table.
     *
     * @param $modelId
     * @return bool
     */
    public function permanentlyDeleteById($modelId, $message = null): bool;

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
    ): ?Collection;

}
