<?php

namespace App\Repositories;

use App\Models\SuiviFinancierMOD;
use Core\Repositories\BaseRepository;

class SuiviFinancierMODRepository extends BaseRepository
{

   /**
    * SuiviFinancierMODRepository constructor.
    *
    * @param SuiviFinancierMOD $suiviFinancierMOD
    */
   public function __construct(SuiviFinancierMOD $suiviFinancierMOD)
   {
       parent::__construct($suiviFinancierMOD);
   }
}