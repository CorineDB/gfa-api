<?php

namespace App\Repositories;

use App\Models\Indicateur;
use Core\Repositories\BaseRepository;

class IndicateurRepository extends BaseRepository
{

   /**
    * IndicateurRepository constructor.
    *
    * @param Indicateur $indicateur
    */
   public function __construct(Indicateur $indicateur)
   {
       parent::__construct($indicateur);
   }
}