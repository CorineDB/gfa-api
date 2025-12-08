<?php

namespace App\Repositories;

use App\Models\IndicateurValueKey;
use Core\Repositories\BaseRepository;

class IndicateurValueKeyRepository extends BaseRepository
{

   /**
    * IndicateurValueKeyRepository constructor.
    *
    * @param IndicateurValueKey $indicateurValueKey
    */
   public function __construct(IndicateurValueKey $indicateurValueKey)
   {
       parent::__construct($indicateurValueKey);
   }
}