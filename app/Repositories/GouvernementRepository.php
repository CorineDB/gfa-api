<?php

namespace App\Repositories;

use App\Models\Gouvernement;
use Core\Repositories\BaseRepository;

class GouvernementRepository extends BaseRepository
{

   /**
    * GouvernementRepository constructor.
    *
    * @param Gouvernement $gouvernement
    */
   public function __construct(Gouvernement $gouvernement)
   {
       parent::__construct($gouvernement);
   }
}