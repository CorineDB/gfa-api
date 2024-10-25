<?php

namespace App\Repositories;

use App\Models\ActionAMener;
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
