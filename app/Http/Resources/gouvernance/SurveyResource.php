<?php

namespace App\Http\Resources\gouvernance;

use Carbon\Carbon;
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
            'intitule' => $this->intitule,
            'description' => $this->description,
            'nbreParticipants' => $this->nbreParticipants,
            'debut' => Carbon::parse($this->debut)->format("Y-m-d"),
            'fin' => Carbon::parse($this->fin)->format("Y-m-d"),
            'statut' => $this->statut,
            'surveyFormId' => $this->survey_form->secure_id,
            'created_at' => Carbon::parse($this->created_at)->format("Y-m-d")
        ];
    }
}
