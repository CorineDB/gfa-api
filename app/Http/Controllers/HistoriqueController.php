<?php

namespace App\Http\Controllers;

use App\Models\Historique;
use Illuminate\Http\Request;
use App\Http\Requests\historique\StoreHistoriqueRequest;
use App\Http\Requests\historique\UpdateHistoriqueRequest;
use App\Http\Resources\HistoriquesResource;
use App\Models\LogActivity;
use Illuminate\Http\Response;

class HistoriqueController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function __construct()
    {
        $this->middleware('permission:voir-un-historique')->only(['index']);
    }
    public function index()
    {
        try {

            return response()->json(['statut' => 'success', 'message' => null, 'data' => HistoriquesResource::collection(LogActivity::orderBy('created_at', 'DESC')->get()), 'statutCode' => Response::HTTP_OK], Response::HTTP_OK);

        } catch (\Throwable $th) {
            //throw $th;
            return response()->json(['statut' => 'error', 'message' => $th->getMessage(), 'errors' => [], 'statutCode' => Response::HTTP_INTERNAL_SERVER_ERROR], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
}
