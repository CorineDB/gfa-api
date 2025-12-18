<?php

namespace App\Repositories\enquetes_de_gouvernance;

use App\Models\enquetes_de_gouvernance\QuestionDePerceptionDeGouvernance;
use Core\Repositories\BaseRepository;

class QuestionDePerceptionDeGouvernanceRepository extends BaseRepository
{
   /**
    * QuestionDePerceptionDeGouvernanceRepository constructor.
    *
    * @param QuestionDePerceptionDeGouvernance $questionDePerceptionDeGouvernance
    */
   public function __construct(QuestionDePerceptionDeGouvernance $questionDePerceptionDeGouvernance)
   {
       parent::__construct($questionDePerceptionDeGouvernance);
   }
}
