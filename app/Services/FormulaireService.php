<?php

namespace App\Services;

use App\Models\CheckList;
use App\Models\EActivite;
use App\Models\Formulaire;
use App\Models\Question;
use App\Models\Unitee;
use App\Repositories\FormulaireRepository;
use App\Http\Resources\FormulaireResource;
use App\Models\ESuivi;
use App\Models\Reponse;
use App\Traits\Helpers\LogActivity;
use Core\Services\Contracts\BaseService;
use Core\Services\Interfaces\FormulaireServiceInterface;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

/**
* Interface FormulaireServiceInterface
* @package Core\Services\Interfaces
*/
class FormulaireService extends BaseService implements FormulaireServiceInterface
{

    /**
     * @var service
     */
    protected $repository;

    /**
     * FormulaireRepository constructor.
     *
     * @param FormulaireRepository $formulaireRepository
     */
    public function __construct(FormulaireRepository $formulaireRepository)
    {
        parent::__construct($formulaireRepository);
        $this->repository = $formulaireRepository;
    }

    public function all(array $attributs = ['*'], array $relations = []): JsonResponse
	{
		try
		{
            $programme = Auth::user()->programme;
            $formulaires = Formulaire::where('programmeId', $programme->id)->where('type', 0)->get();

            return response()->json(['statut' => 'success', 'message' => null, 'data' => FormulaireResource::collection($formulaires), 'statutCode' => Response::HTTP_OK], Response::HTTP_OK);
		}

		catch (\Throwable $th)
		{
		    return response()->json(['statut' => 'error', 'message' => $th->getMessage(), 'errors' => []], Response::HTTP_INTERNAL_SERVER_ERROR);
		}
	}

    public function allGeneral(array $attributs = ['*'], array $relations = []): JsonResponse
	{
		try
		{
            $programme = Auth::user()->programme;
            $formulaires = Formulaire::where('programmeId', $programme->id)->where('type', 1)->get();

            return response()->json(['statut' => 'success', 'message' => null, 'data' => FormulaireResource::collection($formulaires), 'statutCode' => Response::HTTP_OK], Response::HTTP_OK);
		}

		catch (\Throwable $th)
		{
		    return response()->json(['statut' => 'error', 'message' => $th->getMessage(), 'errors' => []], Response::HTTP_INTERNAL_SERVER_ERROR);
		}
	}

    public function show($id) : JsonResponse
    {
        try
        {
            $formulaire = $this->repository->findById($id);

            if($formulaire)
            {
                if(!($formulaire->type)) $json = $formulaire->checkListsJson();

                else $json = $formulaire->generalJson();
            }

            else $json = null;

            return response()->json(['statut' => 'success', 'message' => null, 'data' => $json, 'statutCode' => Response::HTTP_OK], Response::HTTP_OK);
        }

        catch (\Throwable $th)
		{
		    return response()->json(['statut' => 'error', 'message' => $th->getMessage(), 'errors' => []], Response::HTTP_INTERNAL_SERVER_ERROR);
		}

    }

    public function create($attributs) : JsonResponse
    {
        DB::beginTransaction();

        try {
            $ids = [];

            foreach($attributs['json'] as $json)
            {

                if(!$attributs['type'])
                {
                    if(!($activite = EActivite::findByKey($json['activite']['id']))) continue;

                    $data = $json['data'];

                    foreach($data as $d)
                    {
                        if($d['id'])
                        {
                            if(!($checklist = CheckList::findByKey($d['id']))) continue;

                            array_push($ids, ['activiteId' => $activite->id, 'checklistId' => $checklist->id]);
                        }
                        else
                        {
                            if(!($unitee = Unitee::findByKey($d['uniteeId']))) throw new Exception("Unitée de mesure non trouvé", 1);
                            $d['uniteeId'] = $unitee->id;

                            $checklist = CheckList::create($d);
                            array_push($ids, ['activiteId' => $activite->id, 'checklistId' => $checklist->id]);
                        }
                    }

                }

                else
                {

                    if($json['id'])
                    {
                        if(!($question = Question::findByKey($json['id']))) continue;

                        array_push($ids, ['questionId' => $question->id]);
                    }
                    else
                    {
                        $question = Question::create($json);
                        array_push($ids, ['questionId' => $question->id]);
                    }

                }
            }

            if(count($ids))
            {
                $attributs = array_merge($attributs, [
                    'nom' => $attributs['nom'],
                    'auteurId' => Auth::user()->id,
                    'programmeId' => Auth::user()->programmeId,
                    'type' => $attributs['type'],
                ]);

                $formulaire = Formulaire::create($attributs);

                if($formulaire && $attributs['type'] == 0)
                {
                    foreach($ids as $key => $id)
                    {
                        $formulaire->checkLists()->attach($id['checklistId'], ['position' => $key+1, 'activiteId' => $id['activiteId']]);
                    }
                }

                else if($formulaire && $attributs['type'] == 1)
                {
                    foreach($ids as $key => $id)
                    {
                        $formulaire->questions()->attach($id['questionId'], ['position' => $key+1]);
                    }
                }

                $acteur = Auth::check() ? Auth::user()->nom . " ". Auth::user()->prenom : "Inconnu";

                $message = $message ?? Str::ucfirst($acteur) . " a créé un " . strtolower(class_basename($formulaire));

                //LogActivity::addToLog("Modification", $message, get_class($formulaire), $formulaire->id);

                DB::commit();

            }

            else
            {
                $formulaire = null;
            }


            return response()->json(['statut' => 'success', 'message' => null, 'data' =>  $formulaire, 'statutCode' => Response::HTTP_OK], Response::HTTP_OK);

        } catch (\Throwable $th) {

            DB::rollback();

            //throw $th;
            return response()->json(['statut' => 'error', 'message' => $th->getMessage(), 'errors' => []], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    public function getSuivi($attributs) : JsonResponse
    {
        try
        {
            $formulaire = $this->repository->findById($attributs['formulaireId']);

            if($formulaire)
            {
                if(!($formulaire->type))
                {
                    if(!(array_key_exists('date', $attributs)))
                    {
                        $suivi = ESuivi::where('formulaireId', $formulaire->id)->
                                         where('entrepriseExecutantId', $attributs['entrepriseId'])->
                                         orderBy('created_at', 'DESC')->
                                         first();

                        if(!$suivi) return response()->json(['statut' => 'success', 'message' => null, 'data' => [], 'statutCode' => Response::HTTP_OK], Response::HTTP_OK);

                        $attributs = array_merge($attributs, ['date' => $suivi->date]);
                    }

                    $json = $formulaire->getSuiviJson($attributs);
                }

                else
                {
                    if(!(array_key_exists('date', $attributs)))
                    {
                        $suivi = Reponse::where('formulaireId', $formulaire->id)->
                                         where('userId', $attributs['userId'])->
                                         orderBy('created_at', 'DESC')->
                                         first();

                        if(!$suivi) return response()->json(['statut' => 'success', 'message' => null, 'data' => [], 'statutCode' => Response::HTTP_OK], Response::HTTP_OK);

                        $attributs = array_merge($attributs, ['date' => $suivi->date]);
                    }

                    $json = $formulaire->getSuiviGeneralJson($attributs);
                }
            }

            else $json = null;

            return response()->json(['statut' => 'success', 'message' => null, 'data' => $json, 'statutCode' => Response::HTTP_OK], Response::HTTP_OK);
        }

        catch (\Throwable $th)
		{
		    return response()->json(['statut' => 'error', 'message' => $th->getMessage(), 'errors' => []], Response::HTTP_INTERNAL_SERVER_ERROR);
		}

    }
}
