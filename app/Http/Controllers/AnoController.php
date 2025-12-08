<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Requests\ano\StoreAnoRequest;
use App\Http\Requests\ano\UpdateAnoRequest;
use Core\Services\Interfaces\AnoServiceInterface;

class AnoController extends Controller
{
    /**
     * @var service
     */
    private $anoService;

    /**
     * Instantiate a new ActiviteController instance.
     * @param AnoServiceInterface $anoServiceInterface
     */
    public function __construct(AnoServiceInterface $anoServiceInterface)
    {
        $this->middleware('permission:voir-un-ano')->only(['index', 'show']);
        $this->middleware('permission:modifier-un-ano')->only(['update']);
        $this->middleware('permission:creer-un-ano')->only(['store']);
        $this->middleware('permission:supprimer-un-ano')->only(['destroy']);

        $this->anoService = $anoServiceInterface;
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        return $this->anoService->all();
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(StoreAnoRequest $request)
    {
        return $this->anoService->create($request->all());
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function rappel($id)
    {
        return $this->anoService->rappel($id);
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        return $this->anoService->findById($id);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(UpdateAnoRequest $request, $id)
    {
        return $this->anoService->update($id, $request->all());
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        return $this->anoService->deleteById($id);
    }

    public function reponses($id)
    {
        return $this->anoService->reponses($id);
    }
}
