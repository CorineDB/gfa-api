<?php

namespace App\Repositories;

use App\Models\ReponseEvaluation;
use Core\Repositories\BaseRepository;

class ReponseEvaluationRepository extends BaseRepository
{

   /**
    * projetRepository constructor.
    *
    * @param ReponseEvaluation $reponseEvaluation
    */
   public function __construct(ReponseEvaluation $reponseEvaluation)
   {
       parent::__construct($reponseEvaluation);
   }
}
