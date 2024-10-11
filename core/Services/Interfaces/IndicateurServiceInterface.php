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
     * Verife suivi.
     *
     * @param  $idIndicateur
     * @return Illuminate\Http\JsonResponse
     */
    public function checkSuivi($idIndicateur, $year): JsonResponse;

    /**
     * Liste des suivis d'un indicateur.
     *
     * @param  $indicateurId
     * @return Illuminate\Http\JsonResponse
     */
    public function suivis($indicateurId, array $attributs = ['*'], array $relations = []): JsonResponse;

    /**
     * add new keys
     *
     * @param  $indicateurId
     * @return Illuminate\Http\JsonResponse
     */
    public function addValueKeys($indicateurId, array $attributs = ['*'], array $relations = []): JsonResponse;

    /**
     * add new keys
     *
     * @param  $indicateurId
     * @return Illuminate\Http\JsonResponse
     */
    public function removeValueKeys($indicateurId, array $attributs = ['*'], array $relations = []): JsonResponse;
}