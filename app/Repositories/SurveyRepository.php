<?php

namespace App\Repositories;

use App\Models\Survey;
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
