<?php

namespace App\Repositories;

use App\Models\OptionDeReponse;
use Core\Repositories\BaseRepository;

class OptionDeReponseRepository extends BaseRepository
{

   /**
    * OptionDeReponseRepository constructor.
    *
    * @param OptionDeReponse $optionDeReponse
    */
   public function __construct(OptionDeReponse $optionDeReponse)
   {
       parent::__construct($optionDeReponse);
   }
}
