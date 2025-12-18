<?php

namespace App\Services;

use App\Events\NewNotification;
use App\Http\Resources\reponseAnos\ReponseAnoResource;
use App\Jobs\SendEmailJob;
use App\Models\Ano;
use App\Models\ReponseAno;
use App\Models\User;
use App\Notifications\FichierNotification;
use App\Repositories\AnoRepository;
use App\Repositories\ReponseAnoRepository;
use App\Traits\Helpers\HelperTrait;
use App\Traits\Helpers\IdTrait;
use App\Traits\Helpers\LogActivity;
use App\Traits\Helpers\Pta;
use Core\Services\Contracts\BaseService;
use Core\Services\Interfaces\ReponseAnoServiceInterface;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

/**
* Interface UserServiceInterface
* @package Core\Services\Interfaces
*/
class ReponseAnoService extends BaseService implements ReponseAnoServiceInterface
{
    use IdTrait, Pta, HelperTrait;

    /**
     * @var service
     */
    protected $repository, $bailleurRepository, $anoRepository;

    /**
     * ReponseAnoService constructor.
     *
     * @param ReponseAnoRepository $reponseAnoRepository
     */
    public function __construct(ReponseAnoRepository $reponseAnoRepository, AnoRepository $anoRepository)
    {
        parent::__construct($reponseAnoRepository);
        $this->repository = $reponseAnoRepository;
        $this->anoRepository = $anoRepository;
    }

