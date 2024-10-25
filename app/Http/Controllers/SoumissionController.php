<?php

namespace App\Http\Controllers;

use App\Http\Requests\evaluations_de_gouvernance\SoumissionRequest;
use App\Http\Requests\soumissions\UpdateRequest;
use Core\Services\Interfaces\SourceDeVerificationServiceInterface;

class SoumissionController extends Controller
{
    /**
     * @var service
     */
    private $soumissionService;

    /**
     * Instantiate a new SoumissionController instance.
     * @param SoumissionController $soumissionServiceInterface
     */
    public function __construct(SourceDeVerificationServiceInterface $soumissionServiceInterface)
    {
        $this->soumissionService = $soumissionServiceInterface;
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index($evaluationId)
    {
        // Retrieve query parameters with defaults
        $columns = explode(',', request()->query('columns', '*'));

        $relations = request()->query('relations', null) ? explode(',', request()->query('relations')): []; // Default to no relations

        if(request()->query('filters')){
            $filters = request()->query('filters', []);
            return $this->soumissionService->allFiltredBy($filters, $columns, $relations);
        }

        return $this->soumissionService->all();
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(SoumissionRequest $request, $evaluationId)
    {
        return $this->soumissionService->create(array_merge(["evaluationDeGouvernanceId" => $evaluationId], $request->all()));
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\Activite  $paye
     * @return \Illuminate\Http\Response
     */
    public function show($evaluationId, $soumissionId)
    {
        return $this->soumissionService->findById($soumissionId);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Activite  $paye
     * @return \Illuminate\Http\Response
     */
    public function update(SoumissionRequest $request, $evaluationId, $soumissionId)
    {
        return $this->soumissionService->update($soumissionId, $request->all());
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Activite  $paye
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        return $this->soumissionService->deleteById($id);
    }
}
