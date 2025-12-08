<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Requests\eactivite\StoreRequest;
use App\Http\Requests\eactivite\UpdateRequest;
use Core\Services\Interfaces\EActiviteServiceInterface;
use App\Http\Requests\duree\StoreDureeRequest;
use App\Http\Requests\duree\UpdateDureeRequest;


class EActiviteController extends Controller
{
    /**
     * @var service
     */
    private $eActiviteService;

    /**
     * Instantiate a new EActiviteController instance.
     * @param EActiviteServiceInterface $eActiviteServiceInterface
     */
    public function __construct(EActiviteServiceInterface $eActiviteServiceInterface)
    {
        $this->middleware('permission:voir-une-activite-environnementale')->only(['index', 'show']);
        $this->middleware('permission:modifier-une-activite-environnementale')->only(['update']);
        $this->middleware('permission:creer-une-activite-environnementale')->only(['store']);
        $this->middleware('permission:supprimer-une-activite-environnementale')->only(['destroy']);

        $this->eActiviteService = $eActiviteServiceInterface;
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        return $this->eActiviteService->all();
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(StoreRequest $request)
    {
        return $this->eActiviteService->create($request->all());
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\Activite  $activite
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        return $this->eActiviteService->findById($id);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Activite  $activite
     * @return \Illuminate\Http\Response
     */
    public function update(UpdateRequest $request, $activite)
    {
        return $this->eActiviteService->update($activite, $request->all());
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Activite  $activite
     * @return \Illuminate\Http\Response
     */
    public function destroy($activite)
    {
        return $this->eActiviteService->deleteById($activite);
    }

    public function checkLists($id)
    {
        return $this->eActiviteService->checkLists($id);
    }

    public function ajouterDuree(StoreDureeRequest $request, $id)
    {
        return $this->eActiviteService->ajouterDuree($request->all(), );
    }

    public function modifierDuree(UpdateDureeRequest $request, $dureeId)
    {
        return $this->eActiviteService->modifierDuree($request->all(), $dureeId);
    }
}
