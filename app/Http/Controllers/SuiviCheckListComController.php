<?php

namespace App\Http\Controllers;

use App\Http\Requests\suivi_check_list_com\StoreRequest;
use App\Http\Requests\suivi_check_list_com\UpdateRequest;
use Core\Services\Interfaces\SuiviCheckListComServiceInterface;

class SuiviCheckListComController extends Controller
{
    /**
     * @var service
     */
    private $suiviCheckListComService;

    /**
     * Instantiate a new SuiviCheckListComController instance.
     * @param CheckListComServiceInterface $suiviCheckListComServiceInterface
     */
    public function __construct(SuiviCheckListComServiceInterface $suiviCheckListComServiceInterface)
    {
        $this->suiviCheckListComService = $suiviCheckListComServiceInterface;

    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function index()
    {
        return $this->suiviCheckListComService->all();
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function store(StoreRequest $request)
    {
        return $this->suiviCheckListComService->create($request->all());
    }

    /**
     * Display the specified resource.
     *
     * @param int $checkListCom
     * @return \Illuminate\Http\JsonResponse
     */
    public function show($idSuiviCheckListCom)
    {
        return $this->suiviCheckListComService->findById($idSuiviCheckListCom);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param int $checkListCom
     * @return \Illuminate\Http\JsonResponse
     */
    public function update(UpdateRequest $request, $idSuiviCheckListCom)
    {
        return $this->suiviCheckListComService->update($idSuiviCheckListCom, $request->all());
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param int $checkListCom
     * @return \Illuminate\Http\JsonResponse
     */
    public function destroy($idSuiviCheckListCom)
    {
        return $this->suiviCheckListComService->deleteById($idSuiviCheckListCom);
    }
}
