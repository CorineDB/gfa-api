<?php

namespace App\Repositories;

use App\Models\Propriete;
use Core\Repositories\BaseRepository;

class ProprieteRepository extends BaseRepository
{

   /**
    * projetRepository constructor.
    *
    * @param Propriete $propriete
    */
   public function __construct(Propriete $propriete)
   {
       parent::__construct($propriete);
   }
}
