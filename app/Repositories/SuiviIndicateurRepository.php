<?php

namespace App\Repositories;

use App\Models\SuiviIndicateur;
use Core\Repositories\BaseRepository;

class SuiviIndicateurRepository extends BaseRepository
{

   /**
    * SuiviIndicateurRepository constructor.
    *
    * @param SuiviIndicateur $suiviIndicateur
    */
   public function __construct(SuiviIndicateur $suiviIndicateur)
   {
       parent::__construct($suiviIndicateur);
   }
}