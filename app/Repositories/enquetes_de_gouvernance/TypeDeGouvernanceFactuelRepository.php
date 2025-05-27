<?php

namespace App\Repositories\enquetes_de_gouvernance;

use App\Models\enquetes_de_gouvernance\TypeDeGouvernanceFactuel;
use Core\Repositories\BaseRepository;

class TypeDeGouvernanceFactuelRepository extends BaseRepository
{

   /**
    * TypeDeGouvernanceFactuelRepository constructor.
    *
    * @param TypeDeGouvernanceFactuel $typeDeGouvernanceFactuel
    */
   public function __construct(TypeDeGouvernanceFactuel $typeDeGouvernanceFactuel)
   {
       parent::__construct($typeDeGouvernanceFactuel);
   }
}
