<?php

namespace App\Http\Controllers;

use App\Http\Requests\ong\StoreRequest;
use App\Http\Requests\ong\UpdateRequest;
use Core\Services\Interfaces\OngCommServiceInterface;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Auth;

class ONGController extends Controller
{
    /**
     * @var service
     */
    private $ongService;

    /**
     * Instantiate a new AuthController instance.
     * @param OngCommServiceInterface $authServiceInterface
     */
    public function __construct(OngCommServiceInterface $ongServiceInterface)
    {
        $this->middleware('permission:voir-une-ong')->only(['index', 'show']);
        $this->middleware('permission:modifier-une-ong')->only(['update']);
        $this->middleware('permission:creer-une-ong')->only(['store']);
        $this->middleware('permission:supprimer-une-ong')->only(['destroy']);

        $this->ongService = $ongServiceInterface;
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        return $this->ongService->ongs();
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

        $data["type"] = "ong";

        $acteur = Auth::check() ? Auth::user()->nom . " ". Auth::user()->prenom : "Inconnu";

        $message = Str::ucfirst($acteur) . " a créé le compte de l'ong ";

        $data["message"] = $message;

        return $this->ongService->create($data);
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $idOng
     * @return \Illuminate\Http\Response
     */
    public function show($idOng)
    {
        return $this->ongService->findById($idOng);
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

        $data ["type"] = "ong";

        $acteur = Auth::check() ? Auth::user()->nom . " ". Auth::user()->prenom : "Inconnu";

        $message = Str::ucfirst($acteur) . " a mis à jour le compte de l'ong ";

        $data["message"] = $message;

        return $this->ongService->update($idOng, $data);
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

        $message = Str::ucfirst($acteur) . " a supprimé le compte de l'ong ";

        return $this->ongService->deleteById($idOng, $message);
    }

}
