<?php

namespace App\Http\Controllers;

use App\Http\Requests\checklist\StoreRequest;
use App\Http\Requests\checklist\UpdateRequest;
use Core\Services\Interfaces\CheckListServiceInterface;

class CheckListController extends Controller
{
    /**
     * @var service
     */
    private $checkListService;

    /**
     * Instantiate a new CheckListController instance.
     * @param CheckListServiceInterface $checkListServiceInterface
     */
    public function __construct(CheckListServiceInterface $checkListServiceInterface)
    {
        $this->checkListService = $checkListServiceInterface;

    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function index()
    {
        return $this->checkListService->all();
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function store(StoreRequest $request)
    {
        return $this->checkListService->create($request->all());
    }

    /**
     * Display the specified resource.
     *
     * @param int $checkListCom
     * @return \Illuminate\Http\JsonResponse
     */
    public function show($id)
    {
        return $this->checkListService->findById($id);
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
        return $this->checkListService->update($id, $request->all());
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param int $checkListCom
     * @return \Illuminate\Http\JsonResponse
     */
    public function destroy($id)
    {
        return $this->checkListService->deleteById($id);
    }
}
