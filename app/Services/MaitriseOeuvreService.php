<?php

namespace App\Services;

use App\Http\Resources\MaitriseOeuvreResource;
use App\Models\EntrepriseExecutant;
use App\Repositories\MaitriseOeuvreRepository;
use App\Repositories\UserRepository;
use App\Traits\Helpers\LogActivity;
use Core\Services\Contracts\BaseService;
use Core\Services\Interfaces\MaitriseOeuvreServiceInterface;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

/**
* Interface MaitriseOeuvreServiceInterface
* @package Core\Services\Interfaces
*/
class MaitriseOeuvreService extends BaseService implements MaitriseOeuvreServiceInterface
{


    /**
     * @var service
     */
    protected $repository, $userRepository;

    /**
     * MaitriseOeuvreRepository constructor.
     *
     * @param MaitriseOeuvreRepository $maitriseOeuvreRepository
     */
    public function __construct(MaitriseOeuvreRepository $maitriseOeuvreRepository, UserRepository $userRepository)
    {
        parent::__construct($maitriseOeuvreRepository);
        $this->repository = $maitriseOeuvreRepository;
        $this->userRepository = $userRepository;
    }



    /**
     * Création d'une maitrise d'oeuvre'
     *
     *
     */
    public function create($attributs) : JsonResponse
    {
        DB::beginTransaction();

        try {

            $attributs = array_merge($attributs, ['programmeId' => Auth::user()->programmeId]);

            $maitriseOeuvre = $this->repository->fill($attributs);

            $maitriseOeuvre->save();

            $entreprises = [];

            foreach($attributs['attributaire'] as $id)
            {
                $entreprise = EntrepriseExecutant::findByKey($id);
                if(Auth::user()->programmeId != $entreprise->user->programmeId) throw new Exception( "Cet entreprise n'est pas dans le programme", 500);
                array_push($entreprises, $entreprise->id);
            }

            $maitriseOeuvre->entrepriseExecutants()->attach($entreprises);

            if(array_key_exists('commentaire', $attributs))
            {
                $maitriseOeuvre->commentaires->create(['contenu' => $attributs['commentaire']]);
            }

            $acteur = Auth::check() ? Auth::user()->nom . " ". Auth::user()->prenom : "Inconnu";

            $message = $message ?? Str::ucfirst($acteur) . " a créé un " . strtolower(class_basename($maitriseOeuvre));

            //LogActivity::addToLog("Modification", $message, get_class($maitriseOeuvre), $maitriseOeuvre->id);

            DB::commit();

            return response()->json(['statut' => 'success', 'message' => "Maitrise d'oeuvre crée", 'data' => new MaitriseOeuvreResource($maitriseOeuvre), 'statutCode' => Response::HTTP_OK], Response::HTTP_OK);

        } catch (\Throwable $th) {

            DB::rollback();

            //throw $th;
            return response()->json(['statut' => 'error', 'message' => $th->getMessage(), 'errors' => []], Response::HTTP_INTERNAL_SERVER_ERROR);
        }

    }



    public function update($maitriseOeuvre, array $attributs) : JsonResponse
    {

        DB::beginTransaction();

        try {


            if(is_string($maitriseOeuvre))
            {
                $maitriseOeuvre = $this->repository->findById($maitriseOeuvre);
            }
            else{
                $maitriseOeuvre = $maitriseOeuvre;
            }

            $maitriseOeuvre = $maitriseOeuvre->fill($attributs);

            $maitriseOeuvre->save();

            $acteur = Auth::check() ? Auth::user()->nom . " ". Auth::user()->prenom : "Inconnu";

            $message = $message ?? Str::ucfirst($acteur) . " a modifié un " . strtolower(class_basename($maitriseOeuvre));

            //LogActivity::addToLog("Modification", $message, get_class($maitriseOeuvre), $maitriseOeuvre->id);

            DB::commit();

            return response()->json(['statut' => 'success', 'message' => "Donnée de la maitrise d'oeuvre modifié", 'data' => [], 'statutCode' => Response::HTTP_OK], Response::HTTP_OK);

        } catch (\Throwable $th) {

            DB::rollback();

            //throw $th;
            return response()->json(['statut' => 'error', 'message' => $th->getMessage(), 'errors' => []], Response::HTTP_INTERNAL_SERVER_ERROR);
        }

    }

}
