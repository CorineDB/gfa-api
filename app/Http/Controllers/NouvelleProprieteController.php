<?php

namespace App\Http\Controllers;

use App\Http\Requests\nouvellepropriete\StoreRequest;
use App\Http\Requests\nouvellepropriete\UpdateRequest;
use Core\Services\Interfaces\NouvelleProprieteServiceInterface;

class NouvelleProprieteController extends Controller
{
    /**
     * @var service
     */
    private $nouvelleProprieteService;

    /**
     * Instantiate a new NouvelleProprieteController instance.
     * @param NouvelleProprieteServiceInterface $nouvelleProprieteServiceInterface
     */
    public function __construct(NouvelleProprieteServiceInterface $nouvelleProprieteServiceInterface)
    {
        $this->nouvelleProprieteService = $nouvelleProprieteServiceInterface;
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        return $this->nouvelleProprieteService->all();
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(StoreRequest $request)
    {
        return $this->nouvelleProprieteService->create($request->all());
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\Activite  $nouvellepropriete
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        return $this->nouvelleProprieteService->findById($id);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Activite  $nouvellepropriete
     * @return \Illuminate\Http\Response
     */
    public function update(UpdateRequest $request, $nouvellepropriete)
    {
        return $this->nouvelleProprieteService->update($nouvellepropriete, $request->all());
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Activite  $nouvellepropriete
     * @return \Illuminate\Http\Response
     */
    public function destroy($nouvellepropriete)
    {
        return $this->nouvelleProprieteService->deleteById($nouvellepropriete);
    }

}
