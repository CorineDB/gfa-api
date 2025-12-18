<?php

namespace App\Repositories\enquetes_de_gouvernance;

use App\Models\enquetes_de_gouvernance\FormulaireFactuelDeGouvernance;
use Core\Repositories\BaseRepository;

class FormulaireFactuelDeGouvernanceRepository extends BaseRepository
{

   /**
    * FormulaireFactuelDeGouvernanceRepository constructor.
    *
    * @param FormulaireFactuelDeGouvernance $formulaireFactuelDeGouvernance
    */
   public function __construct(FormulaireFactuelDeGouvernance $formulaireFactuelDeGouvernance)
   {
       parent::__construct($formulaireFactuelDeGouvernance);
   }
}
