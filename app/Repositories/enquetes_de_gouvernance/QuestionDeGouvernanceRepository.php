<?php

namespace App\Repositories\enquetes_de_gouvernance;

use App\Models\enquetes_de_gouvernance\QuestionDeGouvernance;
use Core\Repositories\BaseRepository;

class QuestionDeGouvernanceRepository extends BaseRepository
{
   /**
    * QuestionDeGouvernanceRepository constructor.
    *
    * @param QuestionDeGouvernance $questionDeGouvernance
    */
   public function __construct(QuestionDeGouvernance $questionDeGouvernance)
   {
       parent::__construct($questionDeGouvernance);
   }
}
