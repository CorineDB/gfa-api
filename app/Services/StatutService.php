<?php

namespace App\Services;

use App\Models\Bailleur;
use App\Repositories\StatutRepository;
use App\Repositories\ComposanteRepository;
use App\Repositories\ActiviteRepository;
use App\Repositories\TacheRepository;
use App\Traits\Helpers\Pta;
use Core\Services\Contracts\BaseService;
use Core\Services\Interfaces\StatutServiceInterface;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;
use Exception;
use Illuminate\Support\Facades\Auth;

/**
* Interface StatutServiceInterface
* @package Core\Services\Interfaces
*/
class StatutService extends BaseService implements StatutServiceInterface
{

    use Pta;
    /**
     * @var service
     */
    protected $repository, $composanteRepository, $activiteRepository, $tacheRepository;

    /**
     * suiviService constructor.
     *
     * @param StatutRepository $statutRepository
     */
    public function __construct(StatutRepository $statutRepository,
                                ComposanteRepository $composanteRepository,
                                ActiviteRepository $activiteRepository,
                                TacheRepository $tacheRepository)
    {
        parent::__construct($statutRepository);
        $this->repository = $statutRepository;
        $this->composanteRepository = $composanteRepository;
        $this->activiteRepository = $activiteRepository;
        $this->tacheRepository = $tacheRepository;
    }

    public function create(array $attributs) : JsonResponse
    {
        DB::beginTransaction();

        try
        {
            if(!(array_key_exists('composanteId', $attributs)) &&
               !(array_key_exists('activiteId', $attributs)) &&
               !(array_key_exists('tacheId', $attributs))) throw new Exception( "Aucune rubrique choisis pour le statut", 500);

            if(array_key_exists('composanteId', $attributs))
            {
                if(!($composante = $this->composanteRepository->findById($attributs['composanteId']))) throw new Exception( "Cette composante n'existe pas", 500);
                if(!($composante->statut >= $attributs['statut'])) throw new Exception( "Le statut est déja {$composante->statut}", 500);

                $user = Auth::user();
                if($user->type == 'bailleur')
                {
                    $bailleur = Bailleur::where('userId', $user->id)->get();

                    if(!isset($bailleur)) throw new Exception( "Bailleur non retrouver", 500);

                    if($bailleur != $composante->bailleur) throw new Exception( "Ce bailleur n'est pas concerné par cette composante", 500);
                }

                if($attributs['statut'] > -2)
                {
                    if($composante->composanteId == 0)
                    {
                        $composante->position = $this->position($composante->projet, 'composantes');
                        $composante->save();
                    }

                    else
                    {
                        $composante->position = $this->position($composante->composante, 'sousComposantes');
                        $composante->save();
                    }

                }

                $statut = $composante->statuts()->create($attributs);
            }


            else if(array_key_exists('activiteId', $attributs))
            {
                if(!($activite = $this->activiteRepository->findById($attributs['activiteId']))) throw new Exception( "Cette tache n'existe pas", 500);
                if(!($activite->statut >= $attributs['statut'])) throw new Exception( "Le statut est déja {$activite->statut}", 500);

                $user = Auth::user();
                if($user->type == 'bailleur')
                {
                    $bailleur = Bailleur::where('userId', $user->id)->get();

                    if(!isset($bailleur)) throw new Exception( "Bailleur non retrouver", 500);

                    if($bailleur != $activite->bailleur) throw new Exception( "Ce bailleur n'est pas concerné par cette activite", 500);
                }

                if($attributs['statut'] > -2)
                {
                    $activite->position = $this->position($activite->composante, 'activites');
                    $activite->save();
                }

                $statut = $activite->statuts()->create($attributs);
            }


            else if(array_key_exists('tacheId', $attributs))
            {
                if(!($tache = $this->tacheRepository->findById($attributs['tacheId']))) throw new Exception( "Cette tache n'existe pas", 500);
                if(!($tache->statut >= $attributs['statut'])) throw new Exception( "Le statut est déja {$tache->statut}", 500);

                $user = Auth::user();
                if($user->type == 'bailleur')
                {
                    $bailleur = Bailleur::where('userId', $user->id)->get();

                    if(!isset($bailleur)) throw new Exception( "Bailleur non retrouver", 500);

                    if($bailleur != $tache->bailleur) throw new Exception( "Ce bailleur n'est pas concerné par cette tache", 500);
                }

                if($attributs['statut'] > -2)
                {
                    $tache->position = $this->position($tache->activite, 'taches');
                    $tache->save();
                }

                $statut = $tache->statuts()->create($attributs);
            }

            DB::commit();
            return response()->json(['statut' => 'success', 'message' => null, 'data' => $statut, 'statutCode' => Response::HTTP_OK], Response::HTTP_OK);
        }
        catch (\Throwable $th)
        {
            DB::rollback();
            return response()->json(['statut' => 'error', 'message' => $th->getMessage(), 'errors' => []], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
}
