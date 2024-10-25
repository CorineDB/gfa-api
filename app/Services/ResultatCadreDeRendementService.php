<?php

namespace App\Services;

use App\Http\Resources\cadre_de_mesure_rendement\CadreDeMesureRendementResource;
use App\Http\Resources\cadre_de_mesure_rendement\resultats\ResultatCadreDeRendementResource;
use App\Http\Resources\gouvernance\OptionsDeReponseResource;
use App\Http\Resources\ProjetResource;
use App\Models\CadreDeMesureRendement;
use App\Models\ResultatCadreDeRendement;
use App\Repositories\ProjetRepository;
use App\Repositories\ResultatCadreDeRendementRepository;
use Core\Services\Contracts\BaseService;
use Core\Services\Interfaces\ResultatCadreDeRendementServiceInterface;
use Exception;
use App\Traits\Helpers\LogActivity;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Response;

/**
* Interface ResultatCadreDeRendementServiceInterface
* @package Core\Services\Interfaces
*/
class ResultatCadreDeRendementService extends BaseService implements ResultatCadreDeRendementServiceInterface
{

    /**
     * @var service
     */
    protected $repository;

    /**
     * ResultatCadreDeRendementRepository constructor.
     *
     * @param ResultatCadreDeRendementRepository $resultatCadreDeRendement
     */
    public function __construct(ResultatCadreDeRendementRepository $resultatCadreDeRendement)
    {
        parent::__construct($resultatCadreDeRendement);
    }

