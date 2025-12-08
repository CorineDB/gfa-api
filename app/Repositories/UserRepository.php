<?php

namespace App\Repositories;

use App\Models\User;
use Core\Repositories\BaseRepository;

class UserRepository extends BaseRepository
{

   /**
    * UserRepository constructor.
    *
    * @param User $user
    */
   public function __construct(User $user)
   {
       parent::__construct($user);
   }
}