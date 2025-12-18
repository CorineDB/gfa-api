<?php

namespace App\Http\Controllers;

use App\Http\Requests\objectifSpecifique\StoreRequest;
use App\Http\Requests\objectifSpecifique\UpdateRequest;
use Core\Services\Interfaces\ObjectifSpecifiqueServiceInterface;

class ObjectifSpecifiqueController extends Controller
{
    /**
     * @var service
     */
    private $objectifSpecifique;

    /**
     * Instantiate a new ObjectifSpecifiqueController instance.
     * @param ObjectifSpecifiqueServiceInterface $objectifSpecifiqueServiceInterface
     */
    public function __construct(ObjectifSpecifiqueServiceInterface $objectifSpecifiqueServiceInterface)
    {
        $this->objectifSpecifique = $objectifSpecifiqueServiceInterface;
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        return $this->objectifSpecifique->all();
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(StoreRequest $request)
    {
        return $this->objectifSpecifique->create($request->all());
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\Activite  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        return $this->objectifSpecifique->findById($id);
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
        return $this->objectifSpecifique->update($id, $request->all());
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Activite  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        return $this->objectifSpecifique->deleteById($id);
    }

}
