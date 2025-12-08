<?php

namespace Core\Services\Interfaces;

use Illuminate\Http\JsonResponse;

/**
* Interface TypeDeGouvernanceServiceInterface
* @package Core\Services\Interfaces
*/
interface TypeDeGouvernanceServiceInterface
{

    /**
     * Liste des principes de gouvernance
     * 
     */
    public function principes($typeDeGouvernanceId, array $attributs = ['*'], array $relations = []): JsonResponse;
}
