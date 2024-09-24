<?php

namespace App\Repositories;

use App\Models\Formulaire;
use Core\Repositories\BaseRepository;

class FormulaireRepository extends BaseRepository
{

   /**
    * anoRepository constructor.
    *
    * @param Formulaire $formulaire
    */
   public function __construct(Formulaire $formulaire)
   {
       parent::__construct($formulaire);
   }
}
