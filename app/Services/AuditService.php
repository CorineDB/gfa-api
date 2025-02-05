<?php

namespace App\Services;

use App\Events\NewNotification;
use App\Http\Resources\anos\AuditsResource;
use App\Http\Resources\AuditResource;
use App\Models\Audit;
use App\Models\Projet;
use App\Models\User;
use App\Notifications\FichierNotification;
use App\Repositories\AuditRepository;
use App\Traits\Eloquents\DBStatementTrait;
use App\Traits\Helpers\HelperTrait;
use App\Traits\Helpers\LogActivity;
use Core\Services\Contracts\BaseService;
use Core\Services\Interfaces\AuditServiceInterface;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;
use Exception;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

/**
* Interface AuditServiceInterface
* @package Core\Services\Interfaces
*/
class AuditService extends BaseService implements AuditServiceInterface
{

    use  HelperTrait, DBStatementTrait;
    /**
     * @var service
     */
    protected $repository;

    /**
     * AuditService constructor.
     *
     * @param AuditRepository $anoRepository
     */
    public function __construct(AuditRepository $anoRepository)
    {
        parent::__construct($anoRepository);
        $this->repository = $anoRepository;
    }

    public function all(array $columns = ['*'], array $relations = []): JsonResponse
    {

        try {

            $audits = [];

            foreach($this->repository->all() as $audit)
            {
                if($audit->projet->programme->id  != Auth::user()->programmeId) continue;

                array_push($audits, $audit);
            }

            return response()->json(['statut' => 'success', 'message' => null, 'data' => AuditResource::collection($audits), 'statutCode' => Response::HTTP_OK], Response::HTTP_OK);

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

            $projet = Projet::findByKey($attributs['projetId']);

            if($projet == null) throw new Exception("Ce projet n'existe pas", 500);

            $attributs = array_merge($attributs, ['projetId' => $projet->id, 'programmeId' => auth()->user()->programmeId]);

            switch ($attributs['categorie']) {
                case 0:
                    $attributs = array_merge($attributs, ['categorie' => "Audit comptable et financier"]);
                    break;

                case 1:
                    $attributs = array_merge($attributs, ['categorie' => "Audit de conformité environnementale et social"]);
                    break;

                case 2:
                    $attributs = array_merge($attributs, ['categorie' => "Audit des acquisitions"]);
                    break;

                case 3:
                    $attributs = array_merge($attributs, ['categorie' => "Audit techniques"]);
                    break;

                default:
                    # code...
                    break;
            }

            $audit = $this->repository->create($attributs);

            //$fichier = $this->storeFile($attributs['rapport'], 'audit', $audit, null, 'fichier');

            if(array_key_exists('fichier', $attributs))
            {
                if($attributs['fichier']->getClientOriginalExtension() != 'jpg'  &&
                   $attributs['fichier']->getClientOriginalExtension() != 'png' &&
                   $attributs['fichier']->getClientOriginalExtension() != 'jpeg' &&
                   $attributs['fichier']->getClientOriginalExtension() != 'docx' &&
                   $attributs['fichier']->getClientOriginalExtension() != 'pdf')
                    throw new Exception("Le fichier doit être au format jpg, png, jpeg, docx ou pdf", 500);

                $fichier = $this->storeFile($attributs['fichier'], 'audits', $audit, null, 'fichier');

                if(array_key_exists('sharedId', $attributs))
                {
                    foreach($attributs['sharedId'] as $id)
                    {
                        $user = User::findByKey($id);

                        if($user)
                        {
                            $this->storeFile($attributs['fichier'], 'audits', $audit, null, 'fichier', ['fichierId' => $fichier->id, 'userId' => $user->id]);
                        }

                        $data['texte'] = "Un fichier vient d'etre partagé avec vous dans le dossier audit";
                        $data['id'] = $fichier->id;
                        $data['auteurId'] = Auth::user()->id;
                        $notification = new FichierNotification($data);

                        $user->notify($notification);

                        $notification = $user->notifications->last();

                        event(new NewNotification($this->formatageNotification($notification, $user)));
                    }
                }
            }

            $acteur = Auth::check() ? Auth::user()->nom . " ". Auth::user()->prenom : "Inconnu";

            $message = $message ?? Str::ucfirst($acteur) . " a créé un " . strtolower(class_basename($audit));

            //LogActivity::addToLog("Enrégistrement", $message, get_class($audit), $audit->id);

            DB::commit();

            return response()->json(['statut' => 'success', 'message' => null, 'data' => new $audit, 'statutCode' => Response::HTTP_OK], Response::HTTP_OK);
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

            if(!($audit = $this->repository->findById($id))) throw new Exception( "Ce audit n'existe pas", 500);

            if(array_key_exists('projetId', $attributs))
            {
                $projet = Projet::findByKey($attributs['projetId']);

                if($projet == null) throw new Exception("Ce projet n'existe pas", 500);

                $attributs = array_merge($attributs, ['projetId' => $projet->id]);
            }

            if(array_key_exists('categorie', $attributs))
            {

                switch ($attributs['categorie']) {
                    case 0:
                        $attributs = array_merge($attributs, ['categorie' => "Audit comptable et financier"]);
                        break;

                    case 1:
                        $attributs = array_merge($attributs, ['categorie' => "Audit de conformité environnementale et social"]);
                        break;

                    case 2:
                        $attributs = array_merge($attributs, ['categorie' => "Audit des acquisitions"]);
                        break;

                    case 3:
                        $attributs = array_merge($attributs, ['categorie' => "Audit techniques"]);
                        break;

                    default:
                        # code...
                        break;
                }
            }

            $audit = $audit->fill($attributs);
            $audit->save();

            /*if(array_key_exists('rapport', $attributs))
            {
                $fichier = $this->storeFile($attributs['rapport'], 'audit', $audit, null, 'fichier');
            }*/



            DB::commit();
            return response()->json(['statut' => 'success', 'message' => null, 'data' => $audit, 'statutCode' => Response::HTTP_OK], Response::HTTP_OK);
        }
        catch (\Throwable $th)
        {
            DB::rollback();
            return response()->json(['statut' => 'error', 'message' => $th->getMessage(), 'errors' => []], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }


}
