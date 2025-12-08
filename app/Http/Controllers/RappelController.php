<?php

namespace App\Http\Controllers;

use App\Http\Requests\rappel\StoreRequest;
use App\Http\Requests\rappel\UpdateRequest;
use Core\Services\Interfaces\RappelServiceInterface;

class RappelController extends Controller
{
    /**
     * @var service
     */
    private $rappelService;

    /**
     * Instantiate a new RappelController instance.
     * @param RappelServiceInterface $rappelServiceInterface
     */
    public function __construct(RappelServiceInterface $rappelServiceInterface)
    {
        $this->middleware('permission:voir-un-rappel')->only(['index', 'show']);
        $this->middleware('permission:modifier-un-rappel')->only(['update']);
        $this->middleware('permission:creer-un-rappel')->only(['store']);
        $this->middleware('permission:supprimer-un-rappel')->only(['destroy']);

        $this->rappelService = $rappelServiceInterface;
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        return $this->rappelService->all();
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(StoreRequest $request)
    {
        return $this->rappelService->create($request->all());
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\Activite  $rappel
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        return $this->rappelService->findById($id);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Activite  $rappel
     * @return \Illuminate\Http\Response
     */
    public function update(UpdateRequest $request, $rappel)
    {
        return $this->rappelService->update($rappel, $request->all());
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Activite  $rappel
     * @return \Illuminate\Http\Response
     */
    public function destroy($rappel)
    {
        return $this->rappelService->deleteById($rappel);
    }

}
