<?php

namespace App\Http\Resources\gouvernance;

use App\Http\Resources\FichierResource;
use Carbon\Carbon;
use Illuminate\Http\Resources\Json\JsonResource;

class ReponsesDeLaCollecteResource extends JsonResource
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
            'nom' => $this->option_de_reponse->libelle,
            'type' => $this->type,
            'pourcentage_evolution' => $this->pourcentage_evolution,
            'point' => $this->point,
            "sourceDeVerification" => $this->when($this->type === 'indicateur', $this->sourceDeVerificationId ? $this->source_de_verification->intitule : $this->sourceDeVerification),
            "sourceDeVerificationId" => $this->when($this->type === 'indicateur', ($this->source_de_verification ? $this->source_de_verification->secure_id : null)),
            "optionDeReponseId" => $this->option_de_reponse->secure_id,
            "questionId" => $this->question->secure_id,
            "soumissionId" => $this->soumission->secure_id,
            'programmeId' => $this->programme->secure_id,
            'created_at' => Carbon::parse($this->created_at)->format("Y-m-d H:i:s"),
            'updated_at' => Carbon::parse($this->updated_at)->format("Y-m-d H:i:s"),
            'preuves' => $this->when($this->type === 'indicateur', FichierResource::collection($this->preuves_de_verification))
        ];
    }
}
