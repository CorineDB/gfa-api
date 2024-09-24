<?php

namespace App\Repositories;

use App\Models\EntrepriseExecutant;
use Core\Repositories\BaseRepository;

class EntrepriseExecutantRepository extends BaseRepository
{

   /**
    * EntrepriseExecutantRepository constructor.
    *
    * @param EntrepriseExecutant $uniteeDeGestion
    */
   public function __construct(EntrepriseExecutant $uniteeDeGestion)
   {
       parent::__construct($uniteeDeGestion);
   }
}