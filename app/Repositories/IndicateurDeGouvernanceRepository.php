<?php

namespace App\Repositories;

use App\Models\IndicateurDeGouvernance;
use Core\Repositories\BaseRepository;

class IndicateurDeGouvernanceRepository extends BaseRepository
{

   /**
    * projetRepository constructor.
    *
    * @param IndicateurDeGouvernance $indicateurDeGouvernance
    */
   public function __construct(IndicateurDeGouvernance $indicateurDeGouvernance)
   {
       parent::__construct($indicateurDeGouvernance);
   }
}
