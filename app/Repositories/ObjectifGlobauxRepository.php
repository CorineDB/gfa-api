<?php

namespace App\Repositories;

use App\Models\ObjectifGlobaux;
use Core\Repositories\BaseRepository;

class ObjectifGlobauxRepository extends BaseRepository
{

   /**
    * objectifGlobauxRepository constructor.
    *
    * @param ObjectifGlobaux $objectifGlobaux
    */
   public function __construct(ObjectifGlobaux $objectifGlobaux)
   {
       parent::__construct($objectifGlobaux);
   }
}
