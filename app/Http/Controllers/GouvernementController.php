<?php

namespace App\Http\Controllers;

use App\Http\Requests\gouvernement\StoreRequest;
use App\Http\Requests\gouvernement\UpdateRequest;
use Core\Services\Interfaces\GouvernementServiceInterface;
use Illuminate\Http\Request;

class GouvernementController extends Controller
{
    /**
     * @var service
     */
    private $gouvernementService;

    /**
     * Instantiate a new GouvernementController instance.
     * @param GouvernementServiceInterface $authServiceInterface
     */
    public function __construct(GouvernementServiceInterface $gouvernementServiceInterface)
    {
        $this->middleware('permission:voir-un-gouvernement')->only(['index', 'show']);
        $this->middleware('permission:modifier-un-gouvernement')->only(['update']);
        $this->middleware('permission:creer-un-gouvernement')->only(['store']);
        $this->middleware('permission:supprimer-un-gouvernement')->only(['destroy']);

        $this->gouvernementService = $gouvernementServiceInterface;
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        return $this->gouvernementService->all();
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(StoreRequest $request)
    {
        return $this->gouvernementService->create($request->all());
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $idGouvernement
     * @return \Illuminate\Http\Response
     */
    public function show($idGouvernement)
    {
        return $this->gouvernementService->findById($idGouvernement);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int $idGouvernement
     * @return \Illuminate\Http\Response
     */
    public function update(UpdateRequest $request, $idGouvernement)
    {
        return $this->gouvernementService->update($idGouvernement, $request->all());
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Gouvernement  $idGouvernement
     * @return \Illuminate\Http\Response
     */
    public function destroy($idGouvernement)
    {
        return $this->gouvernementService->deleteById($idGouvernement);
    }

}
