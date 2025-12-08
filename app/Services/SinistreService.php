<?php

namespace App\Services;

use App\Http\Resources\PapResource;
use App\Models\Paye;
use App\Models\Propriete;
use App\Models\Site;
use App\Repositories\SinistreRepository;
use App\Traits\Helpers\LogActivity;
use Box\Spout\Reader\Common\Creator\ReaderEntityFactory;
use Core\Services\Contracts\BaseService;
use Core\Services\Interfaces\SinistreServiceInterface;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;
use Exception;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Spatie\SimpleExcel\SimpleExcelReader;

/**
* Interface SinistreServiceInterface
* @package Core\Services\Interfaces
*/
class SinistreService extends BaseService implements SinistreServiceInterface
{

    /**
     * @var service
     */
    protected $repository;

    /**
     * SinistreService constructor.
     *
     * @param SinistreRepository $sinistreRepository
     */
    public function __construct(SinistreRepository $sinistreRepository)
    {
        parent::__construct($sinistreRepository);
        $this->repository = $sinistreRepository;
    }

    public function create(array $attributs) : JsonResponse
    {
        DB::beginTransaction();

        try
        {
            $user = Auth::user();
            $attributs = array_merge($attributs, ['programmeId' => $user->programmeId]);
            $attributs = array_merge($attributs, ['prenoms' => $attributs['nom']]);

            if(!array_key_exists('longitude', $attributs) || $attributs['longitude'] == null)
            {
                $attributs = array_merge($attributs, ['longitude' => 0]);
            }

            if(!array_key_exists('latitude', $attributs) || $attributs['latitude'] == null)
            {
                $attributs = array_merge($attributs, ['latitude' => 0]);
            }


            $sinistre = $this->repository->create($attributs);

            /*$proprieteAttribut = [];
            $proprieteAttribut = array_merge($proprieteAttribut, [
                'nom' => $attributs['nom'],
                'longitude' => $attributs['longitude'],
                'latitude' => $attributs['latitude'],
                'montant' => $attributs['montant'],
                'sinistreId' => $sinistre->id
            ]);

            $propriete = Propriete::create($proprieteAttribut);

            $payeAttribut = [];
            $payeAttribut = array_merge($payeAttribut, [
                'proprieteId' => $propriete->id,
                'montant' => $attributs['payer'],
                'modeDePaiement' => $attributs['modeDePaiement']
            ]);

            $paye = Paye::create($payeAttribut);*/

            $acteur = Auth::check() ? Auth::user()->nom . " ". Auth::user()->prenom : "Inconnu";

            $message = $message ?? Str::ucfirst($acteur) . " a créé un " . strtolower(class_basename($sinistre));

            //LogActivity::addToLog("Enregistrement", $message, get_class($sinistre), $sinistre->id);

            DB::commit();
            return response()->json(['statut' => 'success', 'message' => null, 'data' => new PapResource($sinistre), 'statutCode' => Response::HTTP_OK], Response::HTTP_OK);
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
            $user = Auth::user();
            $attributs = array_merge($attributs, ['programmeId' => $user->programmeId]);

            $sinistre = $this->repository->update($id, $attributs);

            $sinistre = $this->repository->findById($id);

            /*$sinistre = $this->repository->findById($id);

            $propriete = $sinistre->proprietes();

            if(array_key_exists('nom', $attributs)) $propriete->nom = $attributs['nom'].' '.$attributs['prenoms'];
            if(array_key_exists('longitude', $attributs)) $propriete->longitude = $attributs['longitude'];
            if(array_key_exists('latitude', $attributs)) $propriete->latitude = $attributs['latitude'];
            if(array_key_exists('montant', $attributs)) $propriete->montant = $attributs['montant'];
            $propriete->save();*/


            $acteur = Auth::check() ? Auth::user()->nom . " ". Auth::user()->prenom : "Inconnu";

            $message = $message ?? Str::ucfirst($acteur) . " a modifié un " . strtolower(class_basename($sinistre));

            //LogActivity::addToLog("Modification", $message, get_class($sinistre), $sinistre->id);

            DB::commit();
            return response()->json(['statut' => 'success', 'message' => null, 'data' => new PapResource($sinistre), 'statutCode' => Response::HTTP_OK], Response::HTTP_OK);
        }
        catch (\Throwable $th)
        {
            DB::rollback();
            return response()->json(['statut' => 'error', 'message' => $th->getMessage(), 'errors' => []], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    public function importation(array $attributs) : JsonResponse
    {
        DB::beginTransaction();

        try
        {
            $programmeId = Auth::user()->programmeId;

            $file = $attributs['fichier'];

            if($file->getClientOriginalExtension() != 'xlsx')
                throw new Exception("Le fichier doit être au format xlsx", 500);

            $filenameWithExt = $file->getClientOriginalName();
            $filename = strtolower(str_replace(' ', '-',time() . '-'. $filenameWithExt));
            $path = "importation/".$filename;
            Storage::disk('public')->put($path, $file->getContent());

            /*$reader = SimpleExcelReader::create(public_path("storage/".$path));
            $rows = $reader->getHeaders();

            dd($rows);*/

            $reader = ReaderEntityFactory::createReaderFromFile(public_path("storage/".$path));

            $reader->open(public_path("storage/".$path));


	        foreach ($reader->getSheetIterator() as $sheet)
            {
	            foreach ($sheet->getRowIterator() as $key => $row) {
                    /* validation de l'en-tete du fichier */
                    if($key == 1)
                    {
                        $cells = $row->getCells();
                        foreach ($cells as $cellule => $cell) {
                            if($cellule+1 == 1)
                                if(strtolower($cell->getValue()) != "information sur les personnes affectées par le projet")
                                    throw new Exception("Nom de la feuille invalide, ligne {$key} ", 500);
                        }
                    }

                    else if($key == 2)
                    {
                        $cells = $row->getCells();
                        foreach ($cells as $cellule => $cell) {
                            switch ($cellule+1) {
                                case 1:
                                    if(strtolower($cell->getValue()) != "n°")
                                        throw new Exception("Cellule 1 de la ligne 2 invalide ", 500);
                                    break;
                                case 2:
                                    if(strtolower($cell->getValue()) != "n° rue")
                                        throw new Exception("Cellule 2 de la ligne 2 invalide ", 500);
                                    break;
                                case 3:
                                    if(strtolower($cell->getValue()) != "bassin")
                                        throw new Exception("Cellule 3 de la ligne 2 invalide ", 500);
                                    break;
                                case 4:
                                    if(strtolower($cell->getValue()) != "coordonnée")
                                        throw new Exception("Cellule 4 de la ligne 2 invalide ", 500);
                                    break;
                                case 6:
                                    if(strtolower($cell->getValue()) != "nom et prenom de la pap")
                                        throw new Exception("Cellule 5 de la ligne 2 invalide ", 500);
                                    break;
                                case 7:
                                   if(strtolower($cell->getValue()) != "sexe")
                                        throw new Exception("Cellule 6 de la ligne 2 invalide ", 500);
                                    break;
                                case 8:
                                    if(strtolower($cell->getValue()) != "référence pièces d’identité")
                                        throw new Exception("Cellule 7 de la ligne 2 invalide ", 500);
                                    break;
                                case 9:
                                    if(strtolower($cell->getValue()) != "contact du paiement")
                                        throw new Exception("Cellule 8 de la ligne 2 invalide ", 500);
                                    break;
                                case 10:
                                    if(strtolower($cell->getValue()) != "statut du pap")
                                        throw new Exception("Cellule 9 de la ligne 2 invalide ", 500);
                                    break;
                                case 11:
                                    if(strtolower($cell->getValue()) != "mode de paiement")
                                        throw new Exception("Cellule 10 de la ligne 2 invalide ", 500);
                                    break;
                                case 12:
                                    if(strtolower($cell->getValue()) != "montant ")
                                        throw new Exception("Cellule 11 de la ligne 2 invalide ", 500);
                                    break;
                                default:
                                    //throw new Exception("Taille de la ligne 2 invalide, cela doit contenir 11 cellule ", 500);
                                    break;
                            }


                        }
                    }

                    else if($key == 3)
                    {
                        $cells = $row->getCells();
                        foreach ($cells as $cellule => $cell) {
                            switch ($cellule+1) {
                                case 4:
                                    if(strtolower($cell->getValue()) != "x")
                                        throw new Exception("Cellule 4 de la ligne 3 invalide ", 500);
                                    break;
                                case 5:
                                    if(strtolower($cell->getValue()) != "y")
                                        throw new Exception("Cellule 5 de la ligne 3 invalide ", 500);
                                    break;
                                case 12:
                                    if(strtolower($cell->getValue()) != "indeminisation")
                                        throw new Exception("Cellule 12 de la ligne 3 invalide ", 500);
                                    break;
                                case 13:
                                    if(strtolower($cell->getValue()) != "transféré")
                                        throw new Exception("Cellule 13 de la ligne 3 invalide ", 500);
                                    break;
                                case 14:
                                    if(strtolower($cell->getValue()) != "date de paiement")
                                        throw new Exception("Cellule 14 de la ligne 3 invalide : {$cell->getValue()} ", 500);
                                    break;
                                default:
                                    //throw new Exception("Taille de la ligne 3 invalide, cela doit contenir 4 cellule ", 500);
                                    break;
                            }


                        }
                    }

                    /* fin validation en-tete*/

                    else
                    {
                        $attributs = [];
                        $user = Auth::user();
                        $attributs = array_merge($attributs, ['programmeId' => $user->programmeId]);
                        $cells = $row->getCells();
                        foreach ($cells as $cellule => $cell) {
                            switch ($cellule+1) {
                                case 2:
                                    if($cell->getValue() == "")
                                        throw new Exception("Cellule 2 de la ligne {$key} invalide ", 500);

                                    $attributs = array_merge($attributs, ['rue' => $cell->getValue()]);
                                    break;
                                case 3:
                                    if($cell->getValue() == "")
                                        throw new Exception("Cellule 3 de la ligne {$key} invalide ", 500);
                                    $site = Site::where('nom', strtoupper($cell->getValue()))->first();

                                    if(!$site)
                                        throw new Exception("Bassin introuvable, ligne {$key} ", 500);

                                    $attributs = array_merge($attributs, ['siteId' => $site->id]);
                                    break;
                                case 4:
                                    if($cell->getValue() == "")
                                        $attributs = array_merge($attributs, ['longitude' => 0]);

                                    $attributs = array_merge($attributs, ['longitude' => $cell->getValue()]);
                                    break;

                                case 5:
                                    if($cell->getValue() == "")
                                        $attributs = array_merge($attributs, ['latitude' => 0]);

                                    $attributs = array_merge($attributs, ['latitude' => $cell->getValue()]);
                                    break;
                                case 6:
                                    if($cell->getValue() == "")
                                        throw new Exception("Cellule 6 de la ligne {$key} invalide ", 500);

                                    $attributs = array_merge($attributs, ['nom' => $cell->getValue()]);
                                    $attributs = array_merge($attributs, ['prenoms' => $cell->getValue()]);
                                    break;
                                case 7:
                                    if($cell->getValue() == "")
                                        throw new Exception("Cellule 7 de la ligne {$key} invalide ", 500);

                                    $attributs = array_merge($attributs, ['sexe' => $cell->getValue()]);
                                    break;
                                case 8:
                                    if($cell->getValue() == "")
                                        throw new Exception("Cellule 8 de la ligne {$key} invalide ", 500);

                                    $attributs = array_merge($attributs, ['referencePieceIdentite' => $cell->getValue()]);
                                    break;
                                case 9:
                                    if($cell->getValue() == "")
                                        throw new Exception("Cellule 9 de la ligne {$key} invalide ", 500);

                                    $attributs = array_merge($attributs, ['contact' => $cell->getValue()]);
                                    break;
                                case 10:
                                    if($cell->getValue() == "")
                                        throw new Exception("Cellule 10 de la ligne {$key} invalide ", 500);

                                    $attributs = array_merge($attributs, ['statut' => $cell->getValue()]);
                                    break;
                                case 11:
                                    if($cell->getValue() == "")
                                        throw new Exception("Cellule 11 de la ligne {$key} invalide ", 500);

                                    $attributs = array_merge($attributs, ['modeDePaiement' => $cell->getValue()]);
                                    break;
                                case 12:
                                    if($cell->getValue() == "")
                                        throw new Exception("Cellule 12 de la ligne {$key} invalide ", 500);

                                    $attributs = array_merge($attributs, ['montant' => $cell->getValue()]);
                                    break;
                                case 13:
                                    if($cell->getValue() == "")
                                        throw new Exception("Cellule 13 de la ligne {$key} invalide ", 500);

                                    $attributs = array_merge($attributs, ['payer' => $cell->getValue()]);
                                    break;
                                case 14:
                                    if($cell->getValue() == "")
                                        throw new Exception("Cellule 14 de la ligne {$key} invalide ", 500);

                                    $attributs = array_merge($attributs, ['dateDePaiement' => $cell->getValue()]);
                                    break;
                                default:
                                    //throw new Exception("Taille de la ligne 3 invalide, cela doit contenir 4 cellule ", 500);
                                    break;
                            }
                        }

                        $sinistre = $this->repository->create($attributs);

                        /*$proprieteAttribut = [];
                        $proprieteAttribut = array_merge($proprieteAttribut, [
                            'nom' => $attributs['nom'],
                            'longitude' => $attributs['longitude'],
                            'latitude' => $attributs['latitude'],
                            'montant' => $attributs['montant'],
                            'sinistreId' => $sinistre->id
                        ]);

                        $propriete = Propriete::create($proprieteAttribut);

                        $payeAttribut = [];
                        $payeAttribut = array_merge($payeAttribut, [
                            'proprieteId' => $propriete->id,
                            'montant' => $attributs['payer'],
                            'modeDePaiement' => $attributs['modeDePaiement']
                        ]);

                        $paye = Paye::create($payeAttribut);*/


                    }

	            }
	        }

	        $reader->close();

            $acteur = Auth::check() ? Auth::user()->nom . " ". Auth::user()->prenom : "Inconnu";

            $message = $message ?? Str::ucfirst($acteur) . " a importer un fichier pour les sinistres ";

            //LogActivity::addToLog("Enregistrement", $message, get_class($sinistre), $sinistre->id);

            DB::commit();
            return response()->json(['statut' => 'success', 'message' => null, 'data' => 'importation effectué', 'statutCode' => Response::HTTP_OK], Response::HTTP_OK);

        }
        catch (\Throwable $th)
        {
            DB::rollback();

            return response()->json(['statut' => 'error', 'message' => $th->getMessage(), 'errors' => []], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
}
