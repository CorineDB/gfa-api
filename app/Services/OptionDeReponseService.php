<?php

namespace App\Services;

use App\Repositories\OptionDeReponseRepository;
use Core\Services\Contracts\BaseService;
use Core\Services\Interfaces\OptionDeReponseServiceInterface;

/**
* Interface OptionDeReponseServiceInterface
* @package Core\Services\Interfaces
*/
class OptionDeReponseService extends BaseService implements OptionDeReponseServiceInterface
{

    /**
     * @var service
     */
    protected $repository;

    /**
     * OptionDeReponseRepository constructor.
     *
     * @param OptionDeReponseRepository $optionDeReponseRepository
     */
    public function __construct(OptionDeReponseRepository $optionDeReponseRepository)
    {
        parent::__construct($optionDeReponseRepository);
    }

}