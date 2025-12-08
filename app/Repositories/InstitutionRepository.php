<?php

namespace App\Repositories;

use App\Models\User;
use Core\Repositories\BaseRepository;

class InstitutionRepository extends BaseRepository
{

   /**
    * InstitutionRepository constructor.
    *
    * @param User $institution
    */
   public function __construct(User $institution)
   {
       parent::__construct($institution);
   }
}