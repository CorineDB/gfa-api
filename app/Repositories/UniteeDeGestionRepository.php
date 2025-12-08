<?php

namespace App\Repositories;

use App\Models\UniteeDeGestion;
use Core\Repositories\BaseRepository;

class UniteeDeGestionRepository extends BaseRepository
{

   /**
    * UniteeDeGestionRepository constructor.
    *
    * @param UniteeDeGestion $uniteeDeGestion
    */
   public function __construct(UniteeDeGestion $uniteeDeGestion)
   {
       parent::__construct($uniteeDeGestion);
   }
}