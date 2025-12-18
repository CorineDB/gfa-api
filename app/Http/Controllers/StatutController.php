<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Requests\statut\StatutStoreRequest;
use App\Http\Requests\statut\StatutUpdateRequest;
use Core\Services\Interfaces\StatutServiceInterface;

class StatutController extends Controller
{
    /**
     * @var service
     */
    private $statutService;

    /**
     * Instantiate a new StatutController instance.
     * @param StatutServiceInterface $statutServiceInterface
     */
    public function __construct(StatutServiceInterface $statutServiceInterface)
    {
        $this->statutService = $statutServiceInterface;
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        return $this->statutService->all();
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(StatutStoreRequest $request)
    {
        return $this->statutService->create($request->all());
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        return $this->statutService->findById($id);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(StatutUpdateRequest $request, $id)
    {
        return $this->statutService->update($id, $request->all());
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        return $this->statutService->deleteById($id);
    }
}
