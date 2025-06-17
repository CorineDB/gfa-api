<?php

namespace App\Http\Resources\suivi;

use App\Http\Resources\CommentaireResource;
use App\Http\Resources\indicateur\IndicateurResource;
use Carbon\Carbon;
use Illuminate\Http\Resources\Json\JsonResource;

class SuiviIndicateursResource extends JsonResource
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
            "estValider" => $this->estValider,
            "sources_de_donnee" => $this->sources_de_donnee,
            "cumul" => $this->cumul(),
            "valeurRealise" => $this->valeurRealise,
            "valeurCible" => $this->valeurCible ? [
                "id" => $this->valeurCible->secure_id,
                "annee" => $this->valeurCible->annee,
                "valeurCible" => $this->valeurCible->valeurCible
            ] : null,


            $this->mergeWhen(!$this->valeurCible, function(){
                return [
                    "indicateur" => null
                ];
            }),

            $this->mergeWhen($this->valeurCible, function(){
                return [
                    "indicateur" => null//new IndicateurResource($this->indicateur()),
                ];
            }),
            "auteur" => $this->suivi_indicateurable ? [
                "id" => $this->suivi_indicateurable->secure_id,
                "nom" => $this->suivi_indicateurable->user->nom,
                "prenom" => $this->suivi_indicateurable->user->prenom,
            ] : null,
            "commentaire" => $this->commentaire,
            "commentaires" => CommentaireResource::collection($this->commentaires),
              "created_at" => Carbon::parse($this->created_at)->format("Y-m-d h:i:s")
        ];
    }
}
