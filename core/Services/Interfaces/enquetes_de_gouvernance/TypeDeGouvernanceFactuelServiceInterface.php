<?php

namespace Core\Services\Interfaces\enquetes_de_gouvernance;

use Illuminate\Http\JsonResponse;

/**
* Interface TypeDeGouvernanceFactuelService
* @package Core\Services\Interfaces\enquetes_de_gouvernance
*/
interface TypeDeGouvernanceFactuelServiceInterface
{

    /**
     * Liste des principes de gouvernance
     *
     */
    public function principes($typeDeGouvernanceId, array $attributs = ['*'], array $relations = []): JsonResponse;
}
