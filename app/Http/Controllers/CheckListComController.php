<?php

namespace App\Http\Controllers;

use App\Http\Requests\check_list_com\StoreRequest;
use App\Http\Requests\check_list_com\UpdateRequest;
use Core\Services\Interfaces\CheckListComServiceInterface;

class CheckListComController extends Controller
{
    /**
     * @var service
     */
    private $checkListComService;

    /**
     * Instantiate a new CheckListComController instance.
     * @param CheckListComServiceInterface $checkListComServiceInterface
     */
    public function __construct(CheckListComServiceInterface $checkListComServiceInterface)
    {
        $this->checkListComService = $checkListComServiceInterface;

    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function index()
    {
        return $this->checkListComService->all();
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function store(StoreRequest $request)
    {
        return $this->checkListComService->create($request->all());
    }

    /**
     * Display the specified resource.
     *
     * @param int $checkListCom
     * @return \Illuminate\Http\JsonResponse
     */
    public function show($idCheckListCom)
    {
        return $this->checkListComService->findById($idCheckListCom);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param int $checkListCom
     * @return \Illuminate\Http\JsonResponse
     */
    public function update(UpdateRequest $request, $idCheckListCom)
    {
        return $this->checkListComService->update($idCheckListCom, $request->all());
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param int $checkListCom
     * @return \Illuminate\Http\JsonResponse
     */
    public function destroy($idCheckListCom)
    {
        return $this->checkListComService->deleteById($idCheckListCom);
    }
}
