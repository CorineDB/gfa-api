<?php

namespace App\Services;

use App\Http\Resources\anos\AnosResource;
use App\Http\Resources\bailleurs\BailleursResource;
use App\Http\Resources\indicateur\IndicateurResource;
use App\Http\Resources\suivi\SuivisIndicateurResource;
use App\Http\Resources\user\UtilisateurResource;
use App\Jobs\SendEmailJob;
use App\Models\Bailleur;
use App\Models\EntrepriseExecutant;
use App\Models\EntrepriseExecutantSite;
use App\Models\Programme;
use App\Models\User;
use App\Repositories\RoleRepository;
use App\Repositories\BailleurRepository;
use App\Repositories\UserRepository;
use App\Traits\Eloquents\DBStatementTrait;
use App\Traits\Helpers\HelperTrait;
use App\Traits\Helpers\IdTrait;
use App\Traits\Helpers\LogActivity;
use Carbon\Carbon;
use Core\Services\Contracts\BaseService;
use Core\Services\Interfaces\BailleurServiceInterface;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

/**
* Interface BailleurServiceInterface
* @package Core\Services\Interfaces
*/
class BailleurService extends BaseService implements BailleurServiceInterface
{
    use IdTrait, DBStatementTrait, HelperTrait;

    /**
     * @var service
     */
    protected $repository, $roleRepository, $userRepository;

    /**
     * BailleurService constructor.
     *
     * @param BailleurRepository $bailleurRepository
     */
    public function __construct(BailleurRepository $bailleurRepository, UserRepository $userRepository, RoleRepository $roleRepository)
    {
        parent::__construct($bailleurRepository);
        $this->repository = $bailleurRepository;
        $this->userRepository = $userRepository;
        $this->roleRepository = $roleRepository;
    }

