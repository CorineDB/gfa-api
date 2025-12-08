<?php

namespace App\Http\Controllers;

use App\Http\Requests\propriete\StoreRequest;
use App\Http\Requests\propriete\UpdateRequest;
use Core\Services\Interfaces\ProprieteServiceInterface;

class ProprieteController extends Controller
{
    /**
     * @var service
     */
    private $proprieteService;

    /**
     * Instantiate a new ProprieteController instance.
     * @param ProprieteServiceInterface $proprieteServiceInterface
     */
    public function __construct(ProprieteServiceInterface $proprieteServiceInterface)
    {
        $this->proprieteService = $proprieteServiceInterface;
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        return $this->proprieteService->all();
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(StoreRequest $request)
    {
        return $this->proprieteService->create($request->all());
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\Activite  $propriete
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        return $this->proprieteService->findById($id);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Activite  $propriete
     * @return \Illuminate\Http\Response
     */
    public function update(UpdateRequest $request, $propriete)
    {
        return $this->proprieteService->update($propriete, $request->all());
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Activite  $propriete
     * @return \Illuminate\Http\Response
     */
    public function destroy($propriete)
    {
        return $this->proprieteService->deleteById($propriete);
    }

}
