<?php

namespace App\Repositories\enquetes_de_gouvernance;

use App\Models\enquetes_de_gouvernance\QuestionFactuelDeGouvernance;
use Core\Repositories\BaseRepository;

class QuestionFactuelDeGouvernanceRepository extends BaseRepository
{
   /**
    * QuestionFactuelDeGouvernanceRepository constructor.
    *
    * @param QuestionFactuelDeGouvernance $questionFactuelDeGouvernance
    */
   public function __construct(QuestionFactuelDeGouvernance $questionFactuelDeGouvernance)
   {
       parent::__construct($questionFactuelDeGouvernance);
   }
}
