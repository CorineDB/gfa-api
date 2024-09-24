<?php

namespace App\Repositories;

use App\Models\Sinistre;
use Core\Repositories\BaseRepository;

class SinistreRepository extends BaseRepository
{

   /**
    * sinistreRepository constructor.
    *
    * @param Sinistre $sinistre
    */
   public function __construct(Sinistre $sinistre)
   {
       parent::__construct($sinistre);
   }
}
