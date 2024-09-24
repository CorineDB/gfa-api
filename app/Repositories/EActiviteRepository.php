<?php

namespace App\Repositories;

use App\Models\EActivite;
use Core\Repositories\BaseRepository;

class EActiviteRepository extends BaseRepository
{

   /**
    * projetRepository constructor.
    *
    * @param EActivite $eactivite
    */
   public function __construct(EActivite $eactivite)
   {
       parent::__construct($eactivite);
   }
}
