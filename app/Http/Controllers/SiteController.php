<?php

namespace App\Http\Controllers;

use App\Http\Requests\site\StoreRequest;
use App\Http\Requests\site\UpdateRequest;
use Core\Services\Interfaces\SiteServiceInterface;
use Illuminate\Http\Request;

class SiteController extends Controller
{
    /**
     * @var service
     */
    private $siteService;

    /**
     * Instantiate a new SiteController instance.
     * @param SiteServiceInterface $siteServiceInterface
     */
    public function __construct(SiteServiceInterface $siteServiceInterface)
    {
        $this->middleware('role:unitee-de-gestion')->only(['store','update', 'destroy']);
        $this->middleware('permission:voir-un-site')->only(['index', 'show']);
        $this->middleware('permission:modifier-un-site')->only(['update']);
        $this->middleware('permission:creer-un-site')->only(['store']);
        $this->middleware('permission:supprimer-un-site')->only(['destroy']);

        $this->siteService = $siteServiceInterface;

    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function index()
    {
        return $this->siteService->all();
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function store(StoreRequest $request)
    {
        return $this->siteService->create($request->all());
    }

    /**
     * Display the specified resource.
     *
     * @param int $site
     * @return \Illuminate\Http\JsonResponse
     */
    public function show($idSite)
    {
        return $this->siteService->findById($idSite);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param int $site
     * @return \Illuminate\Http\JsonResponse
     */
    public function update(UpdateRequest $request, $idSite)
    {
        return $this->siteService->update($idSite, $request->all());
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param int $site
     * @return \Illuminate\Http\JsonResponse
     */
    public function destroy($idSite)
    {
        return $this->siteService->deleteById($idSite);
    }
}
