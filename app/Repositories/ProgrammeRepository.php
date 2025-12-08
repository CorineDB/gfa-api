<?php

namespace App\Repositories;

use App\Models\Programme;
use Core\Repositories\BaseRepository;

class ProgrammeRepository extends BaseRepository
{

   /**
    * programmeRepository constructor.
    *
    * @param Programme $programme
    */
   public function __construct(Programme $programme)
   {
       parent::__construct($programme);
   }
}
