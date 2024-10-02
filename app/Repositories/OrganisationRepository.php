<?php

namespace App\Repositories;

use App\Models\Organisation;
use Core\Repositories\BaseRepository;

class OrganisationRepository extends BaseRepository
{

   /**
    * OrganisationRepository constructor.
    *
    * @param Organisation $organisation
    */
   public function __construct(Organisation $organisation)
   {
       parent::__construct($organisation);
   }
}