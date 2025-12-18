<?php

namespace App\Http\Controllers;

use App\Http\Requests\passation\StoreRequest;
use App\Http\Requests\passation\UpdateRequest;
use Core\Services\Interfaces\PassationServiceInterface;

class PassationController extends Controller
{
    /**
     * @var service
     */
    private $passationService;

    /**
     * Instantiate a new PassationController instance.
     * @param PassationServiceInterface $passationServiceInterface
     */
    public function __construct(PassationServiceInterface $passationServiceInterface)
    {
        $this->middleware('permission:voir-une-passation')->only(['index', 'show']);
        $this->middleware('permission:modifier-une-passation')->only(['update']);
        $this->middleware('permission:creer-une-passation')->only(['store']);
        $this->middleware('permission:supprimer-une-passation')->only(['destroy']);

        $this->passationService = $passationServiceInterface;

    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function index()
    {
        return $this->passationService->all();
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function store(StoreRequest $request)
    {
        return $this->passationService->create($request->all());
    }

    /**
     * Display the specified resource.
     *
     * @param int $checkListCom
     * @return \Illuminate\Http\JsonResponse
     */
    public function show($id)
    {
        return $this->passationService->findById($id);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param int $checkListCom
     * @return \Illuminate\Http\JsonResponse
     */
    public function update(UpdateRequest $request, $id)
    {
        return $this->passationService->update($id, $request->all());
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param int $checkListCom
     * @return \Illuminate\Http\JsonResponse
     */
    public function destroy($id)
    {
        return $this->passationService->deleteById($id);
    }
}
