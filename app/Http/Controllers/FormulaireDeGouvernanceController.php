<?php

namespace App\Http\Controllers;

use App\Http\Requests\formulaires_de_gouvernance\StoreRequest;
use App\Http\Requests\formulaires_de_gouvernance\UpdateRequest;
use Core\Services\Interfaces\FormulaireDeGouvernanceServiceInterface;

class FormulaireDeGouvernanceController extends Controller
{
    /**
     * @var service
     */
    private $formulaireDeGouvernanceService;

    /**
     * Instantiate a new FormulaireDeGouvernanceController instance.
     * @param FormulaireDeGouvernanceController $formulaireDeGouvernanceServiceInterface
     */
    public function __construct(FormulaireDeGouvernanceServiceInterface $formulaireDeGouvernanceServiceInterface)
    {
        $this->middleware('permission:voir-un-formulaire-de-gouvernance')->only(['index', 'show']);
        $this->middleware('permission:modifier-un-formulaire-de-gouvernance')->only(['update']);
        $this->middleware('permission:creer-un-formulaire-de-gouvernance')->only(['store']);
        $this->middleware('permission:supprimer-un-formulaire-de-gouvernance')->only(['destroy']);

        $this->formulaireDeGouvernanceService = $formulaireDeGouvernanceServiceInterface;
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        // Retrieve query parameters with defaults
        $columns = explode(',', request()->query('columns', '*'));

        $relations = request()->query('relations', null) ? explode(',', request()->query('relations')): []; // Default to no relations

        if(request()->query('filters')){
            $filters = request()->query('filters', []);
            return $this->formulaireDeGouvernanceService->allFiltredBy($filters, $columns, $relations);
        }
        return $this->formulaireDeGouvernanceService->all();
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(StoreRequest $request)
    {
        return $this->formulaireDeGouvernanceService->create($request->all());
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\Activite  $paye
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        return $this->formulaireDeGouvernanceService->findById($id);
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
        return $this->formulaireDeGouvernanceService->update($id, $request->all());
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Activite  $paye
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        return $this->formulaireDeGouvernanceService->deleteById($id);
    }
}
