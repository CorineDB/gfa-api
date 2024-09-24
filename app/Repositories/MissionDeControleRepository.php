<?php

namespace App\Repositories;

use App\Models\MissionDeControle;
use Core\Repositories\BaseRepository;

class MissionDeControleRepository extends BaseRepository
{

   /**
    * MissionDeControleRepository constructor.
    *
    * @param MissionDeControle $uniteeDeGestion
    */
   public function __construct(MissionDeControle $uniteeDeGestion)
   {
       parent::__construct($uniteeDeGestion);
   }
}