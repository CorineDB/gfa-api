<?php

namespace App\Repositories\enquetes_de_gouvernance;

use App\Models\enquetes_de_gouvernance\SoumissionDePerception;
use Core\Repositories\BaseRepository;

class SoumissionDePerceptionRepository extends BaseRepository
{

   /**
    * SoumissionDePerceptionRepository constructor.
    *
    * @param SoumissionDePerception $soumission
    */
   public function __construct(SoumissionDePerception $soumission)
   {
       parent::__construct($soumission);
   }
}
