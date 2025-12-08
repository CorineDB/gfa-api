<?php

namespace App\Repositories\enquetes_de_gouvernance;

use App\Models\enquetes_de_gouvernance\FicheDeSynthese;
use Core\Repositories\BaseRepository;

class FicheDeSyntheseRepository extends BaseRepository
{
   /**
    * FicheDeSyntheseRepository constructor.
    *
    * @param FicheDeSynthese $ficheDeSynthese
    */
   public function __construct(FicheDeSynthese $ficheDeSynthese)
   {
       parent::__construct($ficheDeSynthese);
   }
}
