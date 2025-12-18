<?php

namespace App\Repositories\enquetes_de_gouvernance;

use App\Models\enquetes_de_gouvernance\PrincipeDeGouvernanceFactuel;
use Core\Repositories\BaseRepository;

class PrincipeDeGouvernanceFactuelRepository extends BaseRepository
{

   /**
    * PrincipeFactuelDeGouvernanceRepository constructor.
    *
    * @param PrincipeDeGouvernanceFactuel $principeDeGouvernanceFactuel
    */
   public function __construct(PrincipeDeGouvernanceFactuel $principeDeGouvernanceFactuel)
   {
       parent::__construct($principeDeGouvernanceFactuel);
   }
}
