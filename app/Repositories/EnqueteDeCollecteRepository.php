<?php

namespace App\Repositories;

use App\Models\Enquete;
use Core\Repositories\BaseRepository;

class EnqueteDeCollecteRepository extends BaseRepository
{

   /**
    * EnqueteDeCollecteRepository constructor.
    *
    * @param Enquete $enqueteDeCollecte
    */
   public function __construct(Enquete $enqueteDeCollecte)
   {
       parent::__construct($enqueteDeCollecte);
   }
}