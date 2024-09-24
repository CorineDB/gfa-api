<?php

namespace App\Repositories;

use App\Models\Paye;
use Core\Repositories\BaseRepository;

class PayeRepository extends BaseRepository
{

   /**
    * projetRepository constructor.
    *
    * @param Paye $paye
    */
   public function __construct(Paye $paye)
   {
       parent::__construct($paye);
   }
}
