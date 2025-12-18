<?php

namespace App\Services;

use App\Events\NewNotification;
use App\Http\Resources\FichierResource;
use App\Models\Ano;
use App\Models\Fichier;
use App\Models\Projet;
use App\Models\ReponseAno;
use App\Models\User;
use App\Notifications\FichierNotification;
use App\Repositories\FichierRepository;
use App\Traits\Helpers\HelperTrait;
use Core\Services\Contracts\BaseService;
use Core\Services\Interfaces\FichierServiceInterface;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Response;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Exception;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

/**
* Interface FichierServiceInterface
* @package Core\Services\Interfaces
*/
class FichierService extends BaseService implements FichierServiceInterface
{
    use HelperTrait;

    /**
     * @var service
     */
    protected $repository;

    /**
     * suiviService constructor.
     *
     * @param FichierRepository $fichierRepository
     */
    public function __construct(FichierRepository $fichierRepository)
    {
        parent::__construct($fichierRepository);
        $this->repository = $fichierRepository;
    }

    public function find($modelId)
    {

        try {

            $fichier = $modelId;

            if($fichier == null && !file_exists(storage_path("app/".$fichier->chemin)))throw new Exception( "Cet fichier n'existe pas", 500);

            if(!file_exists(storage_path("app/".$fichier->chemin)))throw new Exception( "Cet fichier n'existe plus sur le serveur", 500);

            return response()/*->json(['statut' => 'success', 'message' => null, 'data' => $fichier, 'statutCode' => Response::HTTP_OK], Response::HTTP_OK)*/->download(storage_path("app/".$fichier->chemin), $fichier->description);

        } catch (\Throwable $th) {

            DB::rollBack();

            //throw $th;
            return response()->json(['statut' => 'error', 'message' => $th->getMessage(), 'errors' => [], 'statutCode' => Response::HTTP_INTERNAL_SERVER_ERROR], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    public function create(array $attributs) : JsonResponse
    {
        DB::beginTransaction();
        $errors = [];

        try
        {

            if(array_key_exists('projetId', $attributs))
            {
                if(!($projet = Projet::findByKey($attributs['projetId'])))throw new Exception( "Cet projet n'existe pas", 500);

                $fichier = $this->storeFile($attributs['fichier'], 'projets', $projet, null, 'fichier');

                if(array_key_exists('sharedId', $attributs))
                {
                    foreach($attributs['sharedId'] as $id)
                    {

                        $user = User::findByKey($id);

                        if($user)
                        {
                            $this->storeFile($attributs['fichier'], 'projets', $projet, null, 'fichier', ['fichierId' => $fichier->id, 'userId' => $user->id]);
                        }

                        $data['texte'] = "Un fichier vient d'etre partagé avec vous dans le dossier projet";
                        $data['id'] = $fichier->id;
                        $data['auteurId'] = Auth::user()->id;
                        $notification = new FichierNotification($data);

                        $user->notify($notification);

                        $notification = $user->notifications->last();

                        event(new NewNotification($this->formatageNotification($notification, $user)));
                    }
                }

            }


            else if(array_key_exists('anoId', $attributs))
            {
                if(!($ano = Ano::findByKey($attributs['anoId'])))throw new Exception( "Cet ano n'existe pas", 500);

                $fichier = $this->storeFile($attributs['fichier'], 'anos', $ano, null, 'fichier');

                if(array_key_exists('sharedId', $attributs))
                {
                    foreach($attributs['sharedId'] as $id)
                    {
                        $user = User::findByKey($id);

                        if($user)
                        {
                            $this->storeFile($attributs['fichier'], 'anos', $ano, null, 'fichier', ['fichierId' => $fichier->id, 'userId' => $user->id]);
                        }

                        $data['texte'] = "Un fichier vient d'etre partagé avec vous dans le dossier ano";
                        $data['id'] = $fichier->id;
                        $data['auteurId'] = Auth::user()->id;
                        $notification = new FichierNotification($data);

                        $user->notify($notification);

                        $notification = $user->notifications->last();

                        event(new NewNotification($this->formatageNotification($notification, $user)));
                    }
                }
            }


            else if(array_key_exists('reponseAnoId', $attributs))
            {
                if(!($reponseAno = ReponseAno::findByKey($attributs['reponseAnoId'])))throw new Exception( "Cette reponse n'existe pas", 500);

                $fichier = $this->storeFile($attributs['fichier'], "anos/reponses", $reponseAno, null, 'fichier');

                if(array_key_exists('sharedId', $attributs))
                {
                    foreach($attributs['sharedId'] as $id)
                    {
                        $user = User::findByKey($id);

                        if($user)
                        {
                            $this->storeFile($attributs['fichier'], "anos/reponses", $reponseAno, null, 'fichier', ['fichierId' => $fichier->id, 'userId' => $user->id]);
                        }

                        $data['texte'] = "Un fichier vient d'etre partagé avec vous dans le dossier reponseAno";
                        $data['id'] = $fichier->id;
                        $data['auteurId'] = Auth::user()->id;
                        $notification = new FichierNotification($data);

                        $user->notify($notification);

                        $notification = $user->notifications->last();

                        event(new NewNotification($this->formatageNotification($notification, $user)));
                    }
                }
            }

            else if(array_key_exists('autre', $attributs))
            {
                $fichier = $this->storeFile($attributs['fichier'], "autres", null, null, 'fichier');

                if(array_key_exists('sharedId', $attributs))
                {
                    foreach($attributs['sharedId'] as $id)
                    {
                        $user = User::findByKey($id);

                        if($user)
                        {
                            $this->storeFile($attributs['fichier'], "autres", null, null, 'fichier', ['fichierId' => $fichier->id, 'userId' => $user->id]);
                        }

                        $data['texte'] = "Un fichier vient d'etre partagé avec vous dans le dossier autre";
                        $data['id'] = $fichier->id;
                        $data['auteurId'] = Auth::user()->id;
                        $notification = new FichierNotification($data);

                        $user->notify($notification);

                        $notification = $user->notifications->last();

                        event(new NewNotification($this->formatageNotification($notification, $user)));
                    }
                }
            }

            DB::commit();
            return response()->json(['statut' => 'success', 'message' => null, 'data' => new FichierResource($fichier), 'erreur role' => $errors, 'statutCode' => Response::HTTP_OK], Response::HTTP_OK);
        }
        catch (\Throwable $th)
        {
            DB::rollback();
            return response()->json(['statut' => 'error', 'message' => $th->getMessage(), 'errors' => []], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }



    public function destroy($fichierId) : JsonResponse
    {
        DB::beginTransaction();

        try
        {
            $fichier = $this->repository->findById($fichierId);

            if($fichier->userId != Auth::id()) throw new Exception( "L'utilisateur actuel n'est pas l'auteur du fichier", 500);

            $fichier = $this->repository->delete($fichierId);

            DB::commit();
            return response()->json(['statut' => 'success', 'message' => null, 'data' => $fichier, 'statutCode' => Response::HTTP_OK], Response::HTTP_OK);
        }

        catch (\Throwable $th)
        {
            DB::rollback();
            return response()->json(['statut' => 'error', 'message' => $th->getMessage(), 'errors' => []], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    public function all(array $columns = ['*'], array $relations = []): JsonResponse
    {
        $fichiers = Fichier::all();

        $fichiers = Fichier::load('roles')->get();
        /* whereRelation('roles','id', '=', 1)->get(); */
        return response()->json(['statut' => 'success', 'message' => null, 'data' => $fichiers, 'statutCode' => Response::HTTP_OK], Response::HTTP_OK);
    }
}
