<?php

namespace App\Http\Controllers;

use App\Models\Permission;
use Illuminate\Http\Request;
use App\Http\Requests\permission\StorePermissionRequest;
use App\Http\Requests\permission\StoreRequest;
use App\Http\Requests\permission\UpdatePermissionRequest;
use App\Http\Requests\permission\UpdateRequest;
use Core\Services\Interfaces\PermissionServiceInterface;

class PermissionController extends Controller
{
    /**
     * @var service
     */
    private $permissionService;

    /**
     * Instantiate a new PermissionController instance.
     * @param PermissionServiceInterface $permissionServiceInterface
     */
    public function __construct(PermissionServiceInterface $permissionServiceInterface)
    {
        $this->permissionService = $permissionServiceInterface;

    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function index()
    {
        return $this->permissionService->all();
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function store(StoreRequest $request)
    {
        return $this->permissionService->create($request->all());
    }

    /**
     * Display the specified resource.
     *
     * @param int $permission
     * @return \Illuminate\Http\JsonResponse
     */
    public function show($idPermission)
    {
        return $this->permissionService->findById($idPermission);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param int $permission
     * @return \Illuminate\Http\JsonResponse
     */
    public function update(UpdateRequest $request, $idPermission)
    {
        return $this->permissionService->update($idPermission, $request->all());
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param int $permission
     * @return \Illuminate\Http\JsonResponse
     */
    public function destroy($idPermission)
    {
        return $this->permissionService->deleteById($idPermission);
    }
}
