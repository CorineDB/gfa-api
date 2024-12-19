<?php

namespace App\Services;

use App\Repositories\ResultatRepository;
use App\Repositories\ProgrammeRepository;
use App\Repositories\ProjetRepository;
use Core\Services\Contracts\BaseService;
use Core\Services\Interfaces\ResultatServiceInterface;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;
use Exception;


/**
* Interface ResultatServiceInterface
* @package Core\Services\Interfaces
*/
class ResultatService extends BaseService implements ResultatServiceInterface
{

    /**
     * @var service
     */
    protected $repository, $programmeRepository, $projetRepository;

    /**
     * objectifSpecifiqueService constructor.
     *
     * @param ResultatRepository $resultatRepository
     */
    public function __construct(ResultatRepository $resultatRepository,
                                ProgrammeRepository $programmeRepository,
                                ProjetRepository $projetRepository)
    {
        parent::__construct($resultatRepository);
        $this->repository = $resultatRepository;
        $this->programmeRepository = $programmeRepository;
        $this->projetRepository = $projetRepository;
    }

    public function create(array $attributs) : JsonResponse
    {
        DB::beginTransaction();

        try
        {
            if(!(array_key_exists('programmeId', $attributs)) &&
               !(array_key_exists('projetId', $attributs))) throw new Exception( "Aucune rubrique choisis pour le resultat", 500);

            if(array_key_exists('programmeId', $attributs))
            {
                $programme = $this->programmeRepository->findById($attributs['programmeId']);

                $resultat = $programme->resultats()->create(array_merge($attributs, ['programmeId' => auth()->user()->programmeId]));
            }

            else
            {
                $projet = $this->projetRepository->findById($attributs['projetId']);

                $resultat = $projet->resultats()->create(array_merge($attributs, ['programmeId' => auth()->user()->programmeId]));
            }

            DB::commit();
            return response()->json(['statut' => 'success', 'message' => null, 'data' => $resultat, 'statutCode' => Response::HTTP_OK], Response::HTTP_OK);
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

            if(!($resultat = $this->repository->findById($id))) throw new Exception( "Resultat inconnue", 500);

            $resultat->nom = $attributs['nom'];
            $resultat->description = $attributs['description'];
            $resultat->indicateurId = $attributs['indicateurId'];
            $resultat->save();

            DB::commit();
            return response()->json(['statut' => 'success', 'message' => null, 'data' => $resultat, 'statutCode' => Response::HTTP_OK], Response::HTTP_OK);
        }
        catch (\Throwable $th)
        {
            DB::rollback();
            return response()->json(['statut' => 'error', 'message' => $th->getMessage(), 'errors' => []], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
}
