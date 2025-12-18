<?php

namespace App\Repositories;

use App\Models\IndicateurCadreLogique;
use Core\Repositories\BaseRepository;

class IndicateurCadreLogiqueRepository extends BaseRepository
{

   /**
    * IndicateurCadreLogiqueRepository constructor.
    *
    * @param IndicateurCadreLogique $indicateurCadreLogique
    */
   public function __construct(IndicateurCadreLogique $indicateurCadreLogique)
   {
       parent::__construct($indicateurCadreLogique);
   }
}