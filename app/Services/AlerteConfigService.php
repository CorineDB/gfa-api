<?php

namespace App\Services;

use App\Repositories\AlerteConfigRepository;
use App\Traits\Helpers\LogActivity;
use Core\Services\Contracts\BaseService;
use Core\Services\Interfaces\AlerteConfigServiceInterface;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;
use Exception;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

/**
* Interface AlerteConfigServiceInterface
* @package Core\Services\Interfaces
*/
class AlerteConfigService extends BaseService implements AlerteConfigServiceInterface
{

    /**
     * @var service
     */
    protected $repository;

    /**
     * AlerteConfigService constructor.
     *
     * @param AlerteConfigRepository $alerteConfigRepository
     */
    public function __construct(AlerteConfigRepository $alerteConfigRepository)
    {
        parent::__construct($alerteConfigRepository);
        $this->repository = $alerteConfigRepository;
    }


    public function update($id, array $attributs) : JsonResponse
    {
        DB::beginTransaction();

        try
        {
            if(array_key_exists('module', $attributs)) unset($attributs['module']);


            $alerteConfig = $this->repository->update($id, $attributs);

            $alerteConfig = $this->repository->findById($id);

            $acteur = Auth::check() ? Auth::user()->nom . " ". Auth::user()->prenom : "Inconnu";

            $message = $message ?? Str::ucfirst($acteur) . " a modifié un " . strtolower(class_basename($alerteConfig));

            //LogActivity::addToLog("Modification", $message, get_class($alerteConfig), $alerteConfig->id);

            DB::commit();
            return response()->json(['statut' => 'success', 'message' => null, 'data' => $alerteConfig, 'statutCode' => Response::HTTP_OK], Response::HTTP_OK);
        }
        catch (\Throwable $th)
        {
            DB::rollback();
            return response()->json(['statut' => 'error', 'message' => $th->getMessage(), 'errors' => []], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
}
