<?php

namespace App\Services;

use App\Http\Resources\gouvernance\FichesDeSyntheseResource;
use App\Repositories\FicheDeSyntheseRepository;
use Core\Services\Contracts\BaseService;
use Core\Services\Interfaces\FicheDeSyntheseServiceInterface;
use Exception;
use App\Traits\Helpers\LogActivity;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Response;

/**
* Interface FicheDeSyntheseServiceInterface
* @package Core\Services\Interfaces
*/
class FicheDeSyntheseService extends BaseService implements FicheDeSyntheseServiceInterface
{

    /**
     * @var service
     */
    protected $repository;

    /**
     * FicheDeSyntheseRepository constructor.
     *
     * @param FicheDeSyntheseRepository $fiche_de_syntheseRepository
     */
    public function __construct(FicheDeSyntheseRepository $fiche_de_syntheseRepository)
    {
        parent::__construct($fiche_de_syntheseRepository);
    }

    public function all(array $columns = ['*'], array $relations = []): JsonResponse
    {
        try
        {
            if((Auth::user()->hasRole('administrateur') || auth()->user()->profilable_type == "App\\Models\\Administrateur")){
                $fiches_de_synthese = $this->repository->all();
            }
            else{
                //$projets = $this->repository->allFiltredBy([['attribut' => 'programmeId', 'operateur' => '=', 'valeur' => auth()->user()->programme->id]]);
                $fiches_de_synthese = Auth::user()->programme->fiches_de_synthese;
            }

            return response()->json(['statut' => 'success', 'message' => null, 'data' => FichesDeSyntheseResource::collection($fiches_de_synthese), 'statutCode' => Response::HTTP_OK], Response::HTTP_OK);
        }

        catch (\Throwable $th)
        {
            return response()->json(['statut' => 'error', 'message' => $th->getMessage(), 'errors' => []], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    public function findById($fiche_de_synthese, array $columns = ['*'], array $relations = [], array $appends = []): JsonResponse
    {
        try
        {
            if(!is_object($fiche_de_synthese) && !($fiche_de_synthese = $this->repository->findById($fiche_de_synthese))) throw new Exception("FicheDeSynthese inconnue.", 500);

            return response()->json(['statut' => 'success', 'message' => null, 'data' => new FichesDeSyntheseResource
            ($fiche_de_synthese), 'statutCode' => Response::HTTP_OK], Response::HTTP_OK);
        }

        catch (\Throwable $th)
        {
            return response()->json(['statut' => 'error', 'message' => $th->getMessage(), 'errors' => []], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    public function create(array $attributs) : JsonResponse
    {
        DB::beginTransaction();

        try {

            $programme = Auth::user()->programme;

            $attributs = array_merge($attributs, ['programmeId' => $programme->id]);
            
            $fiche_de_synthese = $this->repository->create($attributs);

            $acteur = Auth::check() ? Auth::user()->nom . " ". Auth::user()->prenom : "Inconnu";

            $message = $message ?? Str::ucfirst($acteur) . " a créé un " . strtolower(class_basename($fiche_de_synthese));

            //LogActivity::addToLog("Enrégistrement", $message, get_class($fiche_de_synthese), $fiche_de_synthese->id);

            DB::commit();

            return response()->json(['statut' => 'success', 'message' => "Enregistrement réussir", 'data' => new FichesDeSyntheseResource($fiche_de_synthese), 'statutCode' => Response::HTTP_CREATED], Response::HTTP_CREATED);

        } catch (\Throwable $th) {

            DB::rollBack();

            //throw $th;
            return response()->json(['statut' => 'error', 'message' => $th->getMessage(), 'errors' => [], 'statutCode' => Response::HTTP_INTERNAL_SERVER_ERROR], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    public function update($fiche_de_synthese, array $attributs) : JsonResponse
    {
        DB::beginTransaction();

        try {

            if(!is_object($fiche_de_synthese) && !($fiche_de_synthese = $this->repository->findById($fiche_de_synthese))) throw new Exception("Ce fond n'existe pas", 500);

            $this->repository->update($fiche_de_synthese->id, $attributs);

            $fiche_de_synthese->refresh();

            $acteur = Auth::check() ? Auth::user()->nom . " ". Auth::user()->prenom : "Inconnu";

            $message = $message ?? Str::ucfirst($acteur) . " a modifié un " . strtolower(class_basename($fiche_de_synthese));

            //LogActivity::addToLog("Modification", $message, get_class($fiche_de_synthese), $fiche_de_synthese->id);

            DB::commit();

            return response()->json(['statut' => 'success', 'message' => "Enregistrement réussir", 'data' => new FichesDeSyntheseResource($fiche_de_synthese), 'statutCode' => Response::HTTP_CREATED], Response::HTTP_CREATED);

        } catch (\Throwable $th) {

            DB::rollBack();

            //throw $th;
            return response()->json(['statut' => 'error', 'message' => $th->getMessage(), 'errors' => [], 'statutCode' => Response::HTTP_INTERNAL_SERVER_ERROR], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

}