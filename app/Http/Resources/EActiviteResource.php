<?php

namespace App\Http\Resources;

use Carbon\Carbon;
use Illuminate\Http\Resources\Json\JsonResource;

class EActiviteResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array|\Illuminate\Contracts\Support\Arrayable|\JsonSerializable
     */
    public function toArray($request)
    {
        $entreprise = "";
        foreach($this->entrepriseExecutants as $e)
        {
            $entreprise .= ' '.$e->user->nom . ',';
        }

        $entreprise = substr($entreprise, 0, -1);

        return [
            "id" => $this->secure_id,
            "nom" =>$this->nom,
            "code" => $this->code,
            "debut" => $this->duree->debut,
            "fin" => $this->duree->fin,
            "statut" => $this->statut,
            "entreprise" => $entreprise,
              "created_at" => Carbon::parse($this->created_at)->format("Y-m-d h:i:s")
        ];
    }
}
