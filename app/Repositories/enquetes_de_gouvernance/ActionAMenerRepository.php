<?php

namespace App\Repositories\enquetes_de_gouvernance;

use App\Models\enquetes_de_gouvernance\ActionAMener;
use Core\Repositories\BaseRepository;

class ActionAMenerRepository extends BaseRepository
{

   /**
    * ActionAMenerRepository constructor.
    *
    * @param ActionAMener $actionAMener
    */
   public function __construct(ActionAMener $actionAMener)
   {
       parent::__construct($actionAMener);
   }
}
