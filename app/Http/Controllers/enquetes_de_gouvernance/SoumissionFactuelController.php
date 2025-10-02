<?php

namespace App\Http\Controllers\enquetes_de_gouvernance;

use App\Http\Controllers\Controller;
use App\Http\Requests\enquetes_de_gouvernance\evaluation_de_gouvernance\soumissions_factuel\SoumissionFactuelRequest;
use App\Http\Requests\enquetes_de_gouvernance\evaluation_de_gouvernance\soumissions_factuel\SoumissionFactuelValidationRequest;
use Core\Services\Interfaces\enquetes_de_gouvernance\SoumissionFactuelServiceInterface;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class SoumissionFactuelController extends Controller
{
    /**
     * @var service
     */
    private $soumissionFactuelService;

    /**
     * Instantiate a new SoumissionFactuelController instance.
     * @param SoumissionFactuelController $soumissionFactuelServiceInterface
     */
    public function __construct(SoumissionFactuelServiceInterface $soumissionFactuelServiceInterface)
    {
        $this->middleware('permission:voir-une-soumission')->only(['index', 'show']);
        $this->middleware('permission:creer-une-soumission')->only(['store']);
        $this->middleware('permission:supprimer-une-soumission')->only(['destroy']);
        $this->middleware('permission:signaler-une-action-a-mener-est-realise')->only(['notifierActionAMenerEstTerminer']);
        $this->middleware('permission:valider-une-soumission')->only(['validated']);

        $this->soumissionFactuelService = $soumissionFactuelServiceInterface;
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
            return $this->soumissionFactuelService->allFiltredBy($filters, $columns, $relations);
        }
        return $this->soumissionFactuelService->all();
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(SoumissionFactuelRequest $request, $evaluationId)
    {
        $atttributs = array_merge(["evaluationId" => $evaluationId->id], $request->validated());

        return $this->soumissionFactuelService->create($atttributs);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function validated(SoumissionFactuelValidationRequest $request, $evaluationId)
    {
        $atttributs = array_merge(["evaluationId" => $evaluationId->id, 'validation' => true], $request->validated());

        return $this->soumissionFactuelService->create($atttributs);
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\Activite  $paye
     * @return \Illuminate\Http\Response
     */
    public function show($evaluationId, $soumissionId)
    {
        return $this->soumissionFactuelService->findById($soumissionId);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Activite  $paye
     * @return \Illuminate\Http\Response
     */
    public function destroy($evaluationId, $id)
    {
        return $this->soumissionFactuelService->deleteById($id);
    }

    /**
     * Supprimer une preuve de vérification
     *
     * @param string $preuveId
     * @return \Illuminate\Http\Response
     */
    public function deletePreuve($soumissionId, $preuveId)
    {
        return $this->soumissionFactuelService->deletePreuve($soumissionId, $preuveId);
    }
}
