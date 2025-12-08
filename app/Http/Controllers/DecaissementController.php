<?php

namespace App\Http\Controllers;

use App\Http\Requests\decaissement\FiltreRequest;
use Illuminate\Http\Request;
use App\Http\Requests\decaissement\StoreRequest;
use App\Http\Requests\decaissement\UpdateRequest;
use Core\Services\Interfaces\DecaissementServiceInterface;

class DecaissementController extends Controller
{
    /**
     * @var service
     */
    private $decaissementService;

    /**
     * Instantiate a new DecaissementController instance.
     * @param DecaissementServiceInterface $decaissementServiceInterface
     */
    public function __construct(DecaissementServiceInterface $decaissementServiceInterface)
    {
        $this->middleware('permission:voir-un-decaissement')->only(['index', 'show']);
        $this->middleware('permission:modifier-un-decaissement')->only(['update']);
        $this->middleware('permission:creer-un-decaissement')->only(['store']);
        $this->middleware('permission:supprimer-un-decaissement')->only(['destroy']);

        $this->decaissementService = $decaissementServiceInterface;

    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        return $this->decaissementService->all();
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(StoreRequest $request)
    {
        return $this->decaissementService->create($request->all());
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        return $this->decaissementService->findById($id);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(UpdateRequest $request, $id)
    {
        return $this->decaissementService->update($id, $request->all());
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        return $this->decaissementService->deleteById($id);
    }

    public function filtre(FiltreRequest $request)
    {
        return $this->decaissementService->filtre($request->all());
    }
}
