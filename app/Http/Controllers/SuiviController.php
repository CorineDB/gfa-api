<?php

namespace App\Http\Controllers;

use App\Http\Requests\suivi\StoreSuiviRequest;
use App\Http\Requests\suivi\StoreSuiviV2Request;
use App\Http\Requests\suivi\UpdateSuiviRequest;
use Core\Services\Interfaces\SuiviServiceInterface;
use Illuminate\Http\Request;

class SuiviController extends Controller
{
   /**
     * @var service
     */
    private $suiviService;

    /**
     * Instantiate a new ActiviteController instance.
     * @param SuiviServiceInterface $suiviServiceInterface
     */
    public function __construct(SuiviServiceInterface $suiviServiceInterface)
    {
        $this->middleware('permission:voir-un-suivi')->only(['index', 'show']);
        $this->middleware('permission:creer-un-suivi')->only(['store', 'suivisV2']);
        $this->middleware('permission:supprimer-un-suivi')->only(['destroy']);

        $this->suiviService = $suiviServiceInterface;
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        return $this->suiviService->all();
    }

    public function getSuivis(Request $request)
    {
        return $this->suiviService->getSuivis($request->all());
    }


    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(StoreSuiviRequest $request)
    {
        return $this->suiviService->create($request->all());
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function suivisV2(StoreSuiviV2Request $request)
    {
        return $this->suiviService->suiviV2($request->all());
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\Suivi  $suivi
     * @return \Illuminate\Http\Response
     */
    public function show($suivi)
    {
        return $this->suiviService->findById($suivi);
    }


    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Suivi  $suivi
     * @return \Illuminate\Http\Response
     */
    public function update(UpdateSuiviRequest $request, $suivi)
    {
        return $this->suiviService->update($suivi, $request->all());
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Suivi  $suivi
     * @return \Illuminate\Http\Response
     */
    public function destroy($suivi)
    {
        return $this->suiviService->deleteById($suivi);
    }
}
