<?php

namespace App\Repositories\enquetes_de_gouvernance;

use App\Models\enquetes_de_gouvernance\SoumissionFactuel;
use Core\Repositories\BaseRepository;

class SoumissionFactuelRepository extends BaseRepository
{

   /**
    * SoumissionFactuelRepository constructor.
    *
    * @param SoumissionFactuel $soumission
    */
   public function __construct(SoumissionFactuel $soumission)
   {
       parent::__construct($soumission);
   }
}
