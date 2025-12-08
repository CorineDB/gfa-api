<?php

namespace App\Repositories;

use App\Models\Bailleur;
use Core\Repositories\BaseRepository;

class BailleurRepository extends BaseRepository
{

   /**
    * BailleurRepository constructor.
    *
    * @param Bailleur $bailleur
    */
   public function __construct(Bailleur $bailleur)
   {
       parent::__construct($bailleur);
   }
}