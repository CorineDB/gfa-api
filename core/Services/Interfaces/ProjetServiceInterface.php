<?php

namespace Core\Services\Interfaces;

use Illuminate\Http\JsonResponse;

/**
* Interface ProjetServiceInterface
* @package Core\Services\Interfaces
*/
interface ProjetServiceInterface
{
    public function prolonger($projetId, $attributs);
}
