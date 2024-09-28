<?php

namespace App\Repositories;

use App\Models\TypeDeGouvernance;
use Core\Repositories\BaseRepository;

class TypeDeGouvernanceRepository extends BaseRepository
{

   /**
    * TypeDeGouvernanceRepository constructor.
    *
    * @param TypeDeGouvernance $typeDeGouvernance
    */
   public function __construct(TypeDeGouvernance $typeDeGouvernance)
   {
       parent::__construct($typeDeGouvernance);
   }
}
