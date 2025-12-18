<?php

namespace App\Services;

use App\Events\NewNotification;
use App\Models\Projet;
use App\Repositories\DecaissementRepository;
use App\Http\Resources\DecaissementResource;
use App\Models\Bailleur;
use App\Models\Decaissement;
use App\Models\EntrepriseExecutant;
use App\Models\Gouvernement;
use App\Models\User;
use App\Notifications\CommentaireNotification;
use App\Notifications\DecaissementNotification;
use App\Traits\Helpers\HelperTrait;
use App\Traits\Helpers\LogActivity;
use Core\Services\Contracts\BaseService;
use Core\Services\Interfaces\DecaissementServiceInterface;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;
use Exception;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

/**
* Interface DecaissementServiceInterface
* @package Core\Services\Interfaces
*/
class DecaissementService extends BaseService implements DecaissementServiceInterface
{
    use HelperTrait;

    /**
     * @var service
     */
    protected $repository;

    /**
     * ProjetService constructor.
     *
     * @param Decaissement $decaissementRepository
     */
    public function __construct(DecaissementRepository $decaissementRepository)
    {
        parent::__construct($decaissementRepository);
        $this->repository = $decaissementRepository;
    }

    public function all(array $attributs = ['*'], array $relations = []): JsonResponse
	{
		try
		{
            $projets = Auth::user()->programme->projets;
            $decaissements = [];

            foreach($projets as $projet)
            {
                foreach($projet->decaissements as $decaissement)
                    array_push($decaissements, $decaissement);
            }

            return response()->json(['statut' => 'success', 'message' => null, 'data' => DecaissementResource::collection($decaissements), 'statutCode' => Response::HTTP_OK], Response::HTTP_OK);
		}

		catch (\Throwable $th)
		{
		    return response()->json(['statut' => 'error', 'message' => $th->getMessage(), 'errors' => []], Response::HTTP_INTERNAL_SERVER_ERROR);
		}
	}

    public function filtre(array $attributs): JsonResponse
    {

        try {

            if($attributs['type'])
            {
                $attributs = array_merge($attributs, ['type' => get_class(new Gouvernement())]);
            }

            else
            {
                $attributs = array_merge($attributs, ['type' => get_class(new Bailleur())]);
            }

            $decaissements = Decaissement::where('projetId', $attributs['projetId'])->
                                       where('decaissementable_type', $attributs['type'])->
                                       where('date', '>=', $attributs['debut'])->
                                       where('date', '<=', $attributs['fin'])->
                                       get();

            return response()->json(['statut' => 'success', 'message' => null, 'data' => DecaissementResource::collection($decaissements), 'statutCode' => Response::HTTP_OK], Response::HTTP_OK);

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
            if(array_key_exists('beneficiaireId', $attributs) && !($entreprise = EntrepriseExecutant::find($attributs['beneficiaireId']))) {
                throw new Exception( "Cette entreprise n'existe pas", 500);
            }

            if(array_key_exists('beneficiaireId', $attributs))
            {
                $attributs = array_merge($attributs, ['beneficiaireId' => $entreprise->id]);
            }
            
            $user = Auth::user();

            $attributs = array_merge($attributs, ['userId' => $user->id, 'programmeId' => auth()->user()->programmeId]);

            switch ($attributs['methodeDePaiement']) {
                case 0:
                    $attributs = array_merge($attributs, ['methodeDePaiement' => "Paiement Direct"]);
                    break;

                case 1:
                    $attributs = array_merge($attributs, ['methodeDePaiement' => "Avance au compte désigné"]);
                    break;

                case 2:
                    $attributs = array_merge($attributs, ['methodeDePaiement' => "Remboursement"]);
                    break;

                case 3:
                    $attributs = array_merge($attributs, ['methodeDePaiement' => "Engagement spécial"]);
                    break;

                default:
                    # code...
                    break;
            }

            if($attributs['type'])
            {
                $programme = $user->programme;
                $gouvernement = $programme->gouvernement;
                if(!$gouvernement) throw new Exception("Pas de gouvernement, veillez créer le gouvernement", 1);
                $decaissement = $gouvernement->profilable->decaissements()->create($attributs);
            }

            else
            {
                $projet = Projet::find($attributs['projetId']);
                $bailleur = $projet->bailleur;
                $decaissement = $bailleur->decaissements()->create($attributs);
            }

            if(isset($attributs['commentaire']))
            {
                $attributsCommentaire = ['contenu' => $attributs['commentaire'], 'auteurId' => Auth::id()];

                $decaissement->commentaires()->create($attributsCommentaire);

                $data['texte'] = "Un commentaire vient d'etre effectué pour un decaissement";
                $data['id'] = $decaissement->id;
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

            $message = $message ?? Str::ucfirst($acteur) . " a créé un " . strtolower(class_basename($decaissement));

            //LogActivity::addToLog("Enregistrement", $message, get_class($decaissement), $decaissement->id);

            $data['texte'] = "Un décaissement vient d'etre effectué";
            $data['id'] = $decaissement->id;
            $data['auteurId'] = Auth::user()->id;
            $notification = new DecaissementNotification($data);

            $allUsers = User::where('programmeId', Auth::user()->programmeId);
            foreach($allUsers as $user)
            {
                if($user->hasPermissionTo('voir-un-decaissement'))
                {
                    $user->notify($notification);

                    $notification = $user->notifications->last();

                    event(new NewNotification($this->formatageNotification($notification, $user)));

                }
            }

            DB::commit();
            return response()->json(['statut' => 'success', 'message' => null, 'data' => $decaissement, 'statutCode' => Response::HTTP_OK], Response::HTTP_OK);
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

            if(!($decaissement = $this->repository->findById($id))) throw new Exception( "Ce decaissement n'existe pas", 500);

            if(array_key_exists('userId', $attributs)) unset($attributs['userId']);

            if(array_key_exists('type', $attributs)) unset($attributs['type']);

            if(array_key_exists('methodeDePaiement', $attributs))
            {
                switch ($attributs['methodeDePaiement']) {
                    case 0:
                        $attributs = array_merge($attributs, ['methodeDePaiement' => "Paiement Direct"]);
                        break;

                    case 1:
                        $attributs = array_merge($attributs, ['methodeDePaiement' => "Avance au compte désigné"]);
                        break;

                    case 2:
                        $attributs = array_merge($attributs, ['methodeDePaiement' => "Remboursement"]);
                        break;

                    case 3:
                        $attributs = array_merge($attributs, ['methodeDePaiement' => "Engagement spécial"]);
                        break;

                    default:
                        # code...
                        break;
                }

            }

            $decaissement = $decaissement->fill($attributs);
            $decaissement->save();

            if(isset($attributs['commentaire']))
            {
                $attributsCommentaire = ['contenu' => $attributs['commentaire'], 'auteurId' => Auth::id()];

                $decaissement->commentaires()->create($attributsCommentaire);

                $data['texte'] = "Un commentaire vient d'etre effectué pour un decaissement";
                $data['id'] = $decaissement->id;
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

            DB::commit();
            return response()->json(['statut' => 'success', 'message' => null, 'data' => $decaissement, 'statutCode' => Response::HTTP_OK], Response::HTTP_OK);
        }
        catch (\Throwable $th)
        {
            DB::rollback();
            return response()->json(['statut' => 'error', 'message' => $th->getMessage(), 'errors' => []], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
}
