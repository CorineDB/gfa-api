<?php

namespace App\Repositories;

use App\Models\OngCom;
use Core\Repositories\BaseRepository;

class OngComRepository extends BaseRepository
{

   /**
    * OngComRepository constructor.
    *
    * @param OngCom $ong_comm
    */
   public function __construct(OngCom $ong_comm)
   {
        parent::__construct($ong_comm);
   }
}