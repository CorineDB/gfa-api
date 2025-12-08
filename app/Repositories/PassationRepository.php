<?php

namespace App\Repositories;

use App\Models\Passation;
use Core\Repositories\BaseRepository;

class PassationRepository extends BaseRepository
{

   /**
    * projetRepository constructor.
    *
    * @param Passation $passation
    */
   public function __construct(Passation $passation)
   {
       parent::__construct($passation);
   }
}
