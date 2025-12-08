<?php

namespace App\Http\Controllers;

use App\Http\Requests\actions_a_mener\StoreRequest;
use App\Http\Requests\actions_a_mener\UpdateRequest;
use App\Http\Requests\actions_a_mener\ActionAMenerTerminerRequest;
use App\Http\Requests\actions_a_mener\ValiderActionAMenerRequest;
use Core\Services\Interfaces\ActionAMenerServiceInterface;

class ActionAMenerController extends Controller
{
    /**
     * @var service
     */
    private $actionAMenerService;

    /**
     * Instantiate a new ActionAMenerController instance.
     * @param ActionAMenerController $actionAMenerServiceInterface
     */
    public function __construct(ActionAMenerServiceInterface $actionAMenerServiceInterface)
    {
        $this->middleware('permission:voir-une-action-a-mener')->only(['index', 'show']);
        $this->middleware('permission:modifier-une-action-a-mener')->only(['update']);
        $this->middleware('permission:creer-une-action-a-mener')->only(['store']);
        $this->middleware('permission:creer-une-action-a-mener')->only(['destroy']);
        $this->middleware('permission:signaler-une-action-a-mener-est-realise')->only(['notifierActionAMenerEstTerminer']);
        $this->middleware('permission:valider-une-action-a-mener')->only(['valider']);
        $this->actionAMenerService = $actionAMenerServiceInterface;
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        return $this->actionAMenerService->all();
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(StoreRequest $request)
    {
        return $this->actionAMenerService->create($request->all());
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\Activite  $paye
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        return $this->actionAMenerService->findById($id);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Activite  $paye
     * @return \Illuminate\Http\Response
     */
    public function update(UpdateRequest $request, $id)
    {
        return $this->actionAMenerService->update($id, $request->all());
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Activite  $paye
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        return $this->actionAMenerService->deleteById($id);
    }

    /**
     *
     */
    public function notifierActionAMenerEstTerminer(ActionAMenerTerminerRequest $request, $id)
    {
        return $this->actionAMenerService->notifierActionAMenerEstTerminer($id, $request->all());
    }

    /**
     *
     */
    public function valider(ValiderActionAMenerRequest $request, $id)
    {
        return $this->actionAMenerService->valider($id, $request->all());
    }
}
