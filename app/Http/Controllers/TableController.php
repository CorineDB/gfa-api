<?php

namespace App\Http\Controllers;

use App\Http\Requests\table\TauxDecaissementRequest;
use Illuminate\Http\Request;
use Core\Services\Interfaces\TableServiceInterface;

class TableController extends Controller
{
    /**
     * @var service
     */
    private $TableService;

    /**
     * Instantiate a new StatutController instance.
     * @param TableServiceInterface $tableServiceInterface
     */
    public function __construct(TableServiceInterface $tableServiceInterface)
    {
        $this->TableService = $tableServiceInterface;
    }

    public function tauxDecaissement(TauxDecaissementRequest $request)
    {
        return $this->TableService->tauxDecaissement($request->all());
    }


}
