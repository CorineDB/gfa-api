<?php

namespace Core\Services\Interfaces;

use Illuminate\Http\JsonResponse;

/**
* Interface ProjetServiceInterface
* @package Core\Services\Interfaces
*/
interface TacheServiceInterface
{

    /**
     * Liste des suivis d'une tâche
     * 
     */
    public function suivis($tacheId, array $attributs = ['*'], array $relations = []): JsonResponse;

}
