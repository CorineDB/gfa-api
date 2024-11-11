<?php

namespace App\Http\Resources\gouvernance;

use Illuminate\Http\Resources\Json\JsonResource;

class FicheDeSyntheseEvaluationFactuelleResource extends JsonResource
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
            'id'                         => $this->secure_id,
            'nom'                        => $this->categorieable->nom,
            "indice_factuel"             => $this->when(isset($this->indice_factuel), $this->indice_factuel),
            "score_factuel"              => $this->when(isset($this->score_factuel), $this->score_factuel),
            'categories_de_gouvernance'  => $this->when(($this->sousCategoriesDeGouvernance->count() && !$this->questions_de_gouvernance->count()), FicheDeSyntheseEvaluationFactuelleResource::collection($this->sousCategoriesDeGouvernance)),
            'questions_de_gouvernance'   => $this->when((!$this->sousCategoriesDeGouvernance->count() && $this->questions_de_gouvernance->count()), $this->questions_de_gouvernance->map(function($question_de_gouvernance){
                return $this->question_de_gouvernance($question_de_gouvernance);
            }))
        ];
    }
    
    public function question_de_gouvernance($question_de_gouvernance){
        return [
            'id' => $question_de_gouvernance->secure_id,
            'nom' => $question_de_gouvernance->indicateur_de_gouvernance->nom,
            'type' => $question_de_gouvernance->type,
            'reponse' => $question_de_gouvernance->reponses,//$this->when($this->type === 'indicateur', $question_de_gouvernance->indicateur_de_gouvernance/* optional($question_de_gouvernance->reponses->first())->point ?? 0 */)
        ];
    }

    
    public function reponse_de_la_collecte($reponse){
        return [
            'id' => $reponse->secure_id,
            'nom' => $reponse->option_de_reponse->libelle,
            'type' => $reponse->type,
            'point' => $this->when($this->type === 'indicateur', $reponse->point/* optional($question_de_gouvernance->reponses->first())->point ?? 0 */)
        ];
    }

}
