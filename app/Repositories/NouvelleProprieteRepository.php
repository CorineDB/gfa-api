<?php

namespace App\Repositories;

use App\Models\NouvellePropriete;
use Core\Repositories\BaseRepository;

class NouvelleProprieteRepository extends BaseRepository
{

   /**
    * projetRepository constructor.
    *
    * @param NouvellePropriete $nouvellePropriete
    */
   public function __construct(NouvellePropriete $nouvellePropriete)
   {
       parent::__construct($nouvellePropriete);
   }
}
