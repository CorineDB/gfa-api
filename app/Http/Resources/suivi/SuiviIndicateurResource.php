<?php

namespace App\Http\Resources\suivi;

use App\Http\Resources\indicateur\IndicateurResource;
use Carbon\Carbon;
use Illuminate\Http\Resources\Json\JsonResource;

class SuiviIndicateurResource extends JsonResource
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
            "trimestre" => $this->trimestre,
            "dateSuivie" => $this->dateSuivie,
            "cumul" => $this->cumul(),
            "valeurRealise" => $this->valeurRealise,
            "valeurCible" => $this->valeurCible ? [
                "id" => $this->valeurCible->secure_id,
                "annee" => $this->valeurCible->annee,
                "valeurCible" => $this->valeurCible->valeurCible,
                "indicateur" => new IndicateurResource($this->indicateur()),
            ] : null,
            "commentaire" => $this->commentaire,
            "commentaires" => $this->commentaires,
              "created_at" => Carbon::parse($this->created_at)->format("Y-m-d h:i:s")
        ];
    }
}
