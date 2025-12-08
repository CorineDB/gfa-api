<?php

namespace App\Http\Controllers;

use App\Http\Requests\suivi_financier_mod\StoreRequest;
use App\Http\Requests\suivi_financier_mod\UpdateRequest;
use Core\Services\Interfaces\SuiviFinancierMODServiceInterface;

class SuiviFinancierMODController extends Controller
{
    /**
     * @var service
     */
    private $suiviFinancierMODService;

    /**
     * Instantiate a new SuiviFinancierMODController instance.
     * @param SuiviFinancierMODServiceInterface $suiviFinancierMODServiceInterface
     */
    public function __construct(SuiviFinancierMODServiceInterface $suiviFinancierMODServiceInterface)
    {
        $this->suiviFinancierMODService = $suiviFinancierMODServiceInterface;

    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function index()
    {
        return $this->suiviFinancierMODService->all();
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function store(StoreRequest $request)
    {
        return $this->suiviFinancierMODService->create($request->all());
    }

    /**
     * Display the specified resource.
     *
     * @param int $idSuiviFinancierMODs
     * @return \Illuminate\Http\JsonResponse
     */
    public function show($idSuiviFinancierMOD)
    {
        return $this->suiviFinancierMODService->findById($idSuiviFinancierMOD);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param int $idSuiviFinancierMOD
     * @return \Illuminate\Http\JsonResponse
     */
    public function update(UpdateRequest $request, $idSuiviFinancierMOD)
    {
        return $this->suiviFinancierMODService->update($idSuiviFinancierMOD, $request->all());
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param int $idSuiviFinancierMOD
     * @return \Illuminate\Http\JsonResponse
     */
    public function destroy($idSuiviFinancierMOD)
    {
        return $this->suiviFinancierMODService->deleteById($idSuiviFinancierMOD);
    }
}
