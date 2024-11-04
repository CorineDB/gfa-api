<?php

namespace App\Http\Controllers;

use App\Http\Requests\fonds\StoreRequest;
use App\Http\Requests\fonds\UpdateRequest;
use Core\Services\Interfaces\FicheDeSyntheseServiceInterface;

class FicheDeSyntheseController extends Controller
{
    /**
     * @var service
     */
    private $ficheDeSyntheseService;

    /**
     * Instantiate a new FicheDeSyntheseController instance.
     * @param FicheDeSyntheseController $ficheDeSyntheseServiceInterface
     */
    public function __construct(FicheDeSyntheseServiceInterface $ficheDeSyntheseServiceInterface)
    {
        $this->ficheDeSyntheseService = $ficheDeSyntheseServiceInterface;
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        return $this->ficheDeSyntheseService->all();
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(StoreRequest $request)
    {
        return $this->ficheDeSyntheseService->create($request->all());
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\Activite  $paye
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        return $this->ficheDeSyntheseService->findById($id);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Activite  $paye
     * @return \Illuminate\Http\Response
     */
    public function update(UpdateRequest $request, $id)
    {
        return $this->ficheDeSyntheseService->update($id, $request->all());
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Activite  $paye
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        return $this->ficheDeSyntheseService->deleteById($id);
    }
}
