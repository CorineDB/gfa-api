<?php

namespace App\Http\Controllers;

use App\Http\Requests\paye\StoreRequest;
use App\Http\Requests\paye\UpdateRequest;
use Core\Services\Interfaces\PayeServiceInterface;

class PayeController extends Controller
{
    /**
     * @var service
     */
    private $payeService;

    /**
     * Instantiate a new PayeController instance.
     * @param PayeServiceInterface $payeServiceInterface
     */
    public function __construct(PayeServiceInterface $payeServiceInterface)
    {
        $this->payeService = $payeServiceInterface;
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        return $this->payeService->all();
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(StoreRequest $request)
    {
        return $this->payeService->create($request->all());
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\Activite  $paye
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        return $this->payeService->findById($id);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Activite  $paye
     * @return \Illuminate\Http\Response
     */
    public function update(UpdateRequest $request, $paye)
    {
        return $this->payeService->update($paye, $request->all());
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Activite  $paye
     * @return \Illuminate\Http\Response
     */
    public function destroy($paye)
    {
        return $this->payeService->deleteById($paye);
    }

}
