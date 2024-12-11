<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Requests\pta\GenererRequest;
use App\Http\Requests\pta\FiltreRequest;
use Core\Services\Interfaces\PtaServiceInterface;


class PtaController extends Controller
{
    /**
     * @var service
     */
    private $ptaService;

    /**
     * Instantiate a new StatutController instance.
     * @param PtaServiceInterface $ptaServiceInterface
     */
    public function __construct(PtaServiceInterface $ptaServiceInterface)
    {
        $this->middleware('permission:voir-ptab')->only(['generer', 'filtre']);

        $this->ptaService = $ptaServiceInterface;
    }

    public function generer(GenererRequest $request)
    {
        return $this->ptaService->generer($request->all());
    }

    public function filtre(FiltreRequest $request)
    {
        return $this->ptaService->filtre($request->all());
    }
}
