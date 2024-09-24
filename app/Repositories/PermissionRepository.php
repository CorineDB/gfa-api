<?php

namespace App\Repositories;

use App\Models\Permission;
use Core\Repositories\BaseRepository;

class PermissionRepository extends BaseRepository
{

   /**
    * PermissionRepository constructor.
    *
    * @param Permission $permission
    */
   public function __construct(Permission $permission)
   {
       parent::__construct($permission);
   }
}