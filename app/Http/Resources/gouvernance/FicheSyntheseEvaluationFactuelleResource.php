<?php

namespace App\Http\Resources\gouvernance;

use App\Models\PrincipeDeGouvernance;
use Illuminate\Http\Resources\Json\JsonResource;
use Carbon\Carbon;

class FicheSyntheseEvaluationFactuelleResource extends JsonResource
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
            "indice_factuel"            => $this->indice_factuel,
            'principes_de_gouvernance'  => $this->principes_de_gouvernance->map(function($principe){
                return $this->principe($principe);
            })
        ];
    }

    public function principe($principe){
        
        return [
            'id'                        => $principe->secure_id,
            'nom'                       => $principe->nom,
            "score_factuel"             => $principe->score_factuel,
            'indicateurs_de_gouvernance'  => $principe->indicateurs_criteres_de_gouvernance->map(function($indicateur){
                return $this->indicateur($indicateur);
            })
        ];
    }

    public function indicateur($indicateur){
        
        return [
            'id'                        => $indicateur->secure_id,
            'nom'                       => $indicateur->nom,
            "type"                      => $indicateur->type,
            "can_have_multiple_reponse" => $indicateur->can_have_multiple_reponse,
            "note"                      => $indicateur->note,
        ];
    }
}
