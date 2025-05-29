<?php

namespace App\Http\Resources\enquetes_de_gouvernance;

use Carbon\Carbon;
use Illuminate\Http\Resources\Json\JsonResource;

class ReponsesDeLaCollecteDePerceptionResource extends JsonResource
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
            "optionDeReponseId" => $this->option_de_reponse->secure_id,
            "questionId" => $this->question->secure_id,
            "soumissionId" => $this->soumission->secure_id,
            'programmeId' => $this->programme->secure_id,
            'created_at' => Carbon::parse($this->created_at)->format("Y-m-d H:i:s"),
            'updated_at' => Carbon::parse($this->updated_at)->format("Y-m-d H:i:s")
        ];
    }
}
