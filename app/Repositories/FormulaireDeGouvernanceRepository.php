<?php

namespace App\Repositories;

use App\Models\FormulaireDeGouvernance;
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
