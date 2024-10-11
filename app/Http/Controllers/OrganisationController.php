<?php

namespace App\Http\Controllers;

use App\Http\Requests\organisation\GenererRequest;
use App\Http\Requests\organisation\StoreRequest;
use App\Http\Requests\organisation\UpdateRequest;
use Core\Services\Interfaces\OrganisationServiceInterface;

class OrganisationController extends Controller
{
    /**
     * @var service
     */
    private $organisation;

    /**
     * Instantiate a new OrganisationController instance.
     * @param OrganisationServiceInterface $authServiceInterface
     */
    public function __construct(OrganisationServiceInterface $organisationInterface)
    {
        $this->middleware('permission:voir-un-organisation')->only(['index', 'show']);
        $this->middleware('permission:modifier-une-organisation')->only(['update']);
        $this->middleware('permission:creer-une-organisation')->only(['store']);
        $this->middleware('permission:supprimer-une-organisation')->only(['destroy']);

        $this->organisation = $organisationInterface;
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        return $this->organisation->all();
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(StoreRequest $request)
    {
        return $this->organisation->create($request->all());
    }

    public function generer(GenererRequest $request)
    {
        return $this->organisation->generer($request->all());
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $idOrganisation
     * @return \Illuminate\Http\Response
     */
    public function show($idOrganisation)
    {
        return $this->organisation->findById($idOrganisation);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int $idOrganisation
     * @return \Illuminate\Http\Response
     */
    public function update(UpdateRequest $request, $idOrganisation)
    {
        return $this->organisation->update($idOrganisation, $request->all());
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Organisation  $idOrganisation
     * @return \Illuminate\Http\Response
     */
    public function destroy($idOrganisation)
    {
        return $this->organisation->deleteById($idOrganisation);
    }

}
