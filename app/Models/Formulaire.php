<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use SaiAshirwadInformatia\SecureIds\Models\Traits\HasSecureIds;

class Formulaire extends Model
{
    use HasFactory, HasSecureIds;

    protected $fillable = array('nom', 'auteurId', 'type', 'programmeId');

    public function checkLists()
    {
        return $this->belongsToMany(CheckList::class, 'checklist_formulaires', 'formulaireId', 'checklistId')
                    ->withPivot('activiteId')
                    ->orderByPivot('position', 'asc');
    }

    public function auteur()
    {
        return $this->belongsTo(User::class, 'auteurId');
    }

    public function programme()
    {
        return $this->belongsTo(Programme::class, 'programmeId');
    }

    public function questions()
    {
        return $this->belongsToMany(Question::class, 'formulaire_questions', 'formulaireId', 'questionId')
                    ->orderByPivot('position', 'asc');
    }

    public function checkListsJson()
    {
        $checkLists = $this->checkLists;
        $json = [];
        $eActivite = null;
        $old = 0;

        foreach($checkLists as $checkList)
        {
            $activite = EActivite::find($checkList->pivot->activiteId);
            $old = 0;

            foreach($json as $key => $j)
            {
                if($old)
                {
                    break;
                }

                if($j['activite']['id'] == $activite->secure_id)
                {
                    $old = 1;
                    array_push($j['data'], $checkList);
                    $json[$key]['data'] = $j['data'];

                }
            }

            if(!$old)
            {
                array_push($json,
                    [
                        "activite" => [
                            'nom' => $activite->nom,
                            'id' => $activite->secure_id,
                        ],
                        "data" => [$checkList]
                    ]
                );
            }
        }

        return $json;
    }

    public function getSuiviJson($attributs)
    {
        $checkLists = $this->checkLists;
        $json = [];

        $suivi = ESuivi::where('formulaireId', $this->id)->
                         where('entrepriseExecutantId', $attributs['entrepriseId'])->
                         where('date', $attributs['date'])->
                         first();

        if(!($suivi)) return $json;

        array_push($json,
            [
                "entete" => [
                    'entreprise' => $suivi->entrepriseExecutant->user->nom,
                    'responsable' => $suivi->responsableEnquete->nom.' '.$suivi->responsableEnquete->prenom,
                    'poste' => $suivi->responsableEnquete->poste,
                    'contact' => $suivi->responsableEnquete->contact,
                    'site' => $suivi->site->nom,
                    'date' => $suivi->date,
                    'bailleur' => $suivi->site->bailleurs->first()->user->nom,
                    'missionaire' => $suivi->auteurable->user->nom. ' '.$suivi->auteurable->user->prenom,
                    'numero' => $suivi->auteurable->user->contact
                ],
            ]
        );

        foreach($checkLists as $checkList)
        {
            $activite = EActivite::find($checkList->pivot->activiteId);
            $old = 0;

            foreach($json as $key => $j)
            {
                if($old)
                {
                    break;
                }

                if(!(array_key_exists('activite', $j)))
                {
                    continue;
                }

                if($j['activite']['id'] == $activite->secure_id)
                {
                    $old = 1;

                    $suivi = ESuivi::where('formulaireId', $this->id)->
                                 where('entrepriseExecutantId', $attributs['entrepriseId'])->
                                 where('checkListId', $checkList->id)->
                                 where('activiteId', $activite->id)->
                                 where('date', $attributs['date'])->
                                 first();
                    $checkList->content = $suivi->valeur;
                    $checkList->justification = $suivi->justification;

                    array_push($j['data'], $checkList);
                    $json[$key]['data'] = $j['data'];

                }
            }

            if(!$old)
            {
                $suivi = ESuivi::where('formulaireId', $this->id)->
                                 where('entrepriseExecutantId', $attributs['entrepriseId'])->
                                 where('checkListId', $checkList->id)->
                                 where('activiteId', $activite->id)->
                                 where('date', $attributs['date'])->
                                 first();
                $checkList->content = $suivi->valeur;
                $checkList->justification = $suivi->justification;

                $statut = EActiviteStatut::where('activiteId', $activite->id)->
                                           where('entrepriseId', $attributs['entrepriseId'])->
                                           where('date', $attributs['date'])->
                                           first();

                array_push($json,
                    [
                        "activite" => [
                            'nom' => $activite->nom,
                            'id' => $activite->secure_id,
                            'statut' => $statut->etat
                        ],
                        "data" => [$checkList]
                    ]
                );
            }
        }

        return $json;
    }

    public function getSuiviGeneralJson($attributs)
    {
        $questions = $this->questions;
        $json = [];

        $suivi = Reponse::where('formulaireId', $this->id)->
                         where('userId', $attributs['userId'])->
                         orWhere('shared', 'like', '%'.$attributs['userId'].'%')->
                         where('date', $attributs['date'])->
                         first();

        if(!($suivi)) return $json;

        array_push($json,
            [
                "entete" => [
                    'auteur' => $suivi->user->nom. ' '.$suivi->user->prenom,
                    'numero' => $suivi->user->contact
                ],
            ]
        );

        foreach($questions as $question)
        {
            $suivi = Reponse::where('formulaireId', $this->id)->
                                 where('userId', $attributs['userId'])->
                                 orWhere('shared', 'like', '%'.$attributs['userId'].'%')->
                                 where('questionId', $question->id)->
                                 where('date', $attributs['date'])->
                                 first();
            $question->content = $suivi->valeur;

            array_push($json, $question);
        }

        return $json;
    }

    public function generalJson()
    {
        $json = $this->questions->toArray();
        return $json;
    }
}
