<?php

namespace App\Repositories;

use App\Models\Tache;
use Core\Repositories\BaseRepository;

class TacheRepository extends BaseRepository
{

   /**
    * projetRepository constructor.
    *
    * @param Tache $tache
    */
   public function __construct(Tache $tache)
   {
       parent::__construct($tache);
   }
}
