<?php

namespace Core\Services\Contracts;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\JsonResponse;

/**
 * Interface AbstractServiceInterface
 * @package Core\Services\Contracts
 */
interface AbstractServiceInterface {

    /**
     * Créer une instance d'un Model
     *
     * @return Model
     */
    public function newInstance(): Model;

    /**
     * Créer une instance d'un nouveau Model avec ces données.
     *
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
     * @return Illuminate\Http\JsonResponse
     */
    public function paginate(): JsonResponse;

    /**
     * Récupérer toutes les occurences de données existantes d'une table.
     *
     * @param array $columns
     * @param array $relations
     * @return Illuminate\Http\JsonResponse
     */
    public function all(array $columns = ['*'], array $relations = []): JsonResponse;


    /**
     * Filtrer toutes les occurences de données d'une table grâce à ses attributs.
     *
     * @param array $filtres
     * @param array $columns
     * @param array $relations
     * 
     * @return Illuminate\Http\JsonResponse
     */
    public function allFiltredBy(array $filtres, array $columns = ['*'], array $relations = []) : JsonResponse;

    /**
     * Récupérer toutes les occurences de données partiellement supprimé d'une table.
     *
     * @return Illuminate\Http\JsonResponse
     */
    public function allTrashed(): JsonResponse;

    /**
     * Récupérer le premier élément du module.
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
     * @return Illuminate\Http\JsonResponse
     */
    public function findById(
         $modelId,
        array $columns = ['*'],
        array $relations = [],
        array $appends = []
    ): JsonResponse;


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
    ): JsonResponse;


    /**
     * Rechercher toutes les occurences de données et même ceux qui ont été partiellement supprimé d'une table grâce à l'attribut ID de la table.
     *
     * @param $modelId
     * @return Illuminate\Http\JsonResponse
     */
    public function findTrashedById($modelId): JsonResponse;

    /**
     * Rechercher toutes les occurences de données qui ont été partiellement supprimé d'une table grâce à l'attribut ID de la table.
     *
     * @param $modelId
     * @return Illuminate\Http\JsonResponse
     */
    public function findOnlyTrashedById($modelId): JsonResponse;

    /**
     * Créer une nouvelle occurence de données dans une table.
     *
     * @param array $payload
     * @param string $message
     * @return Illuminate\Http\JsonResponse
     */
    public function create(array $payload): JsonResponse;

    /**
     * Mettre à jour une occurence de données existante d'une table grâce à l'attribut ID de la table.
     *
     * @param $modelId
     * @param array $payload
     * @param string $message
     * @return Illuminate\Http\JsonResponse
     */
    public function update($modelId, array $payload): JsonResponse;

    /**
     * Supprimer partiellement une occurence de données existante d'une table grâce à l'attribut ID de la table.
     *
     * @param $modelId
     * @param string $message
     * @return Illuminate\Http\JsonResponse
     */
    public function deleteById($modelId, $message = null): JsonResponse;

    /**
     * Restaurer une occurence de données partiellement supprimé d'une table grâce à l'attribut ID de la table.
     *
     * @param $modelId
     * @param string $message
     * @return Illuminate\Http\JsonResponse
     */
    public function restoreById($modelId, $message = null): JsonResponse;

    /**
     * Supprimer définitivement une occurence de données existante d'une table grâce à l'attribut ID de la table.
     *
     * @param $modelId
     * @param string $message
     * @return Illuminate\Http\JsonResponse
     */
    public function permanentlyDeleteById($modelId, $message = null): JsonResponse;

    /**
     * Prolongement de la date de debut et fin
     *
     * @param $modelId
     * @param string $message
     * @return Illuminate\Http\JsonResponse
     */
    public function prolonger($modelId, $attributs);
}
