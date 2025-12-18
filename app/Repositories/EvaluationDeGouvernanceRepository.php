<?php

namespace App\Repositories;

use App\Models\EvaluationDeGouvernance;
use Core\Repositories\BaseRepository;

class EvaluationDeGouvernanceRepository extends BaseRepository
{

   /**
    * EvaluationDeGouvernanceRepository constructor.
    *
    * @param EvaluationDeGouvernance $evaluationDeGouvernanc
    */
   public function __construct(EvaluationDeGouvernance $evaluationDeGouvernance)
   {
       parent::__construct($evaluationDeGouvernance);
   }
}
