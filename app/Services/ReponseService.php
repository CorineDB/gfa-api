<?php

namespace App\Services;

use App\Models\User;
use App\Repositories\ReponseRepository;
use App\Traits\Helpers\LogActivity;
use Core\Services\Contracts\BaseService;
use Core\Services\Interfaces\ReponseServiceInterface;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

/**
* Interface ReponseServiceInterface
* @package Core\Services\Interfaces
*/
class ReponseService extends BaseService implements ReponseServiceInterface
{

    /**
     * @var service
     */
    protected $repository, $userRepository;

    /**
     * ReponseRepository constructor.
     *
     * @param ReponseRepository $reponse
     */
    public function __construct(ReponseRepository $reponse)
    {
        parent::__construct($reponse);
        $this->repository = $reponse;
    }



    /**
     * Création de site
     *
     *
     */
    public function create($attributs) : JsonResponse
    {

        DB::beginTransaction();

        try {

            $attributs = array_merge($attributs, ['userId' => Auth::user()->id]);

            if(array_key_exists('shared', $attributs))
            {
                $shared = [];

                foreach($attributs['shared'] as $id)
                {
                    if($user = User::findByKey($id))
                    {
                        array_push($shared, $user->id);
                    }

                }

                $attributs = array_merge($attributs, ['shared' => implode(",", $shared)]);
            }

            $reponse = $this->repository->create($attributs);

            $acteur = Auth::check() ? Auth::user()->nom . " ". Auth::user()->prenom : "Inconnu";

            $message = $message ?? Str::ucfirst($acteur) . " a créé un " . strtolower(class_basename($reponse));

            //LogActivity::addToLog("Enregistrement", $message, get_class($reponse), $reponse->id);

            DB::commit();

            return response()->json(['statut' => 'success', 'message' => null, 'data' => $reponse, 'statutCode' => Response::HTTP_OK], Response::HTTP_OK);

        } catch (\Throwable $th) {

            DB::rollback();

            //throw $th;
            return response()->json(['statut' => 'error', 'message' => $th->getMessage(), 'errors' => []], Response::HTTP_INTERNAL_SERVER_ERROR);
        }

    }
}
