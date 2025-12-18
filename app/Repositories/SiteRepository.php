<?php

namespace App\Repositories;

use App\Models\Site;
use Core\Repositories\BaseRepository;

class SiteRepository extends BaseRepository
{

   /**
    * SiteRepository constructor.
    *
    * @param Site $site
    */
   public function __construct(Site $site)
   {
       parent::__construct($site);
   }
}
