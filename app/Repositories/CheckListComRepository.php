<?php

namespace App\Repositories;

use App\Models\CheckListCom;
use Core\Repositories\BaseRepository;

class CheckListComRepository extends BaseRepository
{

   /**
    * CheckListComRepository constructor.
    *
    * @param CheckListCom $checkListCom
    */
   public function __construct(CheckListCom $checkListCom)
   {
       parent::__construct($checkListCom);
   }
}
