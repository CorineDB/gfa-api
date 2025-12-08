<?php

namespace App\Repositories\enquetes_de_gouvernance;

use App\Models\enquetes_de_gouvernance\SurveyReponse;
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
