<?php

namespace App\Repositories;

use App\Models\CritereDeGouvernance;
use Core\Repositories\BaseRepository;

class CritereDeGouvernanceRepository extends BaseRepository
{

   /**
    * projetRepository constructor.
    *
    * @param CritereDeGouvernance $critereDeGouvernance
    */
   public function __construct(CritereDeGouvernance $critereDeGouvernance)
   {
       parent::__construct($critereDeGouvernance);
   }
}
