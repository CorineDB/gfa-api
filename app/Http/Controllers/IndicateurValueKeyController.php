<?php

namespace App\Http\Controllers;

use App\Http\Requests\indicateur_value_keys\StoreRequest;
use App\Http\Requests\indicateur_value_keys\UpdateRequest;
use Core\Services\Interfaces\IndicateurValueKeyServiceInterface;

class IndicateurValueKeyController extends Controller
{
    /**
     * @var service
     */
    private $indicateurValueKey;

    /**
     * Instantiate a new IndicateurValueKeyController instance.
     * @param IndicateurValueKeyController $indicateurValueKeyInterface
     */
    public function __construct(IndicateurValueKeyServiceInterface $indicateurValueKeyInterface)
    {
        $this->middleware('permission:voir-une-cle-de-valeur-indicateur')->only(['show']);
        $this->middleware('permission:modifier-une-cle-de-valeur-indicateur')->only(['update']);
        $this->middleware('permission:creer-une-cle-de-valeur-indicateur')->only(['store']);
        $this->middleware('permission:supprimer-une-cle-de-valeur-indicateur')->only(['destroy']);

        $this->indicateurValueKey = $indicateurValueKeyInterface;
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        return $this->indicateurValueKey->all();
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(StoreRequest $request)
    {
        return $this->indicateurValueKey->create($request->all());
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\Activite  $paye
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        return $this->indicateurValueKey->findById($id);
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
        return $this->indicateurValueKey->update($id, $request->all());
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Activite  $paye
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        return $this->indicateurValueKey->deleteById($id);
    }
}
