<?php

namespace App\Http\Controllers;

use App\Http\Requests\collecteurBassin\StoreRequest;
use App\Http\Requests\collecteurBassin\UpdateRequest;
use Core\Services\Interfaces\SiteServiceInterface;

class SitesController extends Controller
{
    /**
     * @var service
     */
    private $collecteurBassinService;

    /**
     * Instantiate a new SiteController instance.
     * @param SiteServiceInterface $collecteurBassinServiceInterface
     */
    public function __construct(SiteServiceInterface $collecteurBassinServiceInterface)
    {
        $this->collecteurBassinService = $collecteurBassinServiceInterface;

    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function index()
    {
        return $this->collecteurBassinService->all();
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function store(StoreRequest $request)
    {
        return $this->collecteurBassinService->create($request->all());
    }

    /**
     * Display the specified resource.
     *
     * @param int $collecteurBassin
     * @return \Illuminate\Http\JsonResponse
     */
    public function show($idSite)
    {
        return $this->collecteurBassinService->findById($idSite);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param int $collecteurBassin
     * @return \Illuminate\Http\JsonResponse
     */
    public function update(UpdateRequest $request, $idSite)
    {
        return $this->collecteurBassinService->update($idSite, $request->all());
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param int $collecteurBassin
     * @return \Illuminate\Http\JsonResponse
     */
    public function destroy($idSite)
    {
        return $this->collecteurBassinService->deleteById($idSite);
    }
}
