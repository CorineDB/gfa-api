<?php

namespace App\Http\Resources\suivi_indicateur_mod;

use Illuminate\Http\Resources\Json\JsonResource;

class SuiviIndicateurModResource extends JsonResource
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
            "commentaires" => $this->commentaires,
            "valeurRealise" => $this->valeurRealise,
            "valeurCible" => $this->valeurCible ? [
                "id" => $this->valeurCible->secure_id,
                "annee" => $this->valeurCible->annee,
                "valeurCible" => $this->valeurCible->valeurCible,
                "indicateur" => $this->valeurCible->cibleable ? [
                    "id" => $this->valeurCible->cibleable->secure_id,
                    "nom" => $this->valeurCible->cibleable->nom
                ] : null,
            ] : null,
            "created_at" => $this->created_at
        ];
    }
}
