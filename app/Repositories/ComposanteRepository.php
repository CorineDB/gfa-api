<?php

namespace App\Repositories;

use App\Models\Composante;
use Core\Repositories\BaseRepository;

class ComposanteRepository extends BaseRepository
{

   /**
    * projetRepository constructor.
    *
    * @param Composante $composante
    */
   public function __construct(Composante $composante)
   {
       parent::__construct($composante);
   }
}
