<?php

namespace Core\Services\Interfaces;

use Illuminate\Http\JsonResponse;

/**
* Interface PrincipeDeGouvernanceServiceInterface
* @package Core\Services\Interfaces
*/
interface PrincipeDeGouvernanceServiceInterface
{


    /**
     * Liste des criteres de gouvernance d'un principe
     * 
     */
    public function criteres($principeDeGouvernanceId, array $attributs = ['*'], array $relations = []): JsonResponse;

    /**
     * Liste des indicateurs de gouvernance d'un principe
     * 
     */
    public function indicateurs($principeDeGouvernanceId, array $attributs = ['*'], array $relations = []): JsonResponse;

    /**
     * Charger le formulaire de l'outil factuel
     * 
     */
    public function formulaire_factuel($programmeId, array $attributs = ['*'], array $relations = []): JsonResponse;


}
