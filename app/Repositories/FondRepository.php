<?php

namespace App\Repositories;

use App\Models\Fond;
use Core\Repositories\BaseRepository;

class FondRepository extends BaseRepository
{

   /**
    * FondRepository constructor.
    *
    * @param Fond $fond
    */
   public function __construct(Fond $fond)
   {
       parent::__construct($fond);
   }
}
