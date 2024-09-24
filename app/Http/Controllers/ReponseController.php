<?php

namespace App\Http\Controllers;

use App\Http\Requests\reponse\StoreRequest;
use Illuminate\Http\Request;
use Core\Services\Interfaces\ReponseServiceInterface;

class ReponseController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    /**
     * Instantiate a new ReponseController instance.
     * @param ReponseServiceInterface $reponseServiceInterface
     */
    public function __construct(ReponseServiceInterface $reponseServiceInterface)
    {
        $this->reponseService = $reponseServiceInterface;

    }

    public function index()
    {
        return $this->reponseService->all();
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(StoreRequest $request)
    {
        return $this->reponseService->create($request->all());
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        return $this->reponseService->findById();
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        return $this->reponseService->update($id, $request->all());
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        return $this->reponseService->deleteById($id);
    }
}
