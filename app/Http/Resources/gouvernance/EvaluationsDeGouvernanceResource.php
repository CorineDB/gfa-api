<?php

namespace App\Http\Resources\gouvernance;

use Carbon\Carbon;
use Illuminate\Http\Resources\Json\JsonResource;

class EvaluationsDeGouvernanceResource extends JsonResource
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
            'debut' => Carbon::parse($this->debut)->format("Y-m-d"),
            'fin' => Carbon::parse($this->fin)->format("Y-m-d"),
            'annee_exercice' => $this->annee_exercice,
            'statut' => $this->statut,
            'programmeId' => $this->programme->secure_id,
            'created_at' => $this->created_at,
            'formulaires_de_gouvernance' => FormulairesDeGouvernanceResource::collection($this->formulaires_de_gouvernance)
        ];
    }
}
