<?php

namespace App\Repositories;

use App\Models\Projet;
use Core\Repositories\BaseRepository;

class ProjetRepository extends BaseRepository
{

   /**
    * projetRepository constructor.
    *
    * @param Projet $projet
    */
   public function __construct(Projet $projet)
   {
       parent::__construct($projet);
   }
}
