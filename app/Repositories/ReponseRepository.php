<?php

namespace App\Repositories;

use App\Models\Reponse;
use Core\Repositories\BaseRepository;

class ReponseRepository extends BaseRepository
{

   /**
    * reponseRepository constructor.
    *
    * @param Reponse $reponse
    */
   public function __construct(Reponse $reponse)
   {
       parent::__construct($reponse);
   }
}
