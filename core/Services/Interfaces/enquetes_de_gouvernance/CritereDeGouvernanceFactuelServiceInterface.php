<?php

namespace Core\Services\Interfaces\enquetes_de_gouvernance;

use Illuminate\Http\JsonResponse;

/**
* Interface CritereDeGouvernanceFactuelServiceInterface
* @package Core\Services\Interfaces\enquetes_de_gouvernance
*/
interface CritereDeGouvernanceFactuelServiceInterface
{

    /**
     * Liste des indicateurs de gouvernance d'un critere
     *
     * return JsonResponse
     */
    public function indicateurs($critereDeGouvernanceId, array $attributs = ['*'], array $relations = []): JsonResponse;

}
