<?php

namespace App\Repositories;

use App\Models\MOD;
use Core\Repositories\BaseRepository;

class MODRepository extends BaseRepository
{

   /**
    * MODRepository constructor.
    *
    * @param MOD $mod
    */
   public function __construct(MOD $mod)
   {
       parent::__construct($mod);
   }
}
