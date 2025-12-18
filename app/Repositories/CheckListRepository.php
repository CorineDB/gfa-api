<?php

namespace App\Repositories;

use App\Models\CheckList;
use Core\Repositories\BaseRepository;

class CheckListRepository extends BaseRepository
{

   /**
    * CheckListRepository constructor.
    *
    * @param CheckList $checkList
    */
   public function __construct(CheckList $checkList)
   {
       parent::__construct($checkList);
   }
}
