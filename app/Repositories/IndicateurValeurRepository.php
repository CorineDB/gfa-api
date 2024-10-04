<?php

namespace App\Repositories;

use App\Models\IndicateurValeur;
use Core\Repositories\BaseRepository;

class IndicateurValeurRepository extends BaseRepository
{

   /**
    * IndicateurValeurRepository constructor.
    *
    * @param IndicateurValeur $indicateurValeur
    */
   public function __construct(IndicateurValeur $indicateurValeur)
   {
       parent::__construct($indicateurValeur);
   }
}