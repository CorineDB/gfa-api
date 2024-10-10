<?php

namespace App\Http\Resources\indicateur;

use App\Http\Resources\UniteeMesureResource;
use App\Models\Unitee;
use Illuminate\Http\Resources\Json\JsonResource;

class IndicateurValueKeyResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array|\Illuminate\Contracts\Support\Arrayable|\JsonSerializable
     */
    public function toArray($request)
    {
        $unite = $this->uniteeMesure;
        
        if($this->pivot){
            $unite = Unitee::find($this->pivot->uniteeMesureId);
        }

        return [
            "id" => $this->secure_id,
            "libelle" => $this->libelle,
            "key" => $this->key,
            "description" => $this->description,
            /*"type" => $this->pivot->type,
            "unitee_mesure" => $this->when($this->pivot->uniteeMesureId, new UniteeMesureResource(Unitee::find($this->pivot->uniteeMesureId)))*/

            "type" => $this->when($unite, $unite->nom),
            "uniteeMesureId" => $this->when($unite, $unite->secure_id)
        ];
    }
}
