<?php

namespace Core\Services\Interfaces;

use Illuminate\Http\JsonResponse;

/**
* Interface IndicateurServiceInterface
* @package Core\Services\Interfaces
*/
interface IndicateurServiceInterface
{
    /**
     * Filtrer les indicateurs.
     *
     * @param array $attributs
     * @return JsonResponse
     */
    public function filtre(array $attributs): JsonResponse;

    /**
     * Vérifier les suivis d'un indicateur.
     *
     * @param  $idIndicateur
     * @param  $year
     * @return JsonResponse
     */
    public function checkSuivi($idIndicateur, $year): JsonResponse;

    /**
     * Liste des suivis d'un indicateur.
     *
     * @param  $indicateurId
     * @param array $attributs
     * @param array $relations
     * @return JsonResponse
     */
    public function suivis($indicateurId, array $attributs = ['*'], array $relations = []): JsonResponse;

    /**
     * Ajouter des structures responsables à un indicateur.
     *
     * @param mixed $indicateur
     * @param array $attributs
     * @return JsonResponse
     */
    public function addStrutureResponsable($indicateur, array $attributs): JsonResponse;

    /**
     * Ajouter des années cibles à un indicateur.
     *
     * @param mixed $indicateur
     * @param array $attributs
     * @return JsonResponse
     */
    public function addAnneesCible($indicateur, array $attributs): JsonResponse;

    /**
     * Ajouter des clés de valeurs à un indicateur.
     *
     * @param  $indicateurId
     * @param array $attributs
     * @param array $relations
     * @return JsonResponse
     */
    public function addValueKeys($indicateurId, array $attributs = ['*'], array $relations = []): JsonResponse;

    /**
     * Supprimer des clés de valeurs d'un indicateur.
     *
     * @param  $indicateurId
     * @param array $attributs
     * @param array $relations
     * @return JsonResponse
     */
    public function removeValueKeys($indicateurId, array $attributs = ['*'], array $relations = []): JsonResponse;

    /**
     * Modifier les valeurs cibles d'un indicateur.
     *
     * @param mixed $indicateur
     * @param array $attributs
     * @return JsonResponse
     */
    public function updateValeursCibles($indicateur, array $attributs): JsonResponse;

    /**
     * Modifier une valeur cible pour une année spécifique.
     *
     * @param mixed $indicateur
     * @param int $annee
     * @param mixed $valeurCible
     * @return JsonResponse
     */
    public function updateValeurCibleAnnee($indicateur, int $annee, $valeurCible): JsonResponse;

    /**
     * Supprimer une valeur cible pour une année donnée.
     *
     * @param mixed $indicateur
     * @param int $annee
     * @return JsonResponse
     */
    public function deleteValeurCibleAnnee($indicateur, int $annee): JsonResponse;

    /**
     * Modifier la valeur de base d'un indicateur.
     *
     * @param mixed $indicateur
     * @param array $attributs
     * @return JsonResponse
     */
    public function updateValeurDeBase($indicateur, array $attributs): JsonResponse;

    /**
     * Changer le type d'indicateur (agrégé ↔ simple).
     *
     * @param mixed $indicateur
     * @param array $attributs
     * @return JsonResponse
     */
    public function changeIndicateurType($indicateur, array $attributs): JsonResponse;

    /**
     * Modifier complètement un indicateur.
     *
     * @param mixed $indicateur
     * @param array $attributs
     * @return JsonResponse
     */
    public function updateIndicateurComplet($indicateur, array $attributs): JsonResponse;
}