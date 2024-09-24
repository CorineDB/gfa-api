<?php

namespace App\Http\Controllers;

use App\Http\Requests\reponseAnos\StoreRequest;
use App\Http\Requests\reponseAnos\UpdateRequest;
use Core\Services\Interfaces\ReponseAnoServiceInterface;
use Illuminate\Http\Request;

class ReponseAnoController extends Controller
{
    /**
     * @var service
     */
    private $reponseAnoService;

    /**
     * Instantiate a new ReponseAnoController instance.
     * @param ReponseAnoInterface $reponseAnoServiceInterface
     */
    public function __construct(ReponseAnoServiceInterface $reponseAnoServiceInterface)
    {
        $this->middleware('permission:voir-une-reponse-ano')->only(['index', 'show']);
        $this->middleware('permission:modifier-une-reponse-ano')->only(['update']);
        $this->middleware('permission:creer-une-reponse-ano')->only(['store']);
        $this->middleware('permission:supprimer-une-reponse-ano')->only(['destroy']);

        $this->reponseAnoService = $reponseAnoServiceInterface;

    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        return $this->reponseAnoService->all();
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(StoreRequest $request)
    {
        return $this->reponseAnoService->create($request->all());
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        return $this->reponseAnoService->findById($id);
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
        return $this->reponseAnoService->update($id, $request->all());
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        return $this->reponseAnoService->delete($id);
    }

}
