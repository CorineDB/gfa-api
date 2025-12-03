<?php

namespace Core\Services\Interfaces\enquetes_de_gouvernance;

use Illuminate\Http\JsonResponse;

/**
* Interface EvaluationDeGouvernanceServiceInterface
* @package Core\Services\Interfaces\enquetes_de_gouvernance
*/
interface EvaluationDeGouvernanceServiceInterface
{
    public function soumissions($evaluationDeGouvernance, array $columns = ['*'], array $relations = [], array $appends = []): JsonResponse;
    public function soumissions_enquete_factuel($evaluationDeGouvernance): JsonResponse;
    public function soumissions_enquete_de_perception($evaluationDeGouvernance): JsonResponse;
    public function fiches_de_synthese($evaluationDeGouvernance, array $columns = ['*'], array $relations = [], array $appends = []): JsonResponse;

    public function organisations($evaluationDeGouvernance, array $columns = ['*'], array $relations = [], array $appends = []): JsonResponse;
    public function formulaires_de_gouvernance($evaluationDeGouvernance, array $columns = ['*'], array $relations = [], array $appends = []): JsonResponse;

    public function envoi_mail_au_participants($evaluationDeGouvernance, array $attributs): JsonResponse;

    public function resultats_syntheses_reviser($evaluationDeGouvernance): JsonResponse;
}
