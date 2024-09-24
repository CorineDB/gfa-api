<?php

namespace App\Http\Resources\suivi_indicateur_mod;

use App\Http\Resources\CommentaireResource;
use App\Http\Resources\indicateur_mod\IndicateurModResource;
use Carbon\Carbon;
use Illuminate\Http\Resources\Json\JsonResource;

class SuivisIndicateurModResource extends JsonResource
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
            "cumul" => $this->cumul(),
            "valeurRealise" => $this->valeurRealise,
            "valeurCible" => $this->valeurCible ? [
                "id" => $this->valeurCible->secure_id,
                "annee" => $this->valeurCible->annee,
                "valeurCible" => $this->valeurCible->valeurCible
            ] : null,
            "indicateur" => new IndicateurModResource($this->indicateur()),
            "commentaire" => $this->commentaire,
            "commentaires" => CommentaireResource::collection($this->commentaires),
              "created_at" => Carbon::parse($this->created_at)->format("Y-m-d h:i:s")
        ];
    }
}
