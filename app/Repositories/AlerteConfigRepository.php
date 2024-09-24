<?php

namespace App\Repositories;

use App\Models\AlerteConfig;
use Core\Repositories\BaseRepository;

class AlerteConfigRepository extends BaseRepository
{

   /**
    * projetRepository constructor.
    *
    * @param AlerteConfig $alerteConfig
    */
   public function __construct(AlerteConfig $alerteConfig)
   {
       parent::__construct($alerteConfig);
   }
}
