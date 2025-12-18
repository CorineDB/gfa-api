<?php

namespace App\Repositories;

use App\Models\Statut;
use Core\Repositories\BaseRepository;

class StatutRepository extends BaseRepository
{

   /**
    * statutRepository constructor.
    *
    * @param Statut $statut
    */
   public function __construct(Statut $statut)
   {
       parent::__construct($statut);
   }
}
