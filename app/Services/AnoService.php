<?php

namespace App\Services;

use App\Events\NewNotification;
use App\Http\Resources\anos\AnosResource;
use App\Http\Resources\reponseAnos\ReponseAnoResource;
use App\Jobs\SendEmailJob;
use App\Models\Ano;
use App\Models\Bailleur;
use App\Models\TypeAno;
use App\Models\User;
use App\Notifications\AnoNotification;
use App\Notifications\CommentaireNotification;
use App\Notifications\FichierNotification;
use App\Repositories\AnoRepository;
use App\Traits\Eloquents\DBStatementTrait;
use App\Traits\Helpers\HelperTrait;
use App\Traits\Helpers\LogActivity;
use Core\Services\Contracts\BaseService;
use Core\Services\Interfaces\AnoServiceInterface;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;
use Exception;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

/**
* Interface AnoServiceInterface
* @package Core\Services\Interfaces
*/
class AnoService extends BaseService implements AnoServiceInterface
{

    use  HelperTrait, DBStatementTrait;
    /**
     * @var service
     */
    protected $repository;

    /**
     * AnoService constructor.
     *
     * @param AnoRepository $anoRepository
     */
    public function __construct(AnoRepository $anoRepository)
    {
        parent::__construct($anoRepository);
        $this->repository = $anoRepository;
    }

