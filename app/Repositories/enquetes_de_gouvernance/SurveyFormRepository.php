<?php

namespace App\Repositories\enquetes_de_gouvernance;

use App\Models\enquetes_de_gouvernance\SurveyForm;
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
