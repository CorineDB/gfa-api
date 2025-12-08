<?php

namespace App\Services;

use App\Repositories\ObjectifGlobauxRepository;
use App\Repositories\ProgrammeRepository;
use App\Repositories\ProjetRepository;
use Core\Services\Contracts\BaseService;
use Core\Services\Interfaces\ObjectifGlobauxServiceInterface;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;
use Exception;


/**
* Interface ObjectifGlobauxServiceInterface
* @package Core\Services\Interfaces
*/
class ObjectifGlobauxService extends BaseService implements ObjectifGlobauxServiceInterface
{

    /**
     * @var service
     */
    protected $repository, $programmeRepository, $projetRepository;

    /**
     * objectifGlobauxService constructor.
     *
     * @param ObjectifGlobauxRepository $objectifGlobauxRepository
     */
    public function __construct(ObjectifGlobauxRepository $objectifGlobauxRepository,
                                ProgrammeRepository $programmeRepository,
                                ProjetRepository $projetRepository)
    {
        parent::__construct($objectifGlobauxRepository);
        $this->repository = $objectifGlobauxRepository;
        $this->programmeRepository = $programmeRepository;
        $this->projetRepository = $projetRepository;
    }

    public function create(array $attributs) : JsonResponse
    {
        DB::beginTransaction();

        try
        {
            if(!(array_key_exists('programmeId', $attributs)) &&
               !(array_key_exists('projetId', $attributs))) throw new Exception( "Aucune rubrique choisis pour l'objectif globaux", 500);

            if(array_key_exists('programmeId', $attributs))
            {
                $programme = $this->programmeRepository->findById($attributs['programmeId']);

                $objectif = $programme->objectifGlobauxes()->create($attributs);
            }

            else
            {
                $projet = $this->projetRepository->findById($attributs['projetId']);

                $objectif = $projet->objectifGlobauxes()->create($attributs);
            }

            DB::commit();
            return response()->json(['statut' => 'success', 'message' => null, 'data' => $objectif, 'statutCode' => Response::HTTP_OK], Response::HTTP_OK);
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

            if(!($objectif = $this->repository->findById($id))) throw new Exception( "Objectif globaux inconnue", 500);

            $objectif->nom = $attributs['nom'];
            $objectif->description = $attributs['description'];
            $objectif->indicateurId = $attributs['indicateurId'];
            $objectif->save();

            DB::commit();
            return response()->json(['statut' => 'success', 'message' => null, 'data' => $objectif, 'statutCode' => Response::HTTP_OK], Response::HTTP_OK);
        }
        catch (\Throwable $th)
        {
            DB::rollback();
            return response()->json(['statut' => 'error', 'message' => $th->getMessage(), 'errors' => []], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
}
