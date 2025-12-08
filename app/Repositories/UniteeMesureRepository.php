<?php

namespace App\Repositories;

use App\Models\Unitee;
use Core\Repositories\BaseRepository;

class UniteeMesureRepository extends BaseRepository
{

   /**
    * UniteeMesureRepository constructor.
    *
    * @param Unitee $unitee
    */
   public function __construct(Unitee $unitee)
   {
       parent::__construct($unitee);
   }
}