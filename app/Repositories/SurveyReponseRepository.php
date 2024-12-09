<?php

namespace App\Repositories;

use App\Models\SurveyReponse;
use Core\Repositories\BaseRepository;

class SurveyReponseRepository extends BaseRepository
{

   /**
    * SurveyReponseRepository constructor.
    *
    * @param SurveyReponse $survey_reponse
    */
   public function __construct(SurveyReponse $survey_reponse)
   {
       parent::__construct($survey_reponse);
   }
}
