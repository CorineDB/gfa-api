<?php

namespace App\Http\Controllers;

use App\Http\Requests\fonds\StoreRequest;
use App\Http\Requests\fonds\UpdateRequest;
use Core\Services\Interfaces\FondServiceInterface;

class FondController extends Controller
{
    /**
     * @var service
     */
    private $fondService;

    /**
     * Instantiate a new FondController instance.
     * @param FondController $fondServiceInterface
     */
    public function __construct(FondServiceInterface $fondServiceInterface)
    {
        $this->middleware('permission:voir-un-fond')->only(['index', 'show']);
        $this->middleware('permission:modifier-un-fond')->only(['update']);
        $this->middleware('permission:creer-un-fond')->only(['store']);
        $this->middleware('permission:supprimer-un-fond')->only(['destroy']);
        
        $this->fondService = $fondServiceInterface;
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        return $this->fondService->all();
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(StoreRequest $request)
    {
        return $this->fondService->create($request->all());
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\Activite  $paye
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        return $this->fondService->findById($id);
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
        return $this->fondService->update($id, $request->all());
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Activite  $paye
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        return $this->fondService->deleteById($id);
    }
}
