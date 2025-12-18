<?php

namespace App\Repositories\enquetes_de_gouvernance;

use App\Models\enquetes_de_gouvernance\Recommandation;
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
