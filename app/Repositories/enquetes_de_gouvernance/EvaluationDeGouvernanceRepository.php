<?php

namespace App\Repositories\enquetes_de_gouvernance;

use App\Models\enquetes_de_gouvernance\EvaluationDeGouvernance;
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
