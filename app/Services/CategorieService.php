<?php

namespace App\Services;

use App\Http\Resources\CategorieResource;
use App\Repositories\CategorieRepository;
use App\Repositories\ProgrammeRepository;
use App\Traits\Helpers\LogActivity;
use Core\Services\Contracts\BaseService;
use Core\Services\Interfaces\CategorieServiceInterface;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Response;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

/**
* Interface CategorieServiceInterface
* @package Core\Services\Interfaces
*/
class CategorieService extends BaseService implements CategorieServiceInterface
{

    /**
     * @var service
     */
    protected $repository;

    /**
     * CategorieRepository constructor.
     *
     * @param CategorieRepository $categorieRepository
     */
    public function __construct(CategorieRepository $categorieRepository)
    {
        parent::__construct($categorieRepository);
    }


    public function all(array $attributs = ['*'], array $relations = []): JsonResponse
    {
        try
        {
            $categories = collect([]);
            
            if(!(Auth::user()->hasRole('administrateur') || auth()->user()->profilable_type == "App\\Models\\Administrateur")){
                $categories = Auth::user()->programme->categories;
            }

            return response()->json(['statut' => 'success', 'message' => null, 'data' => CategorieResource::collection($categories), 'statutCode' => Response::HTTP_OK], Response::HTTP_OK);
        }
        catch (\Throwable $th)
        {
            return response()->json(['statut' => 'error', 'message' => $th->getMessage(), 'errors' => []], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    public function create($attributs) : JsonResponse
    {
        DB::beginTransaction();

        try {

            if(!($programme = app(ProgrammeRepository::class)->findById(Auth::user()->programmeId))) throw new Exception( "Ce programme n'existe pas", 500);

            if(isset($attributs['categorieId'])){
                if(!($parentCategorie = $this->repository->findById($attributs['categorieId']))) throw new Exception( "Cette categorie n'existe pas", 500);

                if($parentCategorie->programmeId != $programme->id) throw new Exception("La categorie parent n'est pas de ce programme", 500);

            }

            $attributs = array_merge($attributs, ['nom' => strtolower($attributs['nom']), 'programmeId' => $programme->id]);

            $categorie = $this->repository->create($attributs);

            $acteur = Auth::check() ? Auth::user()->nom . " ". Auth::user()->prenom : "Inconnu";

            $message = $message ?? Str::ucfirst($acteur) . " a créé un " . strtolower(class_basename($categorie));

            //LogActivity::addToLog("Enregistrement", $message, get_class($categorie), $categorie->id);

            DB::commit();

            return response()->json(['statut' => 'success', 'message' => "Unitee de mesure crée", 'data' => new CategorieResource($categorie), 'statutCode' => Response::HTTP_OK], Response::HTTP_OK);

        } catch (\Throwable $th) {

            DB::rollback();

            //throw $th;
            return response()->json(['statut' => 'error', 'message' => $th->getMessage(), 'errors' => []], Response::HTTP_INTERNAL_SERVER_ERROR);
        }

    }

    public function update($categorie, array $attributs) : JsonResponse
    {
        DB::beginTransaction();

        try {

            if(is_string($categorie))
            {
                $categorie = $this->repository->findById($categorie);
            }
            else{
                $categorie = $categorie;
            }

            if(isset($attributs['categorieId'])){
                if(!($parentCategorie = $this->repository->findById($attributs['categorieId']))) throw new Exception( "Cette categorie n'existe pas", 500);

                if($categorie->programmeId != $parentCategorie->programmeId) throw new Exception("La categorie parent n'est pas de ce programme", 500);
            }
            
            if(isset($attributs['nom'])){
                $attributs = array_merge($attributs, ['nom' => strtolower($attributs['nom'])]);
            }
            
            $categorie = $categorie->fill($attributs);

            $categorie->save();

            $acteur = Auth::check() ? Auth::user()->nom . " ". Auth::user()->prenom : "Inconnu";

            $message = $message ?? Str::ucfirst($acteur) . " a modifié un " . strtolower(class_basename($categorie));

            //LogActivity::addToLog("Modification", $message, get_class($categorie), $categorie->id);

            DB::commit();

            return response()->json(['statut' => 'success', 'message' => "Donnée de l'unitee de mesure modifié", 'data' => new CategorieResource($categorie), 'statutCode' => Response::HTTP_OK], Response::HTTP_OK);

        } catch (\Throwable $th) {

            DB::rollback();

            //throw $th;
            return response()->json(['statut' => 'error', 'message' => $th->getMessage(), 'errors' => []], Response::HTTP_INTERNAL_SERVER_ERROR);
        }

    }
}