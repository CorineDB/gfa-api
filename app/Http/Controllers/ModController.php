<?php

namespace App\Http\Controllers;

use App\Http\Requests\mod\StoreRequest;
use App\Http\Requests\mod\UpdateRequest;
use Core\Services\Interfaces\ModServiceInterface;
use Illuminate\Http\Request;

class ModController extends Controller
{
    /**
     * @var service
     */
    private $modService;

    /**
     * Instantiate a new AuthController instance.
     * @param ModServiceInterface $authServiceInterface
     */
    public function __construct(ModServiceInterface $modServiceInterface)
    {
        $this->middleware('permission:voir-un-mod')->only(['index', 'show']);
        $this->middleware('permission:modifier-un-mod')->only(['update']);
        $this->middleware('permission:creer-un-mod')->only(['store']);
        $this->middleware('permission:supprimer-un-mod')->only(['destroy']);

        $this->modService = $modServiceInterface;
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        return $this->modService->all();
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(StoreRequest $request)
    {
        return $this->modService->create($request->all());
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $idMod
     * @return \Illuminate\Http\Response
     */
    public function show($idMod)
    {
        return $this->modService->findById($idMod);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int $idMod
     * @return \Illuminate\Http\Response
     */
    public function update(UpdateRequest $request, $idMod)
    {
        return $this->modService->update($idMod, $request->all());
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Mod  $idMod
     * @return \Illuminate\Http\Response
     */
    public function destroy($idMod)
    {
        return $this->modService->deleteById($idMod);
    }

}
