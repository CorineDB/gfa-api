<?php

namespace Core\Services\Interfaces;

use Illuminate\Http\JsonResponse;

/**
* Interface SurveyServiceInterface
* @package Core\Services\Interfaces
*/
interface SurveyServiceInterface
{
    public function survey_reponses($surveyId): JsonResponse;
    
    public function formulaire($surveyId): JsonResponse;
}
