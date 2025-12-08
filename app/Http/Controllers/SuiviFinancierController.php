<?php

namespace App\Http\Controllers;

use App\Http\Requests\suiviFinancier\FiltreRequest;
use App\Http\Requests\suiviFinancier\ImportationRequest;
use App\Models\SuiviFinancier;
use Illuminate\Http\Request;
use App\Http\Requests\suiviFinancier\StoreSuiviFinancierRequest;
use App\Http\Requests\suiviFinancier\UpdateSuiviFinancierRequest;
use Core\Services\Interfaces\SuiviFinancierServiceInterface;


class SuiviFinancierController extends Controller
{
    /**
     * @var service
     */
    private $suiviFinancierService;

    /**
     * Instantiate a new suiviFinancierService instance.
     * @param PlanDecaissmeentServiceInterface $suiviFinancierService
     */
    public function __construct(SuiviFinancierServiceInterface $suiviFinancierService)
    {
        $this->middleware('permission:voir-un-suivi-financier')->only(['index', 'show']);
        $this->middleware('permission:modifier-un-suivi-financier')->only(['update']);
        $this->middleware('permission:creer-un-suivi-financier')->only(['store']);
        $this->middleware('permission:supprimer-un-suivi-financier')->only(['destroy']);
        $this->middleware('permission:importer-un-suivi-financier')->only(['importation']);

        $this->suiviFinancierService = $suiviFinancierService;
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        return $this->suiviFinancierService->all();
    }


    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(StoreSuiviFinancierRequest $request)
    {
        return $this->suiviFinancierService->create($request->all());
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\SuiviFinancier  $suiviFinancier
     * @return \Illuminate\Http\Response
     */
    public function show(SuiviFinancier $suiviFinancier)
    {
        return $this->suiviFinancierService->findById($suiviFinancier);
    }


    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\SuiviFinancier  $suiviFinancier
     * @return \Illuminate\Http\Response
     */
    public function update(UpdateSuiviFinancierRequest $request, SuiviFinancier $suiviFinancier)
    {
        return $this->suiviFinancierService->update($suiviFinancier, $request->all());
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\SuiviFinancier  $suiviFinancier
     * @return \Illuminate\Http\Response
     */
    public function destroy($suiviFinancier)
    {
        return $this->suiviFinancierService->deleteById($suiviFinancier);
    }

    public function importation(ImportationRequest $request)
    {
        return $this->suiviFinancierService->importation($request->all());
    }

    public function filtre(FiltreRequest $request)
    {
        return $this->suiviFinancierService->filtre($request->all());
    }
    public function trismestreASsuivre(FiltreRequest $request)
    {
        return $this->suiviFinancierService->trismestreASsuivre($request->all());
    }
}
