<?php

namespace App\Repositories;

use App\Models\ValeurCibleIndicateur;
use Core\Repositories\BaseRepository;

class ValeurCibleIndicateurRepository extends BaseRepository
{

   /**
    * ValeurCibleIndicateurRepository constructor.
    *
    * @param ValeurCibleIndicateur $valeurCibleIndicateur
    */
   public function __construct(ValeurCibleIndicateur $valeurCibleIndicateur)
   {
       parent::__construct($valeurCibleIndicateur);
   }
}