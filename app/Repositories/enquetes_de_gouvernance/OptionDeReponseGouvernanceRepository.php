<?php

namespace App\Repositories\enquetes_de_gouvernance;

use App\Models\enquetes_de_gouvernance\OptionDeReponseGouvernance;
use Core\Repositories\BaseRepository;

class OptionDeReponseGouvernanceRepository extends BaseRepository
{

   /**
    * OptionDeReponseGouvernanceRepository constructor.
    *
    * @param OptionDeReponseGouvernance $optionDeReponseGouvernance
    */
   public function __construct(OptionDeReponseGouvernance $optionDeReponseGouvernance)
   {
       parent::__construct($optionDeReponseGouvernance);
   }
}
