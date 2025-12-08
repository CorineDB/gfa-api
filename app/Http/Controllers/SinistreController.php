<?php

namespace App\Http\Controllers;

use App\Http\Requests\sinistre\StoreRequest;
use App\Http\Requests\sinistre\UpdateRequest;
use App\Http\Requests\sinistre\ImportationRequest;
use Core\Services\Interfaces\SinistreServiceInterface;

class SinistreController extends Controller
{
    /**
     * @var service
     */
    private $sinistreService;

    /**
     * Instantiate a new SinistreController instance.
     * @param SinistreServiceInterface $sinistreServiceInterface
     */
    public function __construct(SinistreServiceInterface $sinistreServiceInterface)
    {
        $this->middleware('permission:voir-un-pap')->only(['index', 'show']);
        $this->middleware('permission:modifier-un-pap')->only(['update']);
        $this->middleware('permission:creer-un-pap')->only(['store']);
        $this->middleware('permission:supprimer-un-pap')->only(['destroy']);

        $this->sinistreService = $sinistreServiceInterface;
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        return $this->sinistreService->all();
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(StoreRequest $request)
    {
        return $this->sinistreService->create($request->all());
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\Activite  $sinistre
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        return $this->sinistreService->findById($id);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Activite  $sinistre
     * @return \Illuminate\Http\Response
     */
    public function update(UpdateRequest $request, $sinistre)
    {
        return $this->sinistreService->update($sinistre, $request->all());
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Activite  $sinistre
     * @return \Illuminate\Http\Response
     */
    public function destroy($sinistre)
    {
        return $this->sinistreService->deleteById($sinistre);
    }

    public function importation(ImportationRequest $request)
    {
        return $this->sinistreService->importation($request->all());
    }


}
