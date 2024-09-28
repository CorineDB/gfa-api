<?php

namespace App\Http\Resources\gouvernance;

use Illuminate\Http\Resources\Json\JsonResource;

class FormulaireDePerceptionResource extends JsonResource
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
            'id'                        => $this->secure_id,
            'nom'                       => $this->nom,
            'indicateurs_de_gouvernance'  => $this->indicateurs_de_gouvernance->map(function($indicateur){
                return $this->indicateur($indicateur);
            })
        ];
    }

    private function indicateur($indicateur){
        
        return [
            'id'                        => $indicateur->secure_id,
            'nom'                       => $indicateur->nom,
            "type"                      => $indicateur->type,
            "can_have_multiple_reponse" => $indicateur->can_have_multiple_reponse ? true : false,
            'options_de_reponse'        => $indicateur->options_de_reponse->map(function($option_de_reponse){
                return $this->option_de_reponse($option_de_reponse);
            })
        ];
    }

    public function option_de_reponse($option_de_reponse){
        
        return [
            'id'            => $option_de_reponse->secure_id,
            'libelle'       => $option_de_reponse->libelle,
            "note"          => $option_de_reponse->note
        ];
    }
}
