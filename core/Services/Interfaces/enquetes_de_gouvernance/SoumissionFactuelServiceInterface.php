<?php

namespace Core\Services\Interfaces\enquetes_de_gouvernance;

use Illuminate\Http\JsonResponse;

/**
* Interface SoumissionFactuelServiceInterface
* @package Core\Services\Interfaces\enquetes_de_gouvernance
*/
interface SoumissionFactuelServiceInterface
{
    /**
     * Supprimer une preuve de vérification
     *
     * @param string $soumissionId L'ID de la soumission
     * @param string $preuveId L'ID de la preuve à supprimer
     * @return JsonResponse
     */
    public function deletePreuve($soumissionId, $preuveId): JsonResponse;
}
