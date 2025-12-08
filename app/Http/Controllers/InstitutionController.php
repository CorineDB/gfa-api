<?php

namespace App\Http\Controllers;

use App\Http\Requests\entreprise_institution\StoreRequest;
use App\Http\Requests\entreprise_institution\UpdateRequest;
use Core\Services\Interfaces\InstitutionServiceInterface;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class InstitutionController extends Controller
{
    /**
     * @var service
     */
    private $institution;

    /**
     * Instantiate a new InstitutionController instance.
     * @param InstitutionServiceInterface $institutionInterface
     */
    public function __construct(InstitutionServiceInterface $institutionInterface)
    {
        $this->middleware('permission:voir-une-institution')->only(['index', 'show']);
        $this->middleware('permission:modifier-une-institution')->only(['update']);
        $this->middleware('permission:creer-une-institution')->only(['store']);
        $this->middleware('permission:supprimer-une-institution')->only(['destroy']);

        $this->institution = $institutionInterface;
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        return $this->institution->all();
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(StoreRequest $request)
    {
        return $this->institution->create($request->all());
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $idInstitution
     * @return \Illuminate\Http\Response
     */
    public function show($idInstitution)
    {
        return $this->institution->findById($idInstitution);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  UpdateRequest  $request
     * @param  int $idInstitution
     * @return \Illuminate\Http\Response
     */
    public function update(UpdateRequest $request, $idInstitution)
    {
        return $this->institution->update($idInstitution, $request->all());
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int $idInstitution
     * @return \Illuminate\Http\Response
     */
    public function destroy($idInstitution)
    {
        return $this->institution->deleteById($idInstitution);
    }

}
