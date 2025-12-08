<?php

namespace App\Repositories;

use App\Models\Ano;
use Core\Repositories\BaseRepository;

class AnoRepository extends BaseRepository
{

   /**
    * anoRepository constructor.
    *
    * @param Ano $ano
    */
   public function __construct(Ano $ano)
   {
       parent::__construct($ano);
   }
}
