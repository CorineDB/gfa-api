<?php

namespace App\Http\Controllers;

use App\Http\Requests\indicateur_cadre_logique\StoreRequest;
use App\Http\Requests\indicateur_cadre_logique\UpdateRequest;
use Core\Services\Interfaces\IndicateurCadreLogiqueServiceInterface;
use Illuminate\Validation\Rule;

class IndicateurCadreLogiqueController extends Controller
{
    /**
     * @var service
     */
    private $indicateurCadreLogiqueService;

    /**
     * Instantiate a new IndicateurController instance.
     * @param IndicateurCadreLogiqueServiceInterface $indicateurCadreLogiqueServiceInterface
     */
    public function __construct(IndicateurCadreLogiqueServiceInterface $indicateurCadreLogiqueServiceInterface)
    {
        $this->indicateurCadreLogiqueService = $indicateurCadreLogiqueServiceInterface;

    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function index()
    {
        return $this->indicateurCadreLogiqueService->all();
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function store(StoreRequest $request)
    {

        $validation_id = [
            "programme"             => Rule::exists('programmes', 'id')->whereNull('deleted_at'),
            "projet"                => Rule::exists('projets', 'id')->whereNull('deleted_at'),
            "resultat"              => Rule::exists('resultats', 'id')->whereNull('deleted_at'),
            "objectif_specifique"   => Rule::exists('objectif_specifiques', 'id')->whereNull('deleted_at')
        ];

        $request->validate([

            'indicatable_id'    => ['bail','required', $validation_id[$request->type]]

        ]);

        return $this->indicateurCadreLogiqueService->create($request->all());
    }

    /**
     * Display the specified resource.
     *
     * @param int $idIndicateur
     * @return \Illuminate\Http\JsonResponse
     */
    public function show($idIndicateur)
    {
        return $this->indicateurCadreLogiqueService->findById($idIndicateur);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param int $idIndicateur
     * @return \Illuminate\Http\JsonResponse
     */
    public function update(UpdateRequest $request, $idIndicateur)
    {
        return $this->indicateurCadreLogiqueService->update($idIndicateur, $request->all());
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param int $idIndicateur
     * @return \Illuminate\Http\JsonResponse
     */
    public function destroy($idIndicateur)
    {
        return $this->indicateurCadreLogiqueService->deleteById($idIndicateur);
    }
}
