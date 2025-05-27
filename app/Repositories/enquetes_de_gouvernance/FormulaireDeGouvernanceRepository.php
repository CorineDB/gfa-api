<?php

namespace App\Repositories\enquetes_de_gouvernance;

use App\Models\enquetes_de_gouvernance\FormulaireDeGouvernance;
use Core\Repositories\BaseRepository;

class FormulaireDeGouvernanceRepository extends BaseRepository
{

   /**
    * FormulaireDeGouvernanceRepository constructor.
    *
    * @param FormulaireDeGouvernance $formulaireDeGouvernance
    */
   public function __construct(FormulaireDeGouvernance $formulaireDeGouvernance)
   {
       parent::__construct($formulaireDeGouvernance);
   }
}
