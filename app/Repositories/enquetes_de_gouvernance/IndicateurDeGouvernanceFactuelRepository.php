<?php

namespace App\Repositories\enquetes_de_gouvernance;

use App\Models\enquetes_de_gouvernance\IndicateurDeGouvernanceFactuel;
use Core\Repositories\BaseRepository;

class IndicateurDeGouvernanceFactuelRepository extends BaseRepository
{

   /**
    * projetRepository constructor.
    *
    * @param IndicateurDeGouvernanceFactuel $indicateurDeGouvernanceFactuel
    */
   public function __construct(IndicateurDeGouvernanceFactuel $indicateurDeGouvernanceFactuel)
   {
       parent::__construct($indicateurDeGouvernanceFactuel);
   }
}
