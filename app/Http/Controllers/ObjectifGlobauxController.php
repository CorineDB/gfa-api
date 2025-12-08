<?php

namespace App\Http\Controllers;

use App\Http\Requests\objectifGlobaux\StoreRequest;
use App\Http\Requests\objectifGlobaux\UpdateRequest;
use Core\Services\Interfaces\ObjectifGlobauxServiceInterface;

class ObjectifGlobauxController extends Controller
{
    /**
     * @var service
     */
    private $objectifGlobaux;

    /**
     * Instantiate a new ObjectifGlobauxController instance.
     * @param ObjectifGlobauxServiceInterface $objectifGlobauxServiceInterface
     */
    public function __construct(ObjectifGlobauxServiceInterface $objectifGlobauxServiceInterface)
    {
        $this->objectifGlobaux = $objectifGlobauxServiceInterface;
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        return $this->objectifGlobaux->all();
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(StoreRequest $request)
    {
        return $this->objectifGlobaux->create($request->all());
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\Activite  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        return $this->objectifGlobaux->findById($id);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Activite  $id
     * @return \Illuminate\Http\Response
     */
    public function update(UpdateRequest $request, $id)
    {
        return $this->objectifGlobaux->update($id, $request->all());
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Activite  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        return $this->objectifGlobaux->deleteById($id);
    }

}
