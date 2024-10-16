<?php

namespace App\Repositories;

use App\Models\ResultatCadreDeRendement;
use Core\Repositories\BaseRepository;

class ResultatCadreDeRendementRepository extends BaseRepository
{

   /**
    * ResultatCadreDeRendementRepository constructor.
    *
    * @param ResultatCadreDeRendement $resultatCadreDeRendement
    */
   public function __construct(ResultatCadreDeRendement $resultatCadreDeRendement)
   {
       parent::__construct($resultatCadreDeRendement);
   }
}
