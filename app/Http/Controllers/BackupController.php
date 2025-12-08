<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Storage;

class BackupController extends Controller
{
    public function __construct()
    {
        $this->middleware('permission:faire-un-backup')->only(['lancer']);
        $this->middleware('permission:liste-backup')->only(['listes']);
    }

    public function lancer()
    {
        try
        {
            Artisan::call('backup:run');
            //Artisan::queue('backup:run',['--queue' => 'default']);

            return response()->json(['statut' => 'success', 'message' => null, 'data' => 'Backup lancé', 'statutCode' => Response::HTTP_OK], Response::HTTP_OK);
        }
        catch (\Throwable $th)
        {
            return response()->json(['statut' => 'error', 'message' => $th->getMessage(), 'errors' => []], Response::HTTP_INTERNAL_SERVER_ERROR);
        }

    }

    public function listes()
    {
        try
        {
            return response()->json(['statut' => 'success', 'message' => null, 'data' => Storage::files('Laravel'), 'statutCode' => Response::HTTP_OK], Response::HTTP_OK);
        }
        catch (\Throwable $th)
        {
            return response()->json(['statut' => 'error', 'message' => $th->getMessage(), 'errors' => []], Response::HTTP_INTERNAL_SERVER_ERROR);
        }

    }
}
