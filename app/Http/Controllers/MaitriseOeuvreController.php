<?php

namespace App\Http\Controllers;

use App\Http\Requests\maitriseOeuvre\StoreRequest;
use App\Http\Requests\maitriseOeuvre\UpdateRequest;
use Core\Services\Interfaces\MaitriseOeuvreServiceInterface;

class MaitriseOeuvreController extends Controller
{
    /**
     * @var service
     */
    private $maitriseOeuvreService;

    /**
     * Instantiate a new MaitriseOeuvreController instance.
     * @param MaitriseOeuvreServiceInterface $maitriseOeuvreServiceInterface
     */
    public function __construct(MaitriseOeuvreServiceInterface $maitriseOeuvreServiceInterface)
    {
        $this->middleware('permission:voir-une-maitrise-oeuvre')->only(['index', 'show']);
        $this->middleware('permission:modifier-une-maitrise-oeuvre')->only(['update']);
        $this->middleware('permission:creer-une-maitrise-oeuvre')->only(['store']);
        $this->middleware('permission:supprimer-une-maitrise-oeuvre')->only(['destroy']);

        $this->maitriseOeuvreService = $maitriseOeuvreServiceInterface;

    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function index()
    {
        return $this->maitriseOeuvreService->all();
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function store(StoreRequest $request)
    {
        return $this->maitriseOeuvreService->create($request->all());
    }

    /**
     * Display the specified resource.
     *
     * @param int $maitriseOeuvre
     * @return \Illuminate\Http\JsonResponse
     */
    public function show($idMaitriseOeuvre)
    {
        return $this->maitriseOeuvreService->findById($idMaitriseOeuvre);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param int $maitriseOeuvre
     * @return \Illuminate\Http\JsonResponse
     */
    public function update(UpdateRequest $request, $idMaitriseOeuvre)
    {
        return $this->maitriseOeuvreService->update($idMaitriseOeuvre, $request->all());
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param int $maitriseOeuvre
     * @return \Illuminate\Http\JsonResponse
     */
    public function destroy($idMaitriseOeuvre)
    {
        return $this->maitriseOeuvreService->deleteById($idMaitriseOeuvre);
    }
}
