<?php

namespace App\Http\Resources;

use Carbon\Carbon;
use Illuminate\Http\Resources\Json\JsonResource;

class SuiviFinancierResource extends JsonResource
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
            "id" => $this->secure_id,
            "codePta" => $this->activite->codePta,
            "nom" => $this->activite->nom,
            "annee" => $this->annee,
            "type" => $this->type,
            "dateDeSuivi" => $this->dateDeSuivi,
            "trimestre" => $this->trimestre,
            "created_at" => Carbon::parse($this->created_at)->format("Y-m-d h:i:s"),
            "consommer" => $this->consommer,
        ];
    }
}
