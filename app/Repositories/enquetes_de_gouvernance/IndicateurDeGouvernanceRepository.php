<?php

namespace App\Repositories\enquetes_de_gouvernance;

use App\Models\enquetes_de_gouvernance\IndicateurDeGouvernance;
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
