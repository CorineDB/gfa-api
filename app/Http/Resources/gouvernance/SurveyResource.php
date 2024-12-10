<?php

namespace App\Http\Resources\gouvernance;

use Illuminate\Http\Resources\Json\JsonResource;

class SurveyResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array|\Illuminate\Contracts\Support\Arrayable|\JsonSerializable
     */
    public function toArray($request)
    {
        return [
            'id' => $this->secure_id,
            'libelle' => $this->libelle,
            'description' => $this->description,
            'nbreParticipants' => $this->nbreParticipants,
            'debut' => $this->debut,
            'fin' => $this->fin,
            'statut' => $this->statut,
            'surveyFormId' => $this->survey_form->secure_id,
            'created_at' => $this->created_at
        ];
    }
}
