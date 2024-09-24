<?php

namespace App\Http\Controllers;

use App\Http\Requests\resultat\StoreRequest;
use App\Http\Requests\resultat\UpdateRequest;
use Core\Services\Interfaces\ResultatServiceInterface;

class ResultatController extends Controller
{
    /**
     * @var service
     */
    private $resultat;

    /**
     * Instantiate a new ResultatController instance.
     * @param ResultatServiceInterface $resultatServiceInterface
     */
    public function __construct(ResultatServiceInterface $resultatServiceInterface)
    {
        $this->resultat = $resultatServiceInterface;
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        return $this->resultat->all();
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(StoreRequest $request)
    {
        return $this->resultat->create($request->all());
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\Activite  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        return $this->resultat->findById($id);
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
        return $this->resultat->update($id, $request->all());
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Activite  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        return $this->resultat->deleteById($id);
    }

}
