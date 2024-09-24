<?php

namespace App\Repositories;

use App\Models\IndicateurMod;
use Core\Repositories\BaseRepository;

class IndicateurModRepository extends BaseRepository
{

   /**
    * IndicateurModRepository constructor.
    *
    * @param IndicateurMod $indicateurMod
    */
   public function __construct(IndicateurMod $indicateurMod)
   {
       parent::__construct($indicateurMod);
   }
}