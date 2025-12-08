<?php

namespace App\Repositories;

use App\Models\ESuivi;
use Core\Repositories\BaseRepository;

class ESuiviRepository extends BaseRepository
{

   /**
    * projetRepository constructor.
    *
    * @param ESuivi $eSuivi
    */
   public function __construct(ESuivi $eSuivi)
   {
       parent::__construct($eSuivi);
   }
}
