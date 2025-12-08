<?php

declare(strict_types=1);

namespace App\Http\Controllers\enquetes_de_gouvernance;

use App\Http\Controllers\Controller;
use App\Http\Requests\enquetes_de_gouvernance\formulaires_de_gouvernance_de_perception\StoreRequest;
use App\Http\Requests\enquetes_de_gouvernance\formulaires_de_gouvernance_de_perception\UpdateRequest;
use Core\Services\Interfaces\enquetes_de_gouvernance\FormulaireDePerceptionDeGouvernanceServiceInterface;

class FormulaireDePerceptionDeGouvernanceController extends Controller
{
    /**
     * @var service
     */
    private $formulaireDePerceptionDeGouvernanceService;

    /**
     * Instantiate a new FormulaireDePerceptionDeGouvernanceController instance.
     * @param FormulaireDePerceptionDeGouvernanceServiceInterface $formulaireDePerceptionDeGouvernanceServiceInterface
     */
    public function __construct(FormulaireDePerceptionDeGouvernanceServiceInterface $formulaireDePerceptionDeGouvernanceServiceInterface)
    {
        $this->middleware('permission:voir-un-formulaire-de-gouvernance')->only(['index', 'show']);
        $this->middleware('permission:modifier-un-formulaire-de-gouvernance')->only(['update']);
        $this->middleware('permission:creer-un-formulaire-de-gouvernance')->only(['store']);
        $this->middleware('permission:supprimer-un-formulaire-de-gouvernance')->only(['destroy']);
        $this->formulaireDePerceptionDeGouvernanceService = $formulaireDePerceptionDeGouvernanceServiceInterface;
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        return $this->formulaireDePerceptionDeGouvernanceService->all();
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(StoreRequest $request)
    {
        return $this->formulaireDePerceptionDeGouvernanceService->create($request->all());
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\enquetes_de_gouvernance\PrincipeDeGouvernancePerception  $paye
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        return $this->formulaireDePerceptionDeGouvernanceService->findById($id);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\enquetes_de_gouvernance\PrincipeDeGouvernancePerception  $paye
     * @return \Illuminate\Http\Response
     */
    public function update(UpdateRequest $request, $id)
    {
        return $this->formulaireDePerceptionDeGouvernanceService->update($id, $request->all());
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\enquetes_de_gouvernance\PrincipeDeGouvernancePerception  $paye
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        return $this->formulaireDePerceptionDeGouvernanceService->deleteById($id);
    }
}
