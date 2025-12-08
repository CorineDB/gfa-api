<?php

namespace App\Repositories;

use App\Models\EActiviteMod;
use Core\Repositories\BaseRepository;

class EActiviteModRepository extends BaseRepository
{

   /**
    * projetRepository constructor.
    *
    * @param EActiviteMod $eActiviteMod
    */
   public function __construct(EActiviteMod $eActiviteMod)
   {
       parent::__construct($eActiviteMod);
   }
}
