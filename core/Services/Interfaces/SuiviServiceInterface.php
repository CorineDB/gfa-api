<?php

namespace Core\Services\Interfaces;

use Illuminate\Http\JsonResponse;

/**
* Interface ActiviteServiceInterface
* @package Core\Services\Interfaces
*/
interface SuiviServiceInterface
{

    /**
     * Liste des suivis d'un module spécifique
     * @param array $attributs
     * @return Illuminate\Http\JsonResponse
     */
    public function getSuivis($attributs) : JsonResponse;

}
