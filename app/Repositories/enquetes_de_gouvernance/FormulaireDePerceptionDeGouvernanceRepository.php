<?php

namespace App\Repositories\enquetes_de_gouvernance;

use App\Models\enquetes_de_gouvernance\FormulaireDePerceptionDeGouvernance;
use Core\Repositories\BaseRepository;

class FormulaireDePerceptionDeGouvernanceRepository extends BaseRepository
{

   /**
    * FormulaireDePerceptionDeGouvernanceRepository constructor.
    *
    * @param FormulaireDePerceptionDeGouvernance $formulaireDePerceptionDeGouvernance
    */
   public function __construct(FormulaireDePerceptionDeGouvernance $formulaireDePerceptionDeGouvernance)
   {
       parent::__construct($formulaireDePerceptionDeGouvernance);
   }
}
