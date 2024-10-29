<?php

namespace App\Repositories;

use App\Models\QuestionDeGouvernance;
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
