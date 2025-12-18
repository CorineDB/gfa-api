<?php

namespace App\Repositories;

use App\Models\Recommandation;
use Core\Repositories\BaseRepository;

class RecommandationRepository extends BaseRepository
{

   /**
    * RecommandationRepository constructor.
    *
    * @param Recommandation $recommandation
    */
   public function __construct(Recommandation $recommandation)
   {
       parent::__construct($recommandation);
   }
}
