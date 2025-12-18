<?php

namespace App\Repositories;

use App\Models\EntrepriseExecutant;
use Core\Repositories\BaseRepository;

class EntrepriseExecutantRepository extends BaseRepository
{

   /**
    * EntrepriseExecutantRepository constructor.
    *
    * @param EntrepriseExecutant $entrepriseExecutant
    */
   public function __construct(EntrepriseExecutant $entrepriseExecutant)
   {
       parent::__construct($entrepriseExecutant);
   }
}