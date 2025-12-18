<?php

namespace Core\Services\Interfaces;

use Illuminate\Http\JsonResponse;

/**
* Interface EvaluationDeGouvernanceServiceInterface
* @package Core\Services\Interfaces
*/
interface EvaluationDeGouvernanceServiceInterface
{
    public function soumissions($evaluationDeGouvernance, array $columns = ['*'], array $relations = [], array $appends = []): JsonResponse;
    public function fiches_de_synthese($evaluationDeGouvernance, array $columns = ['*'], array $relations = [], array $appends = []): JsonResponse;

    public function organisations($evaluationDeGouvernance, array $columns = ['*'], array $relations = [], array $appends = []): JsonResponse;
    public function formulaires_de_gouvernance($evaluationDeGouvernance, array $columns = ['*'], array $relations = [], array $appends = []): JsonResponse;
    
    public function envoi_mail_au_participants($evaluationDeGouvernance, array $attributs): JsonResponse;
}
