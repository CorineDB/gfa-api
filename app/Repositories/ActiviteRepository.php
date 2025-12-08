<?php

namespace App\Repositories;

use App\Models\Activite;
use Core\Repositories\BaseRepository;

class ActiviteRepository extends BaseRepository
{

   /**
    * projetRepository constructor.
    *
    * @param Activite $activite
    */
   public function __construct(Activite $activite)
   {
       parent::__construct($activite);
   }
}