    public function all(array $attributs = ['*'], array $relations = []): JsonResponse
    {
        try
        {
            return response()->json(['statut' => 'success', 'message' => null, 'data' => ReponseAnoResource::collection($this->repository->all()), 'statutCode' => Response::HTTP_OK], Response::HTTP_OK);
        }
        catch (\Throwable $th)
        {
            return response()->json(['statut' => 'error', 'message' => $th->getMessage(), 'errors' => []], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    public function create(array $attributs) : JsonResponse
    {
        DB::beginTransaction();

        try
        {
            $ano = Ano::findByKey($attributs['anoId']);

            if(!$ano) throw new Exception("Ano inconnue", 1);

            $attributs = array_merge($attributs, ['anoId' => $ano->id, 'auteurId' => Auth::user()->id]);

            if(array_key_exists('reponseId', $attributs))
            {
                $reponse = ReponseAno::findByKey($attributs['reponseId']);

                if(!$reponse) throw new Exception("Reponse inconnue", 1);

                $attributs = array_merge($attributs, ['reponseId' => $reponse->id]);
            }

            $reponseAno = $this->repository->create($attributs);

            $reponseAno = $reponseAno->fresh();
            $ano = $reponseAno->ano;

            $ano->statut = $attributs['statut'];
            $ano->save();

            /*foreach ($attributs['documents'] as $document) {

                $id = $reponseAno->ano->secure_id;

                $this->storeFile($document, "anos/{$id}/reponses", $reponseAno, null, null);
            }*/

            $i = 0;

            while(array_key_exists('fichier'.$i, $attributs))
            {
                if($attributs['fichier'.$i]->getClientOriginalExtension() != 'jpg'  &&
                   $attributs['fichier'.$i]->getClientOriginalExtension() != 'png' &&
                   $attributs['fichier'.$i]->getClientOriginalExtension() != 'jpeg' &&
                   $attributs['fichier'.$i]->getClientOriginalExtension() != 'docx' &&
                   $attributs['fichier'.$i]->getClientOriginalExtension() != 'pdf')
                    throw new Exception("Le fichier doit être au format jpg, png, jpeg, docx ou pdf", 500);

                $id = $reponseAno->ano->secure_id;

                $fichier = $this->storeFile($attributs['fichier'.$i], "anos/{$id}/reponses", $reponseAno, null, 'fichier');

                if(array_key_exists('sharedId', $attributs))
                {
                    foreach($attributs['sharedId'] as $id)
                    {
                        $user = User::findByKey($id);

                        if($user)
                        {
                            $this->storeFile($attributs['fichier'.$i], "anos/{$id}/reponses", $reponseAno, null, 'fichier', ['fichierId' => $fichier->id, 'userId' => $user->id]);
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

                $i++;
            }


            $reponseAno = $reponseAno->fresh();

            if($reponseAno->ano->statut === 0){
                //Email reponse à la demande d'alerte
                dispatch(new SendEmailJob($reponseAno->ano->auteur, "reponse-ano", "Réponse suite à la demande d'ano"))->delay(now()->addSeconds(15));
            }

            $acteur = Auth::check() ? Auth::user()->nom . " ". Auth::user()->prenom : "Inconnu";

            $message = $message ?? Str::ucfirst($acteur) . " a répondu à la demande de l'ano " . $reponseAno->ano->dossier;

            //LogActivity::addToLog("Enregistrement", $message, get_class($reponseAno), $reponseAno->id);

            DB::commit();

            return response()->json(['statut' => 'success', 'message' => null, 'data' => new ReponseAnoResource($reponseAno), 'statutCode' => Response::HTTP_OK], Response::HTTP_OK);
        }
        catch (\Throwable $th)
        {
            DB::rollback();
            return response()->json(['statut' => 'error', 'message' => $th->getMessage(), 'errors' => []], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    public function update($reponseAnoId, array $attributs) : JsonResponse
    {
        DB::beginTransaction();

        try
        {
            $ano = $this->anoRepository->findById($attributs['anoId']);

            $attributs = array_merge($attributs, ['anoId' => $ano->id, 'auteurId' => Auth::user()->id]);

            $reponse = ReponseAno::findByKey($attributs['reponseId']);

            if(!$reponse) throw new Exception("Reponse inconnue", 1);

            $attributs = array_merge($attributs, ['reponseId' => $reponse->id]);

            $reponseAno = $this->repository->findById($reponseAnoId);

            $reponseAno = $reponseAno->fill($attributs);

            $reponseAno->save();

            $reponseAno = $reponseAno->fresh();

            if(array_key_exists('statut', $attributs) && $attributs['statut'] === -1 ){

                $statut = $reponseAno->statut;

                $this->verifieStatut($statut, $attributs['statut']);

                $statut = ['etat' => $attributs['statut']];

                $reponseAno->ano->statuts()->create($statut);
            }

            $old_documents = [];

            /*foreach ($attributs['documents'] as $document) {

                $id = $reponseAno->ano->secure_id;

                $old_documents = $reponseAno->documents;

                $this->storeFile($document, "anos/{$id}/reponses", $reponseAno, null, null);
            }*/

            $i = 0;

            while(array_key_exists('fichier'.$i, $attributs))
            {
                if($attributs['fichier'.$i]->getClientOriginalExtension() != 'jpg'  &&
                   $attributs['fichier'.$i]->getClientOriginalExtension() != 'png' &&
                   $attributs['fichier'.$i]->getClientOriginalExtension() != 'jpeg' &&
                   $attributs['fichier'.$i]->getClientOriginalExtension() != 'pdf')
                    throw new Exception("Le fichier doit être au format jpg, png, jpeg ou pdf", 500);

                $id = $reponseAno->ano->secure_id;

                $old_documents = $reponseAno->documents;

                $this->storeFile($attributs['fichier'.$i], "anos/{$id}/reponses", $reponseAno, null, 'fichier');

                $i++;
            }

            foreach ($old_documents as $old_document) {

                if($old_document != null){

                    Storage::disk('public')->delete($old_document->chemin);
 
                    if($old_document){
                        $old_document->delete();
                    }
                }
            }

            $reponseAno = $reponseAno->fresh();

            if($reponseAno->ano->statut === 0){
                //Email reponse à la demande d'alerte
                dispatch(new SendEmailJob($reponseAno->ano->auteur, "reponse-ano", "Réponse suite à la demande d'ano"))->delay(now()->addSeconds(15));
            }

            $acteur = Auth::check() ? Auth::user()->nom . " ". Auth::user()->prenom : "Inconnu";

            $message = $message ?? Str::ucfirst($acteur) . " a modifier sa réponse en rapport avec la demande d'ano " . $reponseAno->ano->dossier;

            //LogActivity::addToLog("Modification", $message, get_class($reponseAno), $reponseAno->id);

            DB::commit();

            return response()->json(['statut' => 'success', 'message' => null, 'data' => new ReponseAnoResource($reponseAno), 'statutCode' => Response::HTTP_OK], Response::HTTP_OK);
        }
        catch (\Throwable $th)
        {
            DB::rollback();

            return response()->json(['statut' => 'error', 'message' => $th->getMessage(), 'errors' => []], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

}
