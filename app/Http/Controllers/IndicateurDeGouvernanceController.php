<?php

namespace App\Http\Controllers;

use App\Http\Requests\indicateur_de_gouvernance\StoreRequest;
use App\Http\Requests\indicateur_de_gouvernance\UpdateRequest;
use App\Traits\Eloquents\FilterTrait;
use Core\Services\Interfaces\IndicateurDeGouvernanceServiceInterface;
use Illuminate\Http\Response;

class IndicateurDeGouvernanceController extends Controller
{
    use FilterTrait; // Use the trait
    
    /**
     * @var service
     */
    private $indicateurDeGouvernanceService;

    /**
     * Instantiate a new IndicateurDeGouvernanceController instance.
     * @param IndicateurDeGouvernanceServiceInterface $indicateurDeGouvernanceServiceInterface
     */
    public function __construct(IndicateurDeGouvernanceServiceInterface $indicateurDeGouvernanceServiceInterface)
    {
        $this->middleware('permission:voir-un-indicateur-de-gouvernance')->only(['index', 'show']);
        $this->middleware('permission:modifier-un-indicateur-de-gouvernance')->only(['update']);
        $this->middleware('permission:creer-un-indicateur-de-gouvernance')->only(['store']);
        $this->middleware('permission:supprimer-un-indicateur-de-gouvernance')->only(['destroy']);

        $this->indicateurDeGouvernanceService = $indicateurDeGouvernanceServiceInterface;
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
            return $this->indicateurDeGouvernanceService->allFiltredBy($filters, $columns, $relations);
        }

        return $this->indicateurDeGouvernanceService->all($columns, $relations);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(StoreRequest $request)
    {
        return $this->indicateurDeGouvernanceService->create($request->all());
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\Activite  $paye
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        return $this->indicateurDeGouvernanceService->findById($id);
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
        return $this->indicateurDeGouvernanceService->update($id, $request->all());
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Activite  $paye
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        return $this->indicateurDeGouvernanceService->deleteById($id);
    }

    /**
     * Charger la liste des reponses d'une enquete
     *
     * @param  String  $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function observations($id)
    {
        return $this->indicateurDeGouvernanceService->observations($id);
    }
}
