<?php

namespace App\Http\Controllers;

use App\Http\Requests\role\StoreRequest;
use App\Http\Requests\role\UpdateRequest;
use Core\Services\Interfaces\RoleServiceInterface;

class RoleController extends Controller
{
    /**
     * @var service
     */
    private $roleService;

    /**
     * Instantiate a new RoleController instance.
     * @param RoleServiceInterface $roleServiceInterface
     */
    public function __construct(RoleServiceInterface $roleServiceInterface)
    {
        $this->middleware('permission:voir-un-role')->only(['index', 'show']);
        $this->middleware('permission:modifier-un-role')->only(['update']);
        $this->middleware('permission:creer-un-role')->only(['store']);
        $this->middleware('permission:supprimer-un-role')->only(['destroy']);
        
        $this->roleService = $roleServiceInterface;

    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function index()
    {
        return $this->roleService->all();
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function store(StoreRequest $request)
    {
        return $this->roleService->create($request->all());
    }

    /**
     * Display the specified resource.
     *
     * @param int $role
     * @return \Illuminate\Http\JsonResponse
     */
    public function show($idRole)
    {
        return $this->roleService->findById($idRole);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param int $role
     * @return \Illuminate\Http\JsonResponse
     */
    public function update(UpdateRequest $request, $idRole)
    {
        return $this->roleService->update($idRole, $request->all());
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param int $role
     * @return \Illuminate\Http\JsonResponse
     */
    public function destroy($idRole)
    {
        return $this->roleService->deleteById($idRole);
    }
}