    public function all(array $columns = ['*'], array $relations = []): JsonResponse
    {

        try {

            $programme = Auth::user()->programme;

            //$bailleurs = BailleursResource::collection($this->repository->all());

            $bailleurs = $programme->getBailleurs();

            return response()->json(['statut' => 'success', 'message' => null, 'data' => BailleursResource::collection($bailleurs), 'statutCode' => Response::HTTP_OK], Response::HTTP_OK);

        } catch (\Throwable $th) {

            DB::rollBack();

            //throw $th;
            return response()->json(['statut' => 'error', 'message' => $th->getMessage(), 'errors' => [], 'statutCode' => Response::HTTP_INTERNAL_SERVER_ERROR], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    public function create(array $attributs) : JsonResponse
    {
        DB::beginTransaction();

        try {

            $attributs = array_merge($attributs, ['programmeId' => Auth::user()->programme->id]);


            $role = $this->roleRepository->findByAttribute('slug', 'bailleur');

            $password = strtoupper($this->hashId(4)); // Générer le mot de passe

            /*if( !array_key_exists('programmeId', $attributs) || !isset($attributs['programmeId']) )
            {
                $this->changeState(0);
            }*/

            $bailleur = $this->repository->fill($attributs);

            $bailleur->save();

            //$bailleur = $this->repository->fill(["sigle" => $attributs['sigle'], "pays" => $attributs['pays']]);

            $bailleur->user()->create(array_merge($attributs, ['password' => $password, 'type' => $role->slug, 'profilable_type' => get_class($bailleur), 'profilable_id' => $bailleur->id]));

           /* if( !array_key_exists('programmeId', $attributs) || !isset($attributs['programmeId']) )
            {
                $this->changeState(1);
            }*/

            $bailleur->user->roles()->attach([$role->id]);

            if( isset($attributs['code']) && isset($attributs['programmeId']) ){
                $bailleur->codes()->create(
                    ['codePta' => $attributs['code'], 'programmeId' => $attributs['programmeId']]
                );
            }

            if(isset($attributs['logo']))
            {
                $id = $bailleur->secure_id;

                $this->storeFile($attributs['logo'], "logos/bailleurs/{$id}", $bailleur->user, null, 'logo');
            }

            $bailleur = $bailleur->fresh();

            $utilisateur = $bailleur->user;

            $utilisateur->account_verification_request_sent_at = Carbon::now();

            $utilisateur->token = str_replace(['/', '\\'], '', Hash::make( $utilisateur->secure_id . Hash::make($utilisateur->email) . Hash::make(Hash::make(strtotime($utilisateur->account_verification_request_sent_at)))));

            $utilisateur->link_is_valide = true;

            $utilisateur->save();

            DB::commit();

            //Envoyer les identifiants de connexion à l'utilisateur via son email
            dispatch(new SendEmailJob($bailleur->user, "confirmation-de-compte", $password))->delay(now()->addSeconds(15));

            $acteur = Auth::check() ? Auth::user()->nom . " ". Auth::user()->prenom : "Inconnu";

            $message = Str::ucfirst($acteur) . " a créé le compte admin du bailleur {$bailleur->nom}.";

            //LogActivity::addToLog("Enrégistrement", $message, get_class($bailleur), $bailleur->id);

            return response()->json(['statut' => 'success', 'message' => "Création du compte bailleur réussir", 'data' => new BailleursResource($bailleur), 'statutCode' => Response::HTTP_OK], Response::HTTP_OK);

        } catch (\Throwable $th) {

            DB::rollBack();

            //throw $th;
            return response()->json(['statut' => 'error', 'message' => $th->getMessage(), 'errors' => [], 'statutCode' => Response::HTTP_INTERNAL_SERVER_ERROR], Response::HTTP_INTERNAL_SERVER_ERROR);
        }

    }

    public function update($bailleurId, array $attributs) : JsonResponse
    {
        DB::beginTransaction();

        try {

            $bailleur = $this->repository->findById($bailleurId);

            if(array_key_exists('nom', $attributs))
            {
                if(User::where('nom', $attributs['nom'])->where('profilable_id', '!=', $bailleur->id)->count()) throw new Exception("Ce nom est déja utilisé", 401);
            }

            if(array_key_exists('contact', $attributs))
            {
                if(User::where('contact', $attributs['contact'])->where('profilable_id', '!=', $bailleur->id)->count()) throw new Exception("Ce contact est déja utilisé", 401);
            }

            if(array_key_exists('sigle', $attributs))
            {
                if(Bailleur::where('sigle', $attributs['sigle'])->where('id', '!=', $bailleur->id)->count()) throw new Exception("Ce sigle est déja utilisé", 401);
            }



            unset($attributs['email']);

            unset($attributs['code']);

            unset($attributs['programmeId']);

            $bailleur->user->fill($attributs)->save();

            $bailleur->fill($attributs)->save();

            if(array_key_exists('logo', $attributs))
            {

                $id = $bailleur->secure_id;

                $old_image = $bailleur->user->logo;

                $this->storeFile($attributs['logo'], "logos/bailleurs/{$id}", $bailleur->user, null, 'logo');

                if($old_image != null){

                    unlink(public_path("storage/" . $old_image->chemin));

                    $old_image->delete();
                }
            }

            //$programme = Auth::user()->uniteeDeGestion->programme;

            /*if( isset($attributs['code']) && isset($attributs['programmeId']) ){
                optional($bailleur->codes($attributs['programmeId']))->update(['codePta' => $attributs['code']]);
            }*/

            //$bailleur = $bailleur->fresh();
            $bailleur->refresh();

            DB::commit();

            $acteur = Auth::check() ? Auth::user()->nom . " ". Auth::user()->prenom : "Inconnu";

            $message = Str::ucfirst($acteur) . " a modifié le compte du bailleur {$bailleur->nom}.";

            //LogActivity::addToLog("Modification", $message, get_class($bailleur), $bailleur->id);

            return response()->json(['statut' => 'success', 'message' => "Compte bailleur modifié", 'data' => new BailleursResource($bailleur), 'statutCode' => Response::HTTP_OK], Response::HTTP_OK);

        } catch (\Throwable $th) {

            DB::rollBack();

            //throw $th;
            return response()->json(['statut' => 'error', 'message' => $th->getMessage(), 'errors' => [], 'statutCode' => Response::HTTP_INTERNAL_SERVER_ERROR], Response::HTTP_INTERNAL_SERVER_ERROR);
        }

    }

    public function anos() : JsonResponse
    {
        try
        {

            $bailleur = Auth::user()->profilable;

            $anos = $bailleur->anos ;

            return response()->json(['statut' => 'success', 'message' => null, 'data' => AnosResource::collection($anos), 'statutCode' => Response::HTTP_OK], Response::HTTP_OK);
        }
        catch (\Throwable $th)
        {
            return response()->json(['statut' => 'error', 'message' => $th->getMessage(), 'errors' => []], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    public function indicateurs() : JsonResponse
    {
        try
        {

            $bailleur = Auth::user()->profilable;

            $indicateurs = $bailleur->indicateurs ;

            return response()->json(['statut' => 'success', 'message' => null, 'data' => IndicateurResource::collection($indicateurs), 'statutCode' => Response::HTTP_OK], Response::HTTP_OK);
        }
        catch (\Throwable $th)
        {
            return response()->json(['statut' => 'error', 'message' => $th->getMessage(), 'errors' => []], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    public function entreprisesExecutant() : JsonResponse
    {
        try
        {
            $entreprisesExecutant = [];

            $bailleur = Auth::user()->profilable;

            return response()->json(['statut' => 'success', 'message' => null, 'data' => UtilisateurResource::collection($bailleur->entrepriseExecutants()), 'statutCode' => Response::HTTP_OK], Response::HTTP_OK);
        }
        catch (\Throwable $th)
        {
            return response()->json(['statut' => 'error', 'message' => $th->getMessage(), 'errors' => []], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    public function suiviIndicateurs() : JsonResponse
    {
        try
        {
            $suivis = [];

            $bailleur = Auth::user()->profilable;

            $indicateurs = $bailleur->indicateurs;

            foreach($indicateurs as $indicateur)
            {
                array_push($suivis, ($indicateur->suivis));
            }

            //dd($suivis);

            return response()->json(['statut' => 'success', 'message' => null, 'data' => $suivis, 'statutCode' => Response::HTTP_OK], Response::HTTP_OK);
        }
        catch (\Throwable $th)
        {
            return response()->json(['statut' => 'error', 'message' => $th->getMessage(), 'errors' => []], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

}
