<?php

namespace App\Repositories;

use App\Models\Resultat;
use Core\Repositories\BaseRepository;

class ResultatRepository extends BaseRepository
{

   /**
    * projetRepository constructor.
    *
    * @param Resultat $resultat
    */
   public function __construct(Resultat $resultat)
   {
       parent::__construct($resultat);
   }
}
