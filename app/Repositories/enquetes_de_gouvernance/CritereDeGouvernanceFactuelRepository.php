<?php

namespace App\Repositories\enquetes_de_gouvernance;

use App\Models\enquetes_de_gouvernance\CritereDeGouvernanceFactuel;
use Core\Repositories\BaseRepository;

class CritereDeGouvernanceFactuelRepository extends BaseRepository
{

   /**
    * projetRepository constructor.
    *
    * @param CritereDeGouvernanceFactuel $critereDeGouvernanceFactuel
    */
   public function __construct(CritereDeGouvernanceFactuel $critereDeGouvernanceFactuel)
   {
       parent::__construct($critereDeGouvernanceFactuel);
   }
}
