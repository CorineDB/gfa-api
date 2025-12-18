<?php

namespace Core\Services\Interfaces;

use Illuminate\Http\JsonResponse;

/**
* Interface PtabScopeServiceInterface
* @package Core\Services\Interfaces
*/
interface PtabScopeServiceInterface
{
    public function reviserPtab(array $attributs): JsonResponse;

    public function getOldPtaReviser(array $attributs) : JsonResponse;

    public function getPtabReviser(array $attributs): JsonResponse;

}
