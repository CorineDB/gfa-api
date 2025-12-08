<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Requests\audit\StoreRequest;
use App\Http\Requests\audit\UpdateRequest;
use Core\Services\Interfaces\AuditServiceInterface;

class AuditController extends Controller
{
    /**
     * @var service
     */
    private $auditService;

    /**
     * Instantiate a new ActiviteController instance.
     * @param AuditServiceInterface $auditServiceInterface
     */
    public function __construct(AuditServiceInterface $auditServiceInterface)
    {
        $this->middleware('permission:voir-un-audit')->only(['index', 'show']);
        $this->middleware('permission:modifier-un-audit')->only(['update']);
        $this->middleware('permission:creer-un-audit')->only(['store']);
        $this->middleware('permission:supprimer-un-audit')->only(['destroy']);

        $this->auditService = $auditServiceInterface;
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        return $this->auditService->all();
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(StoreRequest $request)
    {
        return $this->auditService->create($request->all());
    }


    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        return $this->auditService->findById($id);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(UpdateRequest $request, $id)
    {
        return $this->auditService->update($id, $request->all());
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        return $this->auditService->deleteById($id);
    }

}
