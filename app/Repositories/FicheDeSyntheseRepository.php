<?php

namespace App\Repositories;

use App\Models\FicheDeSynthese;
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
