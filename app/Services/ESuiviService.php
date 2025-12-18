<?php

namespace App\Services;

use App\Http\Resources\FormulaireResource;
use App\Models\Activite;
use App\Models\EActivite;
use App\Models\EActiviteStatut;
use App\Models\EntrepriseExecutant;
use App\Models\ESuivi;
use App\Models\Formulaire;
use App\Models\MissionDeControle;
use App\Models\MOD;
use App\Models\Reponse;
use App\Models\UniteeDeGestion;
use App\Models\User;
use App\Repositories\ESuiviRepository;
use App\Traits\Helpers\LogActivity;
use Core\Services\Contracts\BaseService;
use Core\Services\Interfaces\ESuiviServiceInterface;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

/**
* Interface ESuiviServiceInterface
* @package Core\Services\Interfaces
*/
class ESuiviService extends BaseService implements ESuiviServiceInterface
{

    /**
     * @var service
     */
    protected $repository, $userRepository;

    /**
     * ESuiviRepository constructor.
     *
     * @param ESuiviRepository $eSuivi
     */
    public function __construct(ESuiviRepository $eSuivi)
    {
        parent::__construct($eSuivi);
        $this->repository = $eSuivi;
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

            $auteur = Auth::user()->profilable;

            $entreprise = EntrepriseExecutant::find($attributs['entrepriseExecutantId']);

            if(!($entreprise->site($attributs['siteId']))) throw new Exception("L'entreprise n'intervient pas sur ce site", 1);

            $eSuivi = $auteur->esuivis()->create($attributs);

            if(array_key_exists('commentaire', $attributs))
            {
                $eSuivi->commentaires()->create(['contenu' => $attributs['commentaire'], 'auteurId' => Auth::user()->id]);
            }

            $activite = EActivite::find($attributs['activiteId']);

            $statut = EActiviteStatut::create([
                'etat' => $attributs['activiteStatut'],
                'activiteId' => $activite->id,
                'entrepriseId' => $entreprise->id,
                'date' => $attributs['date']
            ]);

            $acteur = Auth::check() ? Auth::user()->nom . " ". Auth::user()->prenom : "Inconnu";

            $message = $message ?? Str::ucfirst($acteur) . " a créé un " . strtolower(class_basename($eSuivi));

            //LogActivity::addToLog("Enregistrement", $message, get_class($eSuivi), $eSuivi->id);

            DB::commit();

            return response()->json(['statut' => 'success', 'message' => null, 'data' => $eSuivi, 'statutCode' => Response::HTTP_OK], Response::HTTP_OK);

        } catch (\Throwable $th) {

            DB::rollback();

            //throw $th;
            return response()->json(['statut' => 'error', 'message' => $th->getMessage(), 'errors' => []], Response::HTTP_INTERNAL_SERVER_ERROR);
        }

    }



    public function update($eSuivi, array $attributs) : JsonResponse
    {

        DB::beginTransaction();

        try {

            if(is_string($eSuivi))
            {
                $eSuivi = $this->repository->findById($eSuivi);
            }
            else{
                $eSuivi = $eSuivi;
            }
            $user = Auth::user();

            $attributs = array_merge($attributs, ['userId' => $user->id]);

            $eSuivi = $eSuivi->fill($attributs);

            $eSuivi->save();

            if(isset($attributs['commentaire']))
            {
                $eSuivi->commentaires()->create(['contenu' => $attributs['commentaire'], 'auteurId' => Auth::user()->id]);
            }

            $activite = EActivite::find($attributs['activiteId']);

            $activite->statuts()->create(['etat' => $attributs['activiteStatut']]);

            $acteur = Auth::check() ? Auth::user()->nom . " ". Auth::user()->prenom : "Inconnu";

            $message = $message ?? Str::ucfirst($acteur) . " a modifié un " . strtolower(class_basename($eSuivi));

            //LogActivity::addToLog("Modification", $message, get_class($eSuivi), $eSuivi->id);

            DB::commit();

            return response()->json(['statut' => 'success', 'message' => "Donnée modifié", 'data' => [], 'statutCode' => Response::HTTP_OK], Response::HTTP_OK);

        } catch (\Throwable $th) {

            DB::rollback();

            //throw $th;
            return response()->json(['statut' => 'error', 'message' => $th->getMessage(), 'errors' => []], Response::HTTP_INTERNAL_SERVER_ERROR);
        }

    }

    public function dates(array $attributs) : JsonResponse
    {

        try {

            switch ($attributs['type']) {
                case 'unitee-de-gestion':
                    $type = UniteeDeGestion::findByKey($attributs['typeId']);
                    if(!$type) throw new Exception("Unitee de gestion inconnue", 1);
                    break;

                case 'mod':
                    $type = MOD::findByKey($attributs['typeId']);
                    if(!$type) throw new Exception("Mod inconnue", 1);
                    break;

                case 'mission-de-controle':
                    $type = MissionDeControle::findByKey($attributs['typeId']);
                    if(!$type) throw new Exception("Mission de controle inconnue", 1);
                    break;

                case 'entreprise':
                    $type = EntrepriseExecutant::findByKey($attributs['typeId']);
                    if(!$type) throw new Exception("Entreprise inconnue", 1);
                    break;

                case 'general':
                    $type = User::findByKey($attributs['typeId']);
                    if(!$type) throw new Exception("User inconnue", 1);
                    break;

                default:
                    throw new Exception("Type inconnue", 1);
                    break;
            }

            if($attributs['type'] == 'general')
            {
                $suivis  = Reponse::select('date', 'userId')->where('formulaireId', $attributs['formulaireId'])
                                             ->where('userId', $type->id)
                                             ->orWhere('shared', 'like', '%'.$type->id.'%')
                                             ->distinct()
                                             ->orderBy('date', 'desc')
                                             ->get();
                foreach($suivis as $key => $suivi)
                {
                    $user = User::find($suivi['userId']);

                    $suivis[$key] = array_merge($suivis[$key]->toArray(), ['userId' => $user->nom." ".$user->prenoms]);

                }
            }

            else
            {
                $suivis  = ESuivi::select('date')->where('formulaireId', $attributs['formulaireId'])
                                             ->where('auteurable_id', $type->id)
                                             ->where('auteurable_type', get_class($type))
                                             ->distinct()
                                             ->orderBy('date', 'desc')
                                             ->get();
            }

            return response()->json(['statut' => 'success', 'message' => "", 'data' => $suivis, 'statutCode' => Response::HTTP_OK], Response::HTTP_OK);

        } catch (\Throwable $th) {

            //throw $th;
            return response()->json(['statut' => 'error', 'message' => $th->getMessage(), 'errors' => []], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    public function formulaires(array $attributs) : JsonResponse
    {

        try {

            switch ($attributs['type']) {
                case 'unitee-de-gestion':
                    $type = UniteeDeGestion::findByKey($attributs['typeId']);
                    if(!$type) throw new Exception("Unitee de gestion inconnue", 1);
                    break;

                case 'mod':
                    $type = MOD::findByKey($attributs['typeId']);
                    if(!$type) throw new Exception("Mod inconnue", 1);
                    break;

                case 'mission-de-controle':
                    $type = MissionDeControle::findByKey($attributs['typeId']);
                    if(!$type) throw new Exception("Mission de controle inconnue", 1);
                    break;

                case 'entreprise':
                    $type = EntrepriseExecutant::findByKey($attributs['typeId']);
                    if(!$type) throw new Exception("Entreprise inconnue", 1);
                    break;

                default:
                    throw new Exception("Type inconnue", 1);
                    break;
            }

            $suivis  = ESuivi::select('formulaireId')->where('auteurable_id', $type->id)
                                             ->where('auteurable_type', get_class($type))
                                             ->where('entrepriseExecutantId', $attributs['entrepriseExecutantId'])
                                             ->distinct()
                                             ->get();

            $formulaires = [];

            foreach($suivis as $suivi)
            {
                array_push($formulaires, new FormulaireResource(Formulaire::find($suivi->formulaireId)));

            }

            return response()->json(['statut' => 'success', 'message' => null, 'data' => $formulaires, 'statutCode' => Response::HTTP_OK], Response::HTTP_OK);

        } catch (\Throwable $th) {

            //throw $th;
            return response()->json(['statut' => 'error', 'message' => $th->getMessage(), 'errors' => []], Response::HTTP_INTERNAL_SERVER_ERROR);
        }

    }

    public function graphes($attributs) : JsonResponse
    {
        try
        {
            $data = [];
            $formulaire = Formulaire::findByKey($attributs['formulaireId']);

            if($formulaire)
            {
                $entreprises = $formulaire->programme->entreprisesExecutante;
                $checklists = $formulaire->checkLists;

                foreach($checklists as $checklist)
                {
                    if($checklist->unitee->type != 1) continue;

                    $noms = [];
                    $valeurs = [];

                    foreach($entreprises as $entreprise)
                    {
                        $suivi = ESuivi::where('formulaireId', $formulaire->id)
                                       ->where('date', $attributs['date'])
                                       ->where('checkListId', $checklist->id)
                                       ->where('entrepriseExecutantId', $entreprise->profilable->id)
                                       ->first();

                        if($suivi)
                        {
                            array_push($valeurs, $suivi->valeur);
                            array_push($noms, $entreprise->nom);
                        }

                        else
                        {
                            array_push($valeurs, 0);
                            array_push($noms, $entreprise->nom);
                        }
                    }

                    array_push($data, [
                        'checklist' => $checklist->nom,
                        'entreprises' => $noms,
                        'valeurs' => $valeurs
                    ]);
                }
            }

            else $data = null;

            return response()->json(['statut' => 'success', 'message' => null, 'data' => $data, 'statutCode' => Response::HTTP_OK], Response::HTTP_OK);
        }

        catch (\Throwable $th)
		{
		    return response()->json(['statut' => 'error', 'message' => $th->getMessage(), 'errors' => []], Response::HTTP_INTERNAL_SERVER_ERROR);
		}

    }

}
