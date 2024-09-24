<?php

namespace App\Repositories;

use App\Models\PlanDecaissement;
use Core\Repositories\BaseRepository;

class PlanDecaissementRepository extends BaseRepository
{

   /**
    * planDecaissementRepository constructor.
    *
    * @param PlanDecaissement $planDecaissement
    */
   public function __construct(PlanDecaissement $planDecaissement)
   {
       parent::__construct($planDecaissement);
   }
}
