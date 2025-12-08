<?php

namespace App\Repositories;

use App\Models\Duree;
use Core\Repositories\BaseRepository;

class DureeRepository extends BaseRepository
{

   /**
    * projetRepository constructor.
    *
    * @param Duree $activite
    */
   public function __construct(Duree $duree)
   {
       parent::__construct($duree);
   }
}
