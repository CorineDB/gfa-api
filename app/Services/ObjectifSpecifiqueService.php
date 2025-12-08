<?php

namespace App\Services;

use App\Repositories\ObjectifSpecifiqueRepository;
use App\Repositories\ProgrammeRepository;
use App\Repositories\ProjetRepository;
use Core\Services\Contracts\BaseService;
use Core\Services\Interfaces\ObjectifSpecifiqueServiceInterface;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;
use Exception;


/**
* Interface ObjectifSpecifiqueServiceInterface
* @package Core\Services\Interfaces
*/
class ObjectifSpecifiqueService extends BaseService implements ObjectifSpecifiqueServiceInterface
{

    /**
     * @var service
     */
    protected $repository, $programmeRepository, $projetRepository;

    /**
     * objectifSpecifiqueService constructor.
     *
     * @param ObjectifSpecifiqueRepository $objectifSpecifiqueRepository
     */
    public function __construct(ObjectifSpecifiqueRepository $objectifSpecifiqueRepository,
                                ProgrammeRepository $programmeRepository,
                                ProjetRepository $projetRepository)
    {
        parent::__construct($objectifSpecifiqueRepository);
        $this->repository = $objectifSpecifiqueRepository;
        $this->programmeRepository = $programmeRepository;
        $this->projetRepository = $projetRepository;
    }

    public function create(array $attributs) : JsonResponse
    {
        DB::beginTransaction();

        try
        {
            if(!(array_key_exists('programmeId', $attributs)) &&
               !(array_key_exists('projetId', $attributs))) throw new Exception( "Aucune rubrique choisis pour l'objectif specifique", 500);

            if(array_key_exists('programmeId', $attributs))
            {
                $programme = $this->programmeRepository->findById($attributs['programmeId']);

                $objectif = $programme->objectifSpecifiques()->create($attributs);
            }

            else
            {
                $projet = $this->projetRepository->findById($attributs['projetId']);

                $objectif = $projet->objectifSpecifiques()->create($attributs);
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

            if(!($objectif = $this->repository->findById($id))) throw new Exception( "Objectif specifique inconnue", 500);

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
