<?php

namespace Core\Services\Interfaces;

use Illuminate\Http\JsonResponse;

/**
* Interface ComposanteServiceInterface
* @package Core\Services\Interfaces
*/
interface ComposanteServiceInterface
{

    /**
     * Liste des suivis d'une composante
     * 
     */
    public function suivis($composanteId, array $attributs = ['*'], array $relations = []): JsonResponse;

}
