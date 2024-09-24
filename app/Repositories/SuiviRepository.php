<?php

namespace App\Repositories;

use App\Models\Suivi;
use Core\Repositories\BaseRepository;

class SuiviRepository extends BaseRepository
{

   /**
    * suiviRepository constructor.
    *
    * @param Suivi $suivi
    */
   public function __construct(Suivi $suivi)
   {
       parent::__construct($suivi);
   }
}
