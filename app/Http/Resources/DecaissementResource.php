<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;
use App\Models\Bailleur;
use Carbon\Carbon;

class DecaissementResource extends JsonResource
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
            "montant" => $this->montant,
            "projet" => $this->projet->nom,
            "commentaires" => CommentaireResource::collection($this->commentaires),
            "bailleur" => $this->projet->bailleur->sigle,
            "date" => $this->date,
              "created_at" => Carbon::parse($this->created_at)->format("Y-m-d h:i:s"),
            "type" => $this->decaissementable_type == get_class(new Bailleur) ? "Pret" : "Budget National",
            "methodeDePaiement" => $this->methodeDePaiement,
            "beneficiaire" => $this->beneficiaire
        ];
    }
}
