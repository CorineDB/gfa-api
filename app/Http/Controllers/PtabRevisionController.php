<?php

namespace App\Http\Controllers;

use App\Http\Requests\PtabReviserRequest;
use App\Http\Requests\PtabRevisionRequest;
use App\Http\Requests\ValidatePtabScope;
use Core\Services\Interfaces\PtabScopeServiceInterface;
use Illuminate\Http\Request;

class PtabRevisionController extends Controller
{
    /**
     * @var service
     */
    private $ptabScopedService;

    /**
     * Instantiate a new PtabRevisionController instance.
     * @param PtaServiceInterface $ptabScopeServiceInterface
     */
    public function __construct(PtabScopeServiceInterface $ptabScopeServiceInterface)
    {
        $this->middleware('permission:voir-revision-ptab')->only(['getOldPtaReviser', 'getListVersionPtab', 'getPtabReviser']);

        $this->middleware('permission:faire-revision-ptab')->only(['reviserPtab']);
        $this->ptabScopedService = $ptabScopeServiceInterface;
    }

    /**
     * Fonction pour récupérer le ptab sous forme de tableau
     * @return JsonResponse
     */
    public function getOldPtaReviser(PtabReviserRequest $request)
    {
        return $this->ptabScopedService->getOldPtaReviser($request->all());
    }

    /**
     * Function de revision de ptab
     * @return JsonResponse
     */
    public function reviserPtab(PtabRevisionRequest $request)
    {
        return $this->ptabScopedService->reviserPtab($request->all());
    }

    /**
     * Récuperer la liste de tout les scopes
     * return JsonResponse
     */
    public function index(){
        return $this->ptabScopedService->all();
    }

    /**
     * Récuperer la liste des scopes d'un programme
     * @param string|int $programmeId
     * return JsonResponse
     */
    public function programmeScopes($programmeId){
        return $this->ptabScopedService->programmeScopes($programmeId);
    }

    /**
     * Get ptab (projets, composantes, sous-composantes, activites, taches) d'un scope donnée sous forme de liste distincte
     * @return JsonResponse
     */
    public function getPtabReviser(ValidatePtabScope $request){
        return $this->ptabScopedService->getPtabReviser($request->all());
    }

    /**
     * Get ptab (projets, composantes, sous-composantes, activites, taches) d'un scope donnée sous forme de liste distincte
     * @return JsonResponse
     */
    public function getListVersionPtab(){
        return $this->ptabScopedService->all();
    }

    
}
