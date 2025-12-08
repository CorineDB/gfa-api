<?php

namespace App\Http\Resources\gouvernance;

use Carbon\Carbon;
use Illuminate\Http\Resources\Json\JsonResource;

class FichesDeSyntheseResource extends JsonResource
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
            'type' => $this->type,
            'synthese' => $this->synthese,
            'evaluatedAt' => Carbon::parse($this->evaluatedAt)->format("Y-m-d"),
            'formulaireDeGouvernanceId' => $this->formulaire_de_gouvernance->secure_id,
            'organisationId' => $this->organisation->secure_id,
            'evaluationDeGouvernanceId' => $this->evaluation_de_gouvernance->secure_id,
            'programmeId' => $this->programme->secure_id,
            'created_at' => Carbon::parse($this->created_at)->format("Y-m-d")
        ];
    }
}
