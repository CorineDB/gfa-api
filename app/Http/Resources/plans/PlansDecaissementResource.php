<?php

namespace App\Http\Resources\plans;

use Carbon\Carbon;
use Illuminate\Http\Resources\Json\JsonResource;

class PlansDecaissementResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array|\Illuminate\Contracts\Support\Arrayable|\JsonSerializable
     */
    public function toArray($request)
    {
        switch ($this->trimestre) {
            case 1:
                $debut = Carbon::parse(1 .'-'. 1 .'-'. $this->annee)->format("Y-m-d");
                $fin = Carbon::parse(31 .'-'.  ($this->trimestre*3).'-'.$this->annee)->format("Y-m-d");
                break;

            case 2:
                $debut = Carbon::parse(1 .'-'. 4 .'-'. $this->annee)->format("Y-m-d");
                $fin = Carbon::parse(30 .'-'.  ($this->trimestre*3).'-'.$this->annee)->format("Y-m-d");
                break;

            case 3:
                $debut = Carbon::parse(1 .'-'. 7 .'-'. $this->annee)->format("Y-m-d");
                $fin = Carbon::parse(30 .'-'.  ($this->trimestre*3).'-'.$this->annee)->format("Y-m-d");
                break;

            case 4:
                $debut = Carbon::parse(1 .'-'. 10 .'-'. $this->annee)->format("Y-m-d");
                $fin = Carbon::parse(31 .'-'.  ($this->trimestre*3).'-'.$this->annee)->format("Y-m-d");
                break;

            default:
                # code...
                break;
        }
        return [
            "id" => $this->secure_id,
            "trimestre" => $this->trimestre,
            "annee" => $this->annee,
            "debut" => $debut,
            "fin" => $fin,
            "activiteId" => optional($this->activite)->secure_id,
            "budgetNational" => $this->budgetNational,
            "pret" => $this->pret,
            "activite" => !$this->activite ? null : [
                "id" => $this->activite->secure_id,
                "nom" => $this->activite->nom,
            ],
              "created_at" => Carbon::parse($this->created_at)->format("Y-m-d h:i:s")
        ];
    }
}
