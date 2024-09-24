<?php

namespace App\Repositories;

use App\Models\ObjectifSpecifique;
use Core\Repositories\BaseRepository;

class ObjectifSpecifiqueRepository extends BaseRepository
{

   /**
    * projetRepository constructor.
    *
    * @param ObjectifSpecifique $objectifSpecifique
    */
   public function __construct(ObjectifSpecifique $objectifSpecifique)
   {
       parent::__construct($objectifSpecifique);
   }
}
