<?php

namespace App\Services;

use App\Repositories\CategorieRepository;
use Core\Services\Contracts\BaseService;
use Core\Services\Interfaces\CategorieServiceInterface;

/**
* Interface CategorieServiceInterface
* @package Core\Services\Interfaces
*/
class CategorieService extends BaseService implements CategorieServiceInterface
{

    /**
     * @var service
     */
    protected $repository;

    /**
     * CategorieRepository constructor.
     *
     * @param CategorieRepository $categorieRepository
     */
    public function __construct(CategorieRepository $categorieRepository)
    {
        parent::__construct($categorieRepository);
    }

}