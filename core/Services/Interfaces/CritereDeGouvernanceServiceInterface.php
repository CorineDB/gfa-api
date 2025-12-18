<?php

namespace Core\Services\Interfaces;

use Illuminate\Http\JsonResponse;

/**
* Interface CritereDeGouvernanceServiceInterface
* @package Core\Services\Interfaces
*/
interface CritereDeGouvernanceServiceInterface
{

    /**
     * Liste des indicateurs de gouvernance d'un critere
     * 
     * return JsonResponse
     */
    public function indicateurs($critereDeGouvernanceId, array $attributs = ['*'], array $relations = []): JsonResponse;

}
