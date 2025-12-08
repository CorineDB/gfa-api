<?php

namespace App\Repositories;

use App\Models\Evaluation;
use Core\Repositories\BaseRepository;

class EvaluationRepository extends BaseRepository
{

   /**
    * projetRepository constructor.
    *
    * @param Evaluation $evaluation
    */
   public function __construct(Evaluation $evaluation)
   {
       parent::__construct($evaluation);
   }
}
