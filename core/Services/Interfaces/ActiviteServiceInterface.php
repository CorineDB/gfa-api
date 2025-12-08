<?php

namespace Core\Services\Interfaces;

use Illuminate\Http\JsonResponse;

/**
* Interface ActiviteServiceInterface
* @package Core\Services\Interfaces
*/
interface ActiviteServiceInterface
{

    /**
     * Liste des suivis d'une activite
     * 
     */
    public function suivis($activiteId, array $attributs = ['*'], array $relations = []): JsonResponse;

}
