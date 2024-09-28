<?php

namespace App\Http\Controllers;

use App\Http\Requests\typeano\StoreRequest;
use App\Http\Requests\typeano\UpdateRequest;
use Core\Services\Interfaces\TypeAnoServiceInterface;

class TypeAnoController extends Controller
{
    /**
     * @var service
     */
    private $typeAnoService;

    /**
     * Instantiate a new TypeAnoController instance.
     * @param TypeAnoServiceInterface $typeAnoServiceInterface
     */
    public function __construct(TypeAnoServiceInterface $typeAnoServiceInterface)
    {
        $this->typeAnoService = $typeAnoServiceInterface;
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        return $this->typeAnoService->all();
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(StoreRequest $request)
    {
        return $this->typeAnoService->create($request->all());
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\Activite  $paye
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        return $this->typeAnoService->findById($id);
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
        return $this->typeAnoService->update($id, $request->all());
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Activite  $paye
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        return $this->typeAnoService->deleteById($id);
    }
}
