<?php

namespace App\Http\Controllers;

use App\Http\Requests\unitee_de_mesure\StoreRequest;
use App\Http\Requests\unitee_de_mesure\UpdateRequest;
use Core\Services\Interfaces\UniteeMesureServiceInterface;
use Illuminate\Http\Request;

class UniteeMesureController extends Controller
{
    /**
     * @var service
     */
    private $uniteeMesureService;

    /**
     * Instantiate a new UniteeMesureController instance.
     * @param UniteeMesureServiceInterface $uniteeMesureServiceInterface
     */
    public function __construct(UniteeMesureServiceInterface $uniteeMesureServiceInterface)
    {
        $this->middleware('permission:voir-une-unitee-de-mesure')->only(['show']);
        $this->middleware('permission:modifier-une-unitee-de-mesure')->only(['update']);
        $this->middleware('permission:creer-une-unitee-de-mesure')->only(['store']);
        $this->middleware('permission:supprimer-une-unitee-de-mesure')->only(['destroy']);
        $this->uniteeMesureService = $uniteeMesureServiceInterface;

    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function index()
    {
        return $this->uniteeMesureService->all();
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function store(StoreRequest $request)
    {
        return $this->uniteeMesureService->create($request->all());
    }

    /**
     * Display the specified resource.
     *
     * @param int $unitee
     * @return \Illuminate\Http\JsonResponse
     */
    public function show($idUnitee)
    {
        return $this->uniteeMesureService->findById($idUnitee);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param int $unitee
     * @return \Illuminate\Http\JsonResponse
     */
    public function update(UpdateRequest $request, $idUnitee)
    {
        return $this->uniteeMesureService->update($idUnitee, $request->all());
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param int $unitee
     * @return \Illuminate\Http\JsonResponse
     */
    public function destroy($idUnitee)
    {
        return $this->uniteeMesureService->deleteById($idUnitee);
    }

}
