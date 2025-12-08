<?php

namespace App\Http\Resources;

use App\Http\Resources\FichierResource;
use Carbon\Carbon;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Storage;

class AuditResource extends JsonResource
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
            "annee" => $this->annee,
            "entreprise" => $this->entreprise,
            "entrepriseContact" => $this->entrepriseContact,
            "dateDeTransmission" => $this->dateDeTransmission,
            "etat" => $this->etat,
            "projet" => $this->projet,
            "statut" => $this->statut,
            "categorie" => $this->categorie,
            "rapport" => FichierResource::collection($this->fichiers),
            "created_at" => Carbon::parse($this->created_at)->format("Y-m-d h:i:s")
        ];
    }
}
