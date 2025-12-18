<?php

namespace Core\Services\Interfaces;

use Illuminate\Http\JsonResponse;

/**
* Interface UserServiceInterface
* @package Core\Services\Interfaces
*/
interface UserServiceInterface
{
    
    
    /**
     * Récupérer les permissions d'un uitlisateur.
     *
     * @param  $userId
     * @return Illuminate\Http\JsonResponse
     */
    public function permissions($userId): JsonResponse;
}