<?php

namespace App\Http\Resources\enquetes_de_gouvernance;

use App\Http\Resources\FichierResource;
use Carbon\Carbon;
use Illuminate\Http\Resources\Json\JsonResource;

class ReponsesDeLaCollecteFactuelResource extends JsonResource
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
            'pourcentage_evolution' => $this->pourcentage_evolution,
            'point' => $this->point,
            "preuveIsRequired" => $this->option_de_reponse->pivot->preuveIsRequired,
            "sourceIsRequired" => $this->option_de_reponse->pivot->sourceIsRequired,
            "descriptionIsRequired" => $this->option_de_reponse->pivot->descriptionIsRequired,
            "sourceDeVerification" => $this->source_de_verification ? $this->source_de_verification->intitule : ($this->sourceDeVerification ?? null),
            "sourceDeVerificationId" => $this->source_de_verification ? $this->source_de_verification->secure_id : null,
            "description" => $this->description,
            "optionDeReponseId" => $this->option_de_reponse->secure_id,
            "questionId" => $this->question->secure_id,
            "soumissionId" => $this->soumission->secure_id,
            'preuves' => FichierResource::collection($this->preuves_de_verification)
        ];
    }
}
