<?php

namespace App\Repositories\enquetes_de_gouvernance;

use App\Models\enquetes_de_gouvernance\Survey;
use Core\Repositories\BaseRepository;

class SurveyRepository extends BaseRepository
{

   /**
    * SurveyRepository constructor.
    *
    * @param Survey $survey
    */
   public function __construct(Survey $survey)
   {
       parent::__construct($survey);
   }
}
