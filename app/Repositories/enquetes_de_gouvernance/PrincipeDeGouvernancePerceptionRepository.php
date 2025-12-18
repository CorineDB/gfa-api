<?php

namespace App\Repositories\enquetes_de_gouvernance;

use App\Models\enquetes_de_gouvernance\PrincipeDeGouvernancePerception;
use Core\Repositories\BaseRepository;

class PrincipeDeGouvernancePerceptionRepository extends BaseRepository
{

   /**
    * PrincipeDeGouvernancePerceptionRepository constructor.
    *
    * @param PrincipeDeGouvernancePerception $principeDeGouvernanceDePerception
    */
   public function __construct(PrincipeDeGouvernancePerception $principeDeGouvernanceDePerception)
   {
       parent::__construct($principeDeGouvernanceDePerception);
   }
}
