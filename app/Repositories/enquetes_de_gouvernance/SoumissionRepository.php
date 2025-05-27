<?php

namespace App\Repositories\enquetes_de_gouvernance;

use App\Models\enquetes_de_gouvernance\Soumission;
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
