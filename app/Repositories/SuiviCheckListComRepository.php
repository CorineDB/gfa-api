<?php

namespace App\Repositories;

use App\Models\SuiviCheckListCom;
use Core\Repositories\BaseRepository;

class SuiviCheckListComRepository extends BaseRepository
{

   /**
    * SuiviCheckListComRepository constructor.
    *
    * @param SuiviCheckListCom $suiviCheckListCom
    */
   public function __construct(SuiviCheckListCom $suiviCheckListCom)
   {
       parent::__construct($suiviCheckListCom);
   }
}
