<?php

namespace App\Repositories;

use App\Models\PrincipeDeGouvernance;
use Core\Repositories\BaseRepository;

class PrincipeDeGouvernanceRepository extends BaseRepository
{

   /**
    * PrincipeDeGouvernanceRepository constructor.
    *
    * @param PrincipeDeGouvernance $principeDeGouvernance
    */
   public function __construct(PrincipeDeGouvernance $principeDeGouvernance)
   {
       parent::__construct($principeDeGouvernance);
   }
}
