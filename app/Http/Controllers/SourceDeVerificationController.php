<?php

namespace App\Http\Controllers;

use App\Http\Requests\sources_de_verification\StoreRequest;
use App\Http\Requests\sources_de_verification\UpdateRequest;
use Core\Services\Interfaces\SourceDeVerificationServiceInterface;

class SourceDeVerificationController extends Controller
{
    /**
     * @var service
     */
    private $sourceDeVerificationService;

    /**
     * Instantiate a new SourceDeVerificationController instance.
     * @param SourceDeVerificationController $sourceDeVerificationServiceInterface
     */
    public function __construct(SourceDeVerificationServiceInterface $sourceDeVerificationServiceInterface)
    {
        $this->middleware('permission:voir-une-source-de-verification')->only(['show']);
        $this->middleware('permission:modifier-une-source-de-verification')->only(['update']);
        $this->middleware('permission:creer-une-source-de-verification')->only(['store']);
        $this->middleware('permission:supprimer-une-source-de-verification')->only(['destroy']);
        $this->sourceDeVerificationService = $sourceDeVerificationServiceInterface;
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        return $this->sourceDeVerificationService->all();
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(StoreRequest $request)
    {
        return $this->sourceDeVerificationService->create($request->all());
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\Activite  $paye
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        throw new \Exception("Error Processing Request " . $id, 1);
        return $this->sourceDeVerificationService->findById($id);
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
        return $this->sourceDeVerificationService->update($id, $request->all());
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Activite  $paye
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        return $this->sourceDeVerificationService->deleteById($id);
    }
}
