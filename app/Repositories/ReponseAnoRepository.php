<?php

namespace App\Repositories;

use App\Models\ReponseAno;
use Core\Repositories\BaseRepository;

class ReponseAnoRepository extends BaseRepository
{

   /**
    * anoRepository constructor.
    *
    * @param ReponseAno $reponseAno
    */
   public function __construct(ReponseAno $reponseAno)
   {
       parent::__construct($reponseAno);
   }
}