    /**
     * Renvoie la liste des resultats de cadres de rendement li s  un programme
     *
     * Si l'utilisateur est administrateur, la liste des resultats de cadres de rendement est vide.
     *
     * @param array $columns colonnes   inclure dans la liste
     * @param array $relations relations   inclure dans la liste
     *
     * @return JsonResponse
     *
     * @throws \Throwable
     */
    public function all(array $columns = ['*'], array $relations = []): JsonResponse
    {
        try
        {
            if(Auth::user()->hasRole('administrateur')){
                $optionsDeReponse = [];
            }
            else{
                //$projets = $this->repository->allFiltredBy([['attribut' => 'programmeId', 'operateur' => '=', 'valeur' => auth()->user()->programme->id]]);
                $resultats_cadre_de_rendement = Auth::user()->programme->resultats_cadre_de_rendement;
            }

            return response()->json(['statut' => 'success', 'message' => null, 'data' => ResultatCadreDeRendementResource::collection($resultats_cadre_de_rendement), 'statutCode' => Response::HTTP_OK], Response::HTTP_OK);
        }

        catch (\Throwable $th)
        {
            return response()->json(['statut' => 'error', 'message' => $th->getMessage(), 'errors' => []], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * Retourne un ResultatCadreDeRendement par son identifiant.
     * 
     * @param int|ResultatCadreDeRendement $resultat_cadre_de_rendement
     * @param array $columns
     * @param array $relations
     * @param array $appends
     * @return JsonResponse
     * @throws Exception si le ResultatCadreDeRendement n'existe pas
     */
    public function findById($resultat_cadre_de_rendement, array $columns = ['*'], array $relations = [], array $appends = []): JsonResponse
    {
        try
        {
            if(!is_object($resultat_cadre_de_rendement) && !($resultat_cadre_de_rendement = $this->repository->findById($resultat_cadre_de_rendement))) throw new Exception("Resultat de cadre de rendement introuvable", Response::HTTP_NOT_FOUND);

            return response()->json(['statut' => 'success', 'message' => null, 'data' => new ResultatCadreDeRendementResource($resultat_cadre_de_rendement), 'statutCode' => Response::HTTP_OK], Response::HTTP_OK);
        }

        catch (\Throwable $th)
        {
            return response()->json(['statut' => 'error', 'message' => $th->getMessage(), 'errors' => []], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * Création d'un ResultatCadreDeRendement.
     *
     * @param array $attributs
     * @return JsonResponse
     */
    public function create(array $attributs) : JsonResponse
    {
        DB::beginTransaction();

        try {

            $programme = Auth::user()->programme;

            $attributs = array_merge($attributs, ['programmeId' => $programme->id]);
            
            $resultatCadreDeRendement = $this->repository->create($attributs);

            $acteur = Auth::check() ? Auth::user()->nom . " ". Auth::user()->prenom : "Inconnu";

            $message = $message ?? Str::ucfirst($acteur) . " a créé un " . strtolower(class_basename($resultatCadreDeRendement));

            LogActivity::addToLog("Enrégistrement", $message, get_class($resultatCadreDeRendement), $resultatCadreDeRendement->id);

            DB::commit();

            return response()->json(['statut' => 'success', 'message' => "Enregistrement réussir", 'data' => new ResultatCadreDeRendementResource($resultatCadreDeRendement), 'statutCode' => Response::HTTP_CREATED], Response::HTTP_CREATED);

        } catch (\Throwable $th) {

            DB::rollBack();

            //throw $th;
            return response()->json(['statut' => 'error', 'message' => $th->getMessage(), 'errors' => [], 'statutCode' => Response::HTTP_INTERNAL_SERVER_ERROR], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * Modification d'un ResultatCadreDeRendement.
     *
     * @param ResultatCadreDeRendement|integer $resultatCadreDeRendement
     * @param array $attributs
     * @return JsonResponse
     */
    public function update($resultatCadreDeRendement, array $attributs) : JsonResponse
    {
        DB::beginTransaction();

        try {

            if(!is_object($resultatCadreDeRendement) && !($resultatCadreDeRendement = $this->repository->findById($resultatCadreDeRendement))) throw new Exception("Resultat introuvable", Response::HTTP_NOT_FOUND);

            $this->repository->update($resultatCadreDeRendement->id, $attributs);

            $resultatCadreDeRendement->refresh();

            $acteur = Auth::check() ? Auth::user()->nom . " ". Auth::user()->prenom : "Inconnu";

            $message = $message ?? Str::ucfirst($acteur) . " a modifié un " . strtolower(class_basename($resultatCadreDeRendement));

            LogActivity::addToLog("Modification", $message, get_class($resultatCadreDeRendement), $resultatCadreDeRendement->id);

            DB::commit();

            return response()->json(['statut' => 'success', 'message' => "Enregistrement réussir", 'data' => new ResultatCadreDeRendementResource($resultatCadreDeRendement), 'statutCode' => Response::HTTP_CREATED], Response::HTTP_CREATED);

        } catch (\Throwable $th) {

            DB::rollBack();

            //throw $th;
            return response()->json(['statut' => 'error', 'message' => $th->getMessage(), 'errors' => [], 'statutCode' => Response::HTTP_INTERNAL_SERVER_ERROR], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    public function constituer_cadre_de_mesure_rendement(array $attributs){

        DB::beginTransaction();

        try {

            $cadre_type=null;

            if(isset($attributs['projetId'])){
                if(!($projet = app(ProjetRepository::class)->findById($attributs['projetId']))) throw new Exception("Resultat introuvable", Response::HTTP_NOT_FOUND);
                //if($projet->cadre_de_mesure_rendement) return response()->json(['statut' => 'success', 'message' => "Le cadre de mesure du rendement de ce projet avait deja ete cree", 'data' => null, 'statutCode' => Response::HTTP_OK], Response::HTTP_OK);
                $cadre_type = $projet;
            }
            else{
                $programme = auth()->user()->programme;
                //if($programme->cadre_de_mesure_rendement) $cadre = $programme->cadre_de_mesure_rendement; //return response()->json(['statut' => 'success', 'message' => "Le cadre de mesure du rendement de ce programme avait deja ete cree", 'data' => null, 'statutCode' => Response::HTTP_OK], Response::HTTP_OK);
                $cadre_type = $programme;
            }

            foreach ($attributs['resultats_cadre_de_mesure_rendement'] as $key => $attribut) {
                $data_indicateurs = $attribut["indicateurs"];
                unset($attribut["indicateurs"]);
                //unset($attribut["resultatCadreDeRendementId"]);
                //dd( array_merge($attribut, ["rendementable_type" => get_class($cadre_type)]));
                $cadreDeMesureRendement = $cadre_type->cadre_de_mesure_rendement()->create(array_merge($attribut, ["rendementable_type" => get_class($cadre_type)]));
                //$cadre_type->resultats_cadre_de_mesure_rendement()->attach($attribut["resultatCadreDeRendementId"], array_merge($attribut, ["rendementable_type" => get_class($cadre_type)]));
                $cadre_type->refresh();
                foreach ($data_indicateurs as $key => $data_indicateur) {
                    $cadreDeMesureRendement->cadreDeMesures()->create($data_indicateur);
                }
            }

            //if(!is_object($resultatCadreDeRendement) && !($resultatCadreDeRendement = $this->repository->findById($resultatCadreDeRendement))) throw new Exception("Resultat introuvable", Response::HTTP_NOT_FOUND);

            //$this->repository->update($resultatCadreDeRendement->id, $attributs);

            $cadre_type->refresh();

            $acteur = Auth::check() ? Auth::user()->nom . " ". Auth::user()->prenom : "Inconnu";

            $message = $message ?? Str::ucfirst($acteur) . " a modifié un " . strtolower(class_basename($cadre_type));

            LogActivity::addToLog("Modification", $message, get_class($cadre_type), $cadre_type->id);

            DB::commit();

            return response()->json(['statut' => 'success', 'message' => "Enregistrement réussir", 'data' => CadreDeMesureRendementResource::collection($cadre_type->resultats_cadre_de_mesure_rendement), 'statutCode' => Response::HTTP_CREATED], Response::HTTP_CREATED);

        } catch (\Throwable $th) {

            DB::rollBack();

            //throw $th;
            return response()->json(['statut' => 'error', 'message' => $th->getMessage(), 'errors' => [], 'statutCode' => Response::HTTP_INTERNAL_SERVER_ERROR], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    public function cadreDeMesureRendement()
    {
        try
        {
            if(Auth::user()->hasRole('administrateur')){
                $cadreDeMesureRendement = [];
            }
            else{
                //$projets = $this->repository->allFiltredBy([['attribut' => 'programmeId', 'operateur' => '=', 'valeur' => auth()->user()->programme->id]]);
                $cadreDeMesureRendement = Auth::user()->programme->resultats_cadre_de_mesure_rendement;
            }

            return response()->json(['statut' => 'success', 'message' => null, 'data' => CadreDeMesureRendementResource::collection($cadreDeMesureRendement), 'statutCode' => Response::HTTP_OK], Response::HTTP_OK);
        }

        catch (\Throwable $th)
        {
            return response()->json(['statut' => 'error', 'message' => $th->getMessage(), 'errors' => []], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
}