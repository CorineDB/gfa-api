<?php

namespace App\Repositories;

use App\Models\TypeAno;
use Core\Repositories\BaseRepository;

class TypeAnoRepository extends BaseRepository
{

   /**
    * projetRepository constructor.
    *
    * @param TypeAno $typeAno
    */
   public function __construct(TypeAno $typeAno)
   {
       parent::__construct($typeAno);
   }
}
