<?php

namespace App\Http\Controllers\enquetes_de_gouvernance;

use App\Http\Controllers\Controller;
use App\Http\Requests\enquetes_de_gouvernance\evaluation_de_gouvernance\soumissions_de_perception\SoumissionDePerceptionRequest;
use App\Http\Requests\enquetes_de_gouvernance\evaluation_de_gouvernance\soumissions_de_perception\SoumissionDePerceptionValidationRequest;
use Core\Services\Interfaces\enquetes_de_gouvernance\SoumissionDePerceptionServiceInterface;
use Illuminate\Http\Request;

class SoumissionDePerceptionController extends Controller
{
    /**
     * @var service
     */
    private $soumissionDePerceptionService;

    /**
     * Instantiate a new SoumissionDePerceptionController instance.
     * @param SoumissionDePerceptionController $soumissionDePerceptionServiceInterface
     */
    public function __construct(SoumissionDePerceptionServiceInterface $soumissionDePerceptionServiceInterface)
    {
        $this->middleware('permission:voir-une-soumission')->only(['index', 'show']);

        $this->soumissionDePerceptionService = $soumissionDePerceptionServiceInterface;
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request, $evaluationId)
    {
        // Retrieve query parameters with defaults
        $columns = explode(',', request()->query('columns', '*'));

        $relations = request()->query('relations', null) ? explode(',', request()->query('relations')): []; // Default to no relations

        // Initialize filters
        $filters = request()->query('filters', []);

        if(!is_string($evaluationId) && is_object($evaluationId))
        {
            $evaluationId = $evaluationId->id;
        }
        // If filters is not an array, create a new one
        $filters = ['evaluationId' => $evaluationId];
        $filters = [];

        // Check if filters are present
        if (!empty($filters)) {
            return $this->soumissionDePerceptionService->allFiltredBy($filters, $columns, $relations);
        }
        return $this->soumissionDePerceptionService->all();
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(SoumissionDePerceptionRequest $request, $evaluationId)
    {
        $atttributs = array_merge(["evaluationId" => $evaluationId->id], $request->all());

        return $this->soumissionDePerceptionService->create($atttributs);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function validated(SoumissionDePerceptionValidationRequest $request, $evaluationId)
    {
        dd($request->all());
        $atttributs = array_merge(["evaluationId" => $evaluationId->id, 'validation' => true], $request->all());

        return $this->soumissionDePerceptionService->create($atttributs);
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\Activite  $paye
     * @return \Illuminate\Http\Response
     */
    public function show($evaluationId, $soumissionId)
    {
        return $this->soumissionDePerceptionService->findById($soumissionId);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Activite  $paye
     * @return \Illuminate\Http\Response
     */
    public function destroy($evaluationId, $id)
    {
        return $this->soumissionDePerceptionService->deleteById($id);
    }
}
