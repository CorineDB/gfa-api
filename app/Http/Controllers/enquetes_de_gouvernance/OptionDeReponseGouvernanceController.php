<?php

declare(strict_types=1);

namespace App\Http\Controllers\enquetes_de_gouvernance;

use App\Http\Controllers\Controller;
use App\Http\Requests\enquetes_de_gouvernance\options_de_reponse_gouvernance\StoreRequest;
use App\Http\Requests\enquetes_de_gouvernance\options_de_reponse_gouvernance\UpdateRequest;
use Core\Services\Interfaces\enquetes_de_gouvernance\OptionDeReponseGouvernanceServiceInterface;

class OptionDeReponseGouvernanceController extends Controller
{
    /**
     * @var service
     */
    private $optionDeReponseGouvernanceService;

    /**
     * Instantiate a new OptionDeReponseGouvernanceController instance.
     * @param OptionDeReponseGouvernanceServiceInterface $optionDeReponseGouvernanceServiceInterface
     */
    public function __construct(OptionDeReponseGouvernanceServiceInterface $optionDeReponseGouvernanceServiceInterface)
    {
        $this->middleware('permission:voir-une-option-de-reponse')->only(['index', 'show']);
        $this->middleware('permission:modifier-une-option-de-reponse')->only(['update']);
        $this->middleware('permission:creer-une-option-de-reponse')->only(['store']);
        $this->middleware('permission:supprimer-une-option-de-reponse')->only(['destroy']);

        $this->optionDeReponseGouvernanceService = $optionDeReponseGouvernanceServiceInterface;
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        return $this->optionDeReponseGouvernanceService->all();
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(StoreRequest $request)
    {
        return $this->optionDeReponseGouvernanceService->create($request->all());
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\enquetes_de_gouvernance\OptionDeReponseGouvernance  $paye
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        return $this->optionDeReponseGouvernanceService->findById($id);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\enquetes_de_gouvernance\OptionDeReponseGouvernance  $paye
     * @return \Illuminate\Http\Response
     */
    public function update(UpdateRequest $request, $id)
    {
        return $this->optionDeReponseGouvernanceService->update($id, $request->all());
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\enquetes_de_gouvernance\OptionDeReponseGouvernance  $paye
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        return $this->optionDeReponseGouvernanceService->deleteById($id);
    }


    /**
     * Liste des options de gouvernance factuel
     *
     * @return \Illuminate\Http\Response
     */
    public function options_factuel(){
        return $this->optionDeReponseGouvernanceService->options_factuel();
    }

    /**
     * Liste des options de gouvernance perception
     *
     * return JsonResponse
     */
    public function options_de_perception(){
        return $this->optionDeReponseGouvernanceService->options_de_perception();
    }
}
