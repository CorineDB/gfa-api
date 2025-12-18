<?php

namespace Core\Services\Interfaces\enquetes_de_gouvernance;

use Illuminate\Http\JsonResponse;

/**
* Interface SurveyServiceInterface
* @package Core\Services\Interfaces\enquetes_de_gouvernance
*/
interface SurveyServiceInterface
{
    public function survey_reponses($surveyId): JsonResponse;

    public function formulaire($surveyId): JsonResponse;
}
