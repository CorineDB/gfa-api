<?php

namespace App\Repositories;

use App\Models\Rappel;
use Core\Repositories\BaseRepository;

class RappelRepository extends BaseRepository
{

   /**
    * rappelRepository constructor.
    *
    * @param Rappel $rappel
    */
   public function __construct(Rappel $rappel)
   {
       parent::__construct($rappel);
   }
}
