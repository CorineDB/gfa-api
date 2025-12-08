<?php

namespace App\Repositories;

use App\Models\Soumission;
use Core\Repositories\BaseRepository;

class SoumissionRepository extends BaseRepository
{

   /**
    * SoumissionRepository constructor.
    *
    * @param Soumission $soumission
    */
   public function __construct(Soumission $soumission)
   {
       parent::__construct($soumission);
   }
}
