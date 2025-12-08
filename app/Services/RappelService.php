<?php

namespace App\Services;

use App\Repositories\RappelRepository;
use App\Traits\Helpers\LogActivity;
use Core\Services\Contracts\BaseService;
use Core\Services\Interfaces\RappelServiceInterface;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;
use Exception;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

/**
* Interface RappelServiceInterface
* @package Core\Services\Interfaces
*/
class RappelService extends BaseService implements RappelServiceInterface
{

    /**
     * @var service
     */
    protected $repository;

    /**
     * RappelService constructor.
     *
     * @param RappelRepository $rappelRepository
     */
    public function __construct(RappelRepository $rappelRepository)
    {
        parent::__construct($rappelRepository);
        $this->repository = $rappelRepository;
    }

    public function create(array $attributs) : JsonResponse
    {
        DB::beginTransaction();

        try
        {
            $user = Auth::user();
            $attributs = array_merge($attributs, ['userId' => $user->id]);

            $rappel = $this->repository->create(array_merge($attributs, ['programmeId' => auth()->user()->programmeId]));

            $acteur = Auth::check() ? Auth::user()->nom . " ". Auth::user()->prenom : "Inconnu";

            $message = $message ?? Str::ucfirst($acteur) . " a créé un " . strtolower(class_basename($rappel));

            //LogActivity::addToLog("Enregistrement", $message, get_class($rappel), $rappel->id);

            DB::commit();
            return response()->json(['statut' => 'success', 'message' => null, 'data' => $rappel, 'statutCode' => Response::HTTP_OK], Response::HTTP_OK);
        }
        catch (\Throwable $th)
        {
            DB::rollback();
            return response()->json(['statut' => 'error', 'message' => $th->getMessage(), 'errors' => []], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    public function update($id, array $attributs) : JsonResponse
    {
        DB::beginTransaction();

        try
        {
            $user = Auth::user();
            $attributs = array_merge($attributs, ['userId' => $user->id]);

            $rappel = $this->repository->update($id, $attributs);

            $rappel = $this->repository->findById($id);

            $acteur = Auth::check() ? Auth::user()->nom . " ". Auth::user()->prenom : "Inconnu";

            $message = $message ?? Str::ucfirst($acteur) . " a modifié un " . strtolower(class_basename($rappel));

            //LogActivity::addToLog("Modification", $message, get_class($rappel), $rappel->id);

            DB::commit();
            return response()->json(['statut' => 'success', 'message' => null, 'data' => $rappel, 'statutCode' => Response::HTTP_OK], Response::HTTP_OK);
        }
        catch (\Throwable $th)
        {
            DB::rollback();
            return response()->json(['statut' => 'error', 'message' => $th->getMessage(), 'errors' => []], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
}
