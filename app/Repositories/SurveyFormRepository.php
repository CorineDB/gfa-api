<?php

namespace App\Repositories;

use App\Models\SurveyForm;
use Core\Repositories\BaseRepository;

class SurveyFormRepository extends BaseRepository
{

   /**
    * SurveyFormRepository constructor.
    *
    * @param SurveyForm $survey_form
    */
   public function __construct(SurveyForm $survey_form)
   {
       parent::__construct($survey_form);
   }
}