    public function all(array $columns = ['*'], array $relations = []): JsonResponse
    {

        try {

            $anos = [];

            foreach($this->repository->all() as $ano)
            {
                if($ano->programme()->id  != Auth::user()->programmeId) continue;

                array_push($anos, $ano);
            }

            return response()->json(['statut' => 'success', 'message' => null, 'data' => AnosResource::collection($anos), 'statutCode' => Response::HTTP_OK], Response::HTTP_OK);

        } catch (\Throwable $th) {

            DB::rollBack();

            //throw $th;
            return response()->json(['statut' => 'error', 'message' => $th->getMessage(), 'errors' => [], 'statutCode' => Response::HTTP_INTERNAL_SERVER_ERROR], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    public function create(array $attributs) : JsonResponse
    {
        DB::beginTransaction();

        try
        {

            $auteur = Auth::user();

            $attributs = array_merge($attributs, ['auteurId' => $auteur->id, 'dateDeSoumission' => $attributs['dateSoumission'], 'typeId' => 0]);

            if(is_string($attributs['bailleurId']))
            {
                $attributs = array_merge($attributs, ['bailleurId' => Bailleur::findByKey($attributs['bailleurId'])->id]);
            }

            if( $attributs['typeId'] == 0 )
            {
                $this->changeState(0);
            }

            $attributs = array_merge($attributs, ['statut' => 0]);

            $ano = $this->repository->fill($attributs);

            $ano->save();

            if( $attributs['typeId'] == 0 )
            {
                $this->changeState(1);
            }

            //$type = TypeAno::find($attributs['typeId']);

            $i = 0;

            while(array_key_exists('fichier'.$i, $attributs))
            {
                if($attributs['fichier'.$i]->getClientOriginalExtension() != 'jpg'  &&
                   $attributs['fichier'.$i]->getClientOriginalExtension() != 'png' &&
                   $attributs['fichier'.$i]->getClientOriginalExtension() != 'jpeg' &&
                   $attributs['fichier'.$i]->getClientOriginalExtension() != 'pdf')
                    throw new Exception("Le fichier doit être au format jpg, png, jpeg ou pdf", 500);

                $fichier = $this->storeFile($attributs['fichier'.$i], 'anos', $ano, null, 'fichier');

                if(array_key_exists('sharedId', $attributs))
                {
                    foreach($attributs['sharedId'] as $id)
                    {
                        $user = User::findByKey($id);

                        if($user)
                        {
                            $this->storeFile($attributs['fichier'.$i], 'anos', $ano, null, 'fichier', ['fichierId' => $fichier->id, 'userId' => $user->id]);
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

                $i++;
            }

            $jour = date('Y-m-d');

            //$maDate = strtotime($jour."+ ".$type->duree." days");

            $maDate = strtotime($jour."+ 14 days");

            $duree = ['debut' => $jour, 'fin' => date("Y-m-d",$maDate)];

            $ano->durees()->create($duree);

            /*$statut = ['etat' => 0];

            $statuts = $ano->statuts()->create($statut);*/

            $ano = $ano->fresh();

            if($ano->statut === 0){
                //Email demande d'alerte
                $data['texte'] = "Une nouvelle demande d'ano vient d'être soumis";
                $data['id'] = null;
                $data['auteurId'] = 0;
                $notification = new AnoNotification($data);

                $ano->bailleur->user->notify($notification);

                $notification = $ano->bailleur->user->notifications->last();

                event(new NewNotification($this->formatageNotification($notification, $ano->bailleur->user)));

                dispatch(new SendEmailJob($ano->bailleur->user, "demande-ano", "Nouvelle demande d'ano"))->delay(now()->addSeconds(15));
            }

            if(isset($attributs['commentaire']))
            {
                $attributsCommentaire = ['contenu' => $attributs['commentaire'], 'auteurId' => Auth::id()];

                $ano->commentaires()->create($attributsCommentaire);

                $data['texte'] = "Un commentaire vient d'etre effectué pour un ano";
                $data['id'] = $ano->id;
                $data['auteurId'] = Auth::user()->id;
                $notification = new CommentaireNotification($data);

                $allUsers = User::where('programmeId', Auth::user()->programmeId);
                foreach($allUsers as $user)
                {
                    if($user->hasPermissionTo('voir-un-commentaire'))
                    {
                        $user->notify($notification);

                        $notification = $user->notifications->last();

                        event(new NewNotification($this->formatageNotification($notification, $user)));

                    }
                }

            }

            $acteur = Auth::check() ? Auth::user()->nom . " ". Auth::user()->prenom : "Inconnu";

            $message = $message ?? Str::ucfirst($acteur) . " a créé un " . strtolower(class_basename($ano));

            //LogActivity::addToLog("Enrégistrement", $message, get_class($ano), $ano->id);

            DB::commit();

            return response()->json(['statut' => 'success', 'message' => null, 'data' => new AnosResource($ano), 'statutCode' => Response::HTTP_OK], Response::HTTP_OK);
        }
        catch (\Throwable $th)
        {
            DB::rollback();
            return response()->json(['statut' => 'error', 'message' => $th->getMessage(), 'errors' => []], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    public function update($anoId, array $attributs) : JsonResponse
    {
        DB::beginTransaction();

        try
        {
            if(array_key_exists('dateSoumission', $attributs))
            {
                $attributs = array_merge($attributs, ['dateDeSoumission' => $attributs['dateSoumission']]);
            }

            $ano = $this->repository->findById($anoId, $attributs);

            $ano = $ano->fill($attributs);

            $ano->save();

            //$type = TypeAno::find($attributs['typeId']);

            $jour = date('Y-m-d');

            //$maDate = strtotime($jour."+ ".$type->duree." days");

            //$duree = ['debut' => $jour, 'fin' => date("Y-m-d",$maDate)];

            //$ano->durees()->create($duree);

            //$statut = ['etat' => $attributs['statut']];

            $ano->statut = $attributs['statut'];
            $ano->save();

            $ano = $ano->fresh();

            if(isset($attributs['commentaire']))
            {
                $attributsCommentaire = ['contenu' => $attributs['commentaire'], 'auteurId' => Auth::id()];

                $ano->commentaires()->create($attributsCommentaire);

                $data['texte'] = "Un commentaire vient d'etre effectué pour un ano";
                $data['id'] = $ano->id;
                $data['auteurId'] = Auth::user()->id;
                $notification = new CommentaireNotification($data);

                $allUsers = User::where('programmeId', Auth::user()->programmeId);
                foreach($allUsers as $user)
                {
                    if($user->hasPermissionTo('voir-un-commentaire'))
                    {
                        $user->notify($notification);

                        $notification = $user->notifications->last();

                        event(new NewNotification($this->formatageNotification($notification, $user)));

                    }
                }

            }

            if($ano->statut === 0){

                //Email demande d'alerte
                $data['texte'] = "Une nouvelle demande d'ano vient d'être soumis";
                $data['id'] = null;
                $notification = new AnoNotification($data);

                $ano->bailleur->user->notify($notification);

                $notification = $ano->bailleur->user->notifications->last();

                event(new NewNotification($this->formatageNotification($notification, $ano->bailleur->user)));

                dispatch(new SendEmailJob($ano->bailleur->user, "demande-ano", "Nouvelle demande d'ano"))->delay(now()->addSeconds(15));
            }


            $acteur = Auth::check() ? Auth::user()->nom . " ". Auth::user()->prenom : "Inconnu";

            $message = $message ?? Str::ucfirst($acteur) . " a modifié un " . strtolower(class_basename($ano));

            //LogActivity::addToLog("Modification", $message, get_class($ano), $ano->id);

            DB::commit();

            return response()->json(['statut' => 'success', 'message' => null, 'data' => $ano, 'statutCode' => Response::HTTP_OK], Response::HTTP_OK);
        }
        catch (\Throwable $th)
        {
            DB::rollback();
            return response()->json(['statut' => 'error', 'message' => $th->getMessage(), 'errors' => []], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    public function rappel($anoId) : JsonResponse
    {
        DB::beginTransaction();

        try
        {

            $ano = $this->repository->findById($anoId);

            $nom = $ano->dossier;

            //Email de rappel
            dispatch(new SendEmailJob($ano->bailleur->user, "rappel-ano", "Rappel de traitement de la demande d'ano {$nom}"))->delay(now()->addSeconds(15));

            $acteur = Auth::check() ? Auth::user()->nom . " ". Auth::user()->prenom : "Inconnu";

            $message = $message ?? Str::ucfirst($acteur) . " a un eemail de rappel au bailleur pour le demande d'ano " . $ano->dossier;

            //LogActivity::addToLog("Rappel pour la demande d'ano", $message, get_class($ano), $ano->id);

            DB::commit();

            return response()->json(['statut' => 'success', 'message' => null, 'data' => $ano, 'statutCode' => Response::HTTP_OK], Response::HTTP_OK);
        }
        catch (\Throwable $th)
        {
            DB::rollback();
            return response()->json(['statut' => 'error', 'message' => $th->getMessage(), 'errors' => []], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    public function reponses($id): JsonResponse
    {

        try {

            $ano = Ano::findByKey($id);

            if(!$ano) throw new Exception("Ano inconnue", 1);

            $reponses = $ano->reponsesAno;

            return response()->json(['statut' => 'success', 'message' => null, 'data' => ReponseAnoResource::collection($reponses), 'statutCode' => Response::HTTP_OK], Response::HTTP_OK);

        } catch (\Throwable $th) {

            DB::rollBack();

            //throw $th;
            return response()->json(['statut' => 'error', 'message' => $th->getMessage(), 'errors' => [], 'statutCode' => Response::HTTP_INTERNAL_SERVER_ERROR], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
}
