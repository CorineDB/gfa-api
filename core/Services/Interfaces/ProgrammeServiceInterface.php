<?php

namespace Core\Services\Interfaces;

use Illuminate\Http\JsonResponse;

/**
* Interface ProgrammeServiceInterface
* @package Core\Services\Interfaces
*/
interface ProgrammeServiceInterface
{

    public function scopes($programmeId): JsonResponse;
}
