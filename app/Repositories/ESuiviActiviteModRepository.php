<?php

namespace App\Repositories;

use App\Models\ESuiviActiviteMod;
use Core\Repositories\BaseRepository;

class ESuiviActiviteModRepository extends BaseRepository
{

   /**
    * projetRepository constructor.
    *
    * @param ESuiviActiviteMod $eActiviteMod
    */
   public function __construct(ESuiviActiviteMod $eSuiviActiviteMod)
   {
       parent::__construct($eSuiviActiviteMod);
   }
}
