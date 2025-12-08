<?php

namespace Core\Services\Interfaces;

use Illuminate\Http\JsonResponse;

/**
* Interface EnqueteDeCollecteServiceInterface
* @package Core\Services\Interfaces
*/
interface EnqueteDeCollecteServiceInterface
{

    /**
     * Liste des reponses de l'enquete.
     *
     * @param  $indicateurId
     * @return Illuminate\Http\JsonResponse
     */
    public function reponses_collecter($enqueteId, array $attributs = ['*'], array $relations = []): JsonResponse;


    /**
     * Effectuer une collecte de donnees pour le compte d'une enquete.
     *
     * @param  $indicateurId
     * @return Illuminate\Http\JsonResponse
     */
    public function collecter($enqueteId, array $attributs = ['*'], array $relations = []): JsonResponse;

    public function resultats($enqueteId, $organisationId, array $attributs = ['*'], array $relations = []): JsonResponse;

    /**
     * Appreciation
     *
     * @param  $enqueteId
     * @return Illuminate\Http\JsonResponse
     */
    public function appreciation($enqueteId, array $attributs = ['*'], array $relations = []): JsonResponse;
}