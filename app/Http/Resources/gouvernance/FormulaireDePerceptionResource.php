<?php

namespace App\Http\Resources\gouvernance;

use Illuminate\Http\Resources\Json\JsonResource;

class FormulaireDePerceptionResource extends JsonResource
{
    protected $enqueteId;
    protected $organisationId;

    // Modify the constructor to accept the additional external value
    public function __construct($resource, $enqueteId=null, $organisationId=null)
    {
        parent::__construct($resource);
        $this->enqueteId = $enqueteId;
        $this->organisationId = $organisationId;
    }

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
            }),
            'reponses_collecter' => $this->when(($this->enqueteId != null && $this->organisationId != null), $indicateur->observations->where('enqueteDeCollecteId', $this->enqueteId)->where('organisationId', $this->organisationId)->map(function($reponse){
                return [
                    'id'                        => $reponse->secure_id,
                    'libelle'                   => $reponse->libelle,
                    "note"                      => intval($reponse->note),
                    'source'                    => $reponse->source,
                    'optionDeReponseId'         => $reponse->optionDeReponse->secure_id,
                ];
            }))
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
