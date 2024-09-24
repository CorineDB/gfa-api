<?php

namespace App\Http\Controllers;

use App\Http\Requests\entreprise_executant\StoreRequest;
use App\Http\Requests\entreprise_executant\UpdateRequest;
use Core\Services\Interfaces\EntrepriseExecutantServiceInterface;
use Illuminate\Http\Request;

class EntrepriseExecutantController extends Controller
{
    /**
     * @var service
     */
    private $entrepriseExecutant;

    /**
     * Instantiate a new EntrepriseExecutantController instance.
     * @param EntrepriseExecutantServiceInterface $authServiceInterface
     */
    public function __construct(EntrepriseExecutantServiceInterface $entrepriseExecutantInterface)
    {
        $this->middleware('permission:voir-une-entreprise-executante')->only(['index', 'show']);
        $this->middleware('permission:modifier-une-entreprise-executante')->only(['update']);
        $this->middleware('permission:creer-une-entreprise-executante')->only(['store']);
        $this->middleware('permission:supprimer-une-entreprise-executante')->only(['destroy']);

        $this->entrepriseExecutant = $entrepriseExecutantInterface;
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        return $this->entrepriseExecutant->all();
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(StoreRequest $request)
    {
        return $this->entrepriseExecutant->create($request->all());
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $idEntrepriseExecutant
     * @return \Illuminate\Http\Response
     */
    public function show($idEntrepriseExecutant)
    {
        return $this->entrepriseExecutant->findById($idEntrepriseExecutant);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int $idEntrepriseExecutant
     * @return \Illuminate\Http\Response
     */
    public function update(UpdateRequest $request, $idEntrepriseExecutant)
    {
        return $this->entrepriseExecutant->update($idEntrepriseExecutant, $request->all());
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\EntrepriseExecutant  $idEntrepriseExecutant
     * @return \Illuminate\Http\Response
     */
    public function destroy($idEntrepriseExecutant)
    {
        return $this->entrepriseExecutant->deleteById($idEntrepriseExecutant);
    }

    public function eActivites($idEntrepriseExecutant)
    {
        return $this->entrepriseExecutant->eActivites($idEntrepriseExecutant);
    }

}
