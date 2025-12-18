<?php

namespace App\Http\Resources\gouvernance;

use Carbon\Carbon;
use Illuminate\Http\Resources\Json\JsonResource;

class SurveyReponseResource extends JsonResource
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
            'id'                => $this->secure_id,
            'idParticipant'     => $this->idParticipant,
            'response_data'     => $this->response_data,
            'submitted_at'      => $this->submitted_at ? Carbon::parse($this->submitted_at)->format("Y-m-d") : null,
            'statut'            => $this->statut,
            'surveyId'          => $this->survey->secure_id,
            'created_at'        => Carbon::parse($this->created_at)->format("Y-m-d")
        ];
    }
}
