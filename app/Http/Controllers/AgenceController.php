<?php

namespace App\Http\Controllers;

use App\Http\Requests\agence\StoreRequest;
use App\Http\Requests\agence\UpdateRequest;
use Core\Services\Interfaces\OngCommServiceInterface;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

class AgenceController extends Controller
{
    /**
     * @var service
     */
    private $agenceService;

    /**
     * Instantiate a new AuthController instance.
     * @param OngCommServiceInterface $authServiceInterface
     */
    public function __construct(OngCommServiceInterface $agenceServiceInterface)
    {
        $this->middleware('permission:voir-une-agence')->only(['index', 'show']);
        $this->middleware('permission:modifier-une-agence')->only(['update']);
        $this->middleware('permission:creer-une-agence')->only(['store']);
        $this->middleware('permission:supprimer-une-agence')->only(['destroy']);

        $this->agenceService = $agenceServiceInterface;
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        return $this->agenceService->agences_communication();
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(StoreRequest $request)
    {
        $data = $request->all();

        $data ["type"] = "agence";

        $acteur = Auth::check() ? Auth::user()->nom . " ". Auth::user()->prenom : "Inconnu";

        $message = Str::ucfirst($acteur) . " a créé le compte de l'agence ";

        $data["message"] = $message;

        return $this->agenceService->create($data);
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $idOng
     * @return \Illuminate\Http\Response
     */
    public function show($idOng)
    {
        return $this->agenceService->findById($idOng);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int $idOng
     * @return \Illuminate\Http\Response
     */
    public function update(UpdateRequest $request, $idOng)
    {
        $data = $request->all();

        $data ["type"] = "agence";

        $acteur = Auth::check() ? Auth::user()->nom . " ". Auth::user()->prenom : "Inconnu";

        $message = Str::ucfirst($acteur) . " a mis à jour le compte de l'agence ";

        $data["message"] = $message;

        return $this->agenceService->update($idOng, $data);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Ong  $idOng
     * @return \Illuminate\Http\Response
     */
    public function destroy($idOng)
    {
        $acteur = Auth::check() ? Auth::user()->nom . " ". Auth::user()->prenom : "Inconnu";

        $message = Str::ucfirst($acteur) . " a supprimé le compte de l'agence ";

        return $this->agenceService->deleteById($idOng, $message);
    }

}
