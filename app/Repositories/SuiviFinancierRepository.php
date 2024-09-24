<?php

namespace App\Repositories;

use App\Models\SuiviFinancier;
use Core\Repositories\BaseRepository;

class SuiviFinancierRepository extends BaseRepository
{

   /**
    * projetRepository constructor.
    *
    * @param SuiviFinancier $activite
    */
   public function __construct(SuiviFinancier $activite)
   {
       parent::__construct($activite);
   }
}
