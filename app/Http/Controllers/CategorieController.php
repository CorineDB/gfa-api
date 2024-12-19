<?php

namespace App\Http\Controllers;

use App\Http\Requests\categorie\StoreRequest;
use App\Http\Requests\categorie\UpdateRequest;
use Core\Services\Interfaces\CategorieServiceInterface;

class CategorieController extends Controller
{
    /**
     * @var service
     */
    private $categorieService;

    /**
     * Instantiate a new CategorieController instance.
     * @param CategorieServiceInterface $categorieServiceInterface
     */
    public function __construct(CategorieServiceInterface $categorieServiceInterface)
    {
        $this->middleware('permission:voir-une-categorie')->only(['index', 'show']);
        $this->middleware('permission:modifier-une-categorie')->only(['update']);
        $this->middleware('permission:creer-une-categorie')->only(['store']);
        $this->middleware('permission:supprimer-une-categorie')->only(['destroy']);

        $this->categorieService = $categorieServiceInterface;
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function index()
    {
        return $this->categorieService->all();
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function store(StoreRequest $request)
    {
        return $this->categorieService->create($request->all());
    }

    /**
     * Display the specified resource.
     *
     * @param int $categorie
     * @return \Illuminate\Http\JsonResponse
     */
    public function show($idCategorie)
    {
        return $this->categorieService->findById($idCategorie);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param int $categorie
     * @return \Illuminate\Http\JsonResponse
     */
    public function update(UpdateRequest $request, $idCategorie)
    {
        return $this->categorieService->update($idCategorie, $request->all());
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param int $categorie
     * @return \Illuminate\Http\JsonResponse
     */
    public function destroy($idCategorie)
    {
        return $this->categorieService->deleteById($idCategorie);
    }
}
