<?php

namespace App\Repositories;

use App\Models\Role;
use Core\Repositories\BaseRepository;

class RoleRepository extends BaseRepository
{

   /**
    * RoleRepository constructor.
    *
    * @param Role $role
    */
   public function __construct(Role $role)
   {
       parent::__construct($role);
   }
}