<?php

namespace App\Http\Resources\gouvernance;

use Carbon\Carbon;
use Illuminate\Http\Resources\Json\JsonResource;

class FormulairesDeGouvernanceResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array|\Illuminate\Contracts\Support\Arrayable|\JsonSerializable
     */
    public function toArray($request)
    {
        $formulaireDeGouvernanceId=$this->id;
        return [
            'id' => $this->secure_id,
            'libelle' => $this->libelle,
            'description' => $this->description,
            'type' => $this->type,
            'lien' => $this->lien,
            'annee_exercice' => $this->annee_exercice,
            'created_by' => $this->createdBy->secure_id,
            'programmeId' => $this->programme->secure_id,
            'created_at' => Carbon::parse($this->created_at)->format("Y-m-d"),
            'options_de_reponse' => $this->options_de_reponse,
            'categories_de_gouvernance' => CategoriesDeGouvernanceResource::collection($this->categories_de_gouvernance->load([
                'questions_de_gouvernance' => function($query) use ($formulaireDeGouvernanceId) {
                    $query->when($formulaireDeGouvernanceId, function($q) use ($formulaireDeGouvernanceId) {
                        return $q->where('formulaireDeGouvernanceId', $formulaireDeGouvernanceId);
                    });
                }
            ])),
            
            //'categories_de_gouvernance' => $this->categories_de_gouvernance->load(['questions_de_gouvernance'])//QuestionsDeGouvernanceResource::collection($this->questions_operationnelle)
        ];
    }
}
