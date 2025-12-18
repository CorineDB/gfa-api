<?php

namespace App\Repositories;

use App\Models\Decaissement;
use Core\Repositories\BaseRepository;

class DecaissementRepository extends BaseRepository
{

   /**
    * projetRepository constructor.
    *
    * @param Decaissement $decaissement
    */
   public function __construct(Decaissement $decaissement)
   {
       parent::__construct($decaissement);
   }
}
