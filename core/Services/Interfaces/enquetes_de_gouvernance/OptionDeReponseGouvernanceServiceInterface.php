<?php

namespace Core\Services\Interfaces\enquetes_de_gouvernance;

use Illuminate\Http\JsonResponse;

/**
* Interface OptionDeReponseGouvernanceServiceInterface
* @package Core\Services\Interfaces\enquetes_de_gouvernance
*/
interface OptionDeReponseGouvernanceServiceInterface
{
    /**
     * Liste des options de gouvernance factuel
     *
     * return JsonResponse
     */
    public function options_factuel(array $columns = ['*'], array $relations = []): JsonResponse;

    /**
     * Liste des options de gouvernance perception
     *
     * return JsonResponse
     */
    public function options_de_perception(array $columns = ['*'], array $relations = []): JsonResponse;


}
