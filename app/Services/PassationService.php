<?php

namespace App\Services;

use App\Models\MissionDeControle;
use App\Models\MOD;
use App\Repositories\PassationRepository;
use App\Traits\Helpers\LogActivity;
use Core\Services\Contracts\BaseService;
use Core\Services\Interfaces\PassationServiceInterface;
use App\Http\Resources\PassationResource;
use App\Models\EntrepriseExecutant;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

/**
* Interface PassationServiceInterface
* @package Core\Services\Interfaces
*/
class PassationService extends BaseService implements PassationServiceInterface
{

    /**
     * @var service
     */
    protected $repository;

    /**
     * PassationRepository constructor.
     *
     * @param PassationRepository $passationRepository
     */
    public function __construct(PassationRepository $passationRepository)
    {
        parent::__construct($passationRepository);
        $this->repository = $passationRepository;
    }



    /**
     * Création d'une passation
     *
     *
     */
    public function create($attributs) : JsonResponse
    {

        DB::beginTransaction();

        try {

            $user = Auth::user();

            $attributs = array_merge($attributs, ['programmeId' => $user->programmeId]);

            $entreprise = EntrepriseExecutant::find($attributs['entrepriseExecutantId']);
            if($entreprise->user->programmeId != $user->programmeId)
                    throw new Exception( "L'entreprise exécutante n'est pas dans le programme", 500);

            if(array_key_exists('modId', $attributs))
            {
                $mod = MOD::find($attributs['modId']);

                if($mod->user->programmeId != $user->programmeId)
                    throw new Exception( "L'Mod n'est pas dans le programme", 500);

                $passation = $mod->passations()->create($attributs);
            }

            else if(array_key_exists('missionDeControleId', $attributs))
            {
                $missionDeControle = MissionDeControle::find($attributs['missionDeControleId']);

                if($missionDeControle->user->programmeId != $user->programmeId)
                    throw new Exception( "La mission de controle n'est pas dans le programme", 500);

                $passation = $missionDeControle->passations()->create($attributs);
            }

            else throw new Exception( "Veillez préciser celui qui passe la passation : un mod ou une mission de controle", 500);

            if(array_key_exists('commentaire', $attributs))
            {
                $commentaire = ['contenu' => $attributs['commentaire'], 'auteurId' => Auth::user()->id];
                $commentaires = $passation->commentaires()->create($commentaire);
            }

            $acteur = Auth::check() ? Auth::user()->nom . " ". Auth::user()->prenom : "Inconnu";

            $message = $message ?? Str::ucfirst($acteur) . " a créé un " . strtolower(class_basename($passation));

            //LogActivity::addToLog("Enregistrement", $message, get_class($passation), $passation->id);

            DB::commit();

            return response()->json(['statut' => 'success', 'message' => "Passation crée", 'data' => new PassationResource($passation), 'statutCode' => Response::HTTP_OK], Response::HTTP_OK);

        } catch (\Throwable $th) {

            DB::rollback();

            //throw $th;
            return response()->json(['statut' => 'error', 'message' => $th->getMessage(), 'errors' => []], Response::HTTP_INTERNAL_SERVER_ERROR);
        }

    }



    public function update($passation, array $attributs) : JsonResponse
    {

        DB::beginTransaction();

        try {

            $user = Auth::user();

            $attributs = array_merge($attributs, ['programmeId' => $user->programmeId]);

            if(is_string($passation))
            {
                $passation = $this->repository->findById($passation);
            }
            else{
                $passation = $passation;
            }

            $passation = $this->repository->fill($attributs);

            if(array_key_exists('commentaire', $attributs))
            {
                $passation->commentaires()->create(['contenu' => $attributs['commentaire'], 'auteurId' => Auth::user()->id]);
            }

            $passation->save();

            $acteur = Auth::check() ? Auth::user()->nom . " ". Auth::user()->prenom : "Inconnu";

            $message = $message ?? Str::ucfirst($acteur) . " a modifié un " . strtolower(class_basename($passation));

            //LogActivity::addToLog("Modification", $message, get_class($passation), $passation->id);

            DB::commit();

            return response()->json(['statut' => 'success', 'message' => "Donnée  modifié", 'data' => [], 'statutCode' => Response::HTTP_OK], Response::HTTP_OK);

        } catch (\Throwable $th) {

            DB::rollback();

            //throw $th;
            return response()->json(['statut' => 'error', 'message' => $th->getMessage(), 'errors' => []], Response::HTTP_INTERNAL_SERVER_ERROR);
        }

    }

}
