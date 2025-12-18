<?php

namespace App\Repositories;

use App\Models\SuiviIndicateurMOD;
use Core\Repositories\BaseRepository;

class SuiviIndicateurMODRepository extends BaseRepository
{

   /**
    * SuiviIndicateurMODRepository constructor.
    *
    * @param SuiviIndicateurMOD $suiviIndicateurMOD
    */
   public function __construct(SuiviIndicateurMOD $suiviIndicateurMOD)
   {
       parent::__construct($suiviIndicateurMOD);
   }
}