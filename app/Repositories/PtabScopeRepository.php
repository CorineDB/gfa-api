<?php

namespace App\Repositories;

use App\Models\PtabScope;
use Core\Repositories\BaseRepository;

class PtabScopeRepository extends BaseRepository
{

   /**
    * projetRepository constructor.
    *
    * @param PtabScope $ptabScope
    */
   public function __construct(PtabScope $ptabScope)
   {
       parent::__construct($ptabScope);
   }
}
