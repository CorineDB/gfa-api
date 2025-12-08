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
            "indice_de_perception"       => $this->when(isset($this->indice_de_perception), $this->indice_de_perception),
            'categories_de_gouvernance'  => $this->when(($this->sousCategoriesDeGouvernance->count() && !$this->questions_de_gouvernance->count()), FicheDeSyntheseEvaluationFactuelleResource::collection($this->sousCategoriesDeGouvernance)),
            'questions_de_gouvernance'   => $this->when((!$this->sousCategoriesDeGouvernance->count() && $this->questions_de_gouvernance->count()), $this->questions_de_gouvernance->map(function ($question_de_gouvernance) {

                $type = null;

                if (method_exists($question_de_gouvernance, 'indicateur_de_gouvernance') &&
                    $question_de_gouvernance->indicateur_de_gouvernance()->exists()) {
                    $type = 'factuel';
                } elseif (method_exists($question_de_gouvernance, 'question_operationnelle') &&
                        $question_de_gouvernance->question_operationnelle()->exists()) {
                    $type = 'perception';
                } else {
                    $type = null;
                }
                return $this->question_de_gouvernance($question_de_gouvernance, $type);
            }))
        ];
    }

    public function question_de_gouvernance($question_de_gouvernance, $type)
    {
        $question = $question_de_gouvernance ? [
            'id' => $question_de_gouvernance->secure_id,
            'nom' => $type == 'factuel' ? $question_de_gouvernance->indicateur_de_gouvernance->nom : ($type == 'perception' ? $question_de_gouvernance->question_operationnelle->nom : "null"),

            //'type' => $question_de_gouvernance->type
            /*,
                "moyenne_ponderee"             => $this->when(isset($question_de_gouvernance->moyenne_ponderee), $question_de_gouvernance->moyenne_ponderee),

                "options_de_reponse"             => $this->when(isset($question_de_gouvernance->options_de_reponse), function() use($question_de_gouvernance) {
                    return $question_de_gouvernance->options_de_reponse->map(function($option_de_reponse){
                        return $this->option_de_reponse($option_de_reponse);
                    });
                }),
                'reponse' => $this->when( (isset($question_de_gouvernance->type) && $question_de_gouvernance->type === 'indicateur'), function() use ($question_de_gouvernance){
                    return $this->reponse_de_la_collecte($question_de_gouvernance->reponses->first());
                })
            */
        ] : null;

        if ($question != null) {
            if($type == 'factuel'){
                $question['indicateur_de_gouvernance'] = $question_de_gouvernance->indicateur_de_gouvernance ? [
                        'id' => $question_de_gouvernance->indicateur_de_gouvernance->secure_id,
                        'nom' => $question_de_gouvernance->indicateur_de_gouvernance->nom
                    ] : null;
            }

            if($type == 'perception'){
                $question['question_operationnelle'] = $question_de_gouvernance->question_operationnelle ? [
                        'id' => $question_de_gouvernance->question_operationnelle->secure_id,
                        'nom' => $question_de_gouvernance->question_operationnelle->nom
                    ] : null;
            }

            if (isset($question_de_gouvernance->moyenne_ponderee)) {
                $question = array_merge($question, [
                    "moyenne_ponderee" => $question_de_gouvernance->moyenne_ponderee
                ]);
            }

            if (isset($question_de_gouvernance->options_de_reponse)) {
                $question = array_merge($question, [
                    "options_de_reponse" => $question_de_gouvernance->options_de_reponse->map(function ($option_de_reponse) {
                        return $this->option_de_reponse($option_de_reponse);
                    })
                ]);
            }

            if ((isset($type) && $type === 'factuel')) {
                $question = array_merge($question, [
                    "reponse" => $this->reponse_de_la_collecte($question_de_gouvernance->reponses->first())
                ]);
            }

            /* elseif((isset($question_de_gouvernance->type) && $question_de_gouvernance->type === 'question_operationnelle')){
                $question = array_merge($question, [
                    "reponses" => $question_de_gouvernance->reponses->map(function($reponse){
                        return $this->reponse_de_la_collecte($reponse);
                    })
                ]);
            } */
        }

        return $question;
    }


    public function reponse_de_la_collecte($reponse)
    {
        return $reponse ? [
            'id' => $reponse->secure_id,
            'nom' => $reponse->option_de_reponse->libelle,
            //'type' => $reponse->type,
            'point' => $reponse->point,
            'sourceDeVerification' => $reponse->source_de_verification ? $reponse->source_de_verification->intitule : ($reponse->sourceDeVerification ?? "N/D"),
            'preuves' => $reponse->preuves_de_verification,
        ] : [
            'id' => null,
            'nom' => "N/D",
            //'type' => $reponse->type,
            'sourceDeVerification' => "N/D",
            'point' => "N/D",
            'sourceDeVerification' => "N/D",
            'preuves' => "N/D",
        ];
    }

    public function option_de_reponse($option_de_reponse)
    {
        return $option_de_reponse ? [
            'id' => $option_de_reponse->secure_id,
            'nom' => $option_de_reponse->libelle,
            'point' => $option_de_reponse->pivot->point,
            'moyenne_ponderee_i' => $option_de_reponse->moyenne_ponderee_i,
            'reponses_count' => $option_de_reponse->reponses_count

        ] : [
            'id' => $option_de_reponse->secure_id,
            'nom' => $option_de_reponse->libelle,
            'point' => $option_de_reponse->pivot->point,
            'moyenne_ponderee_i' => $option_de_reponse->moyenne_ponderee_i,
            'reponses_count' => $option_de_reponse->reponses_count
        ];
    }
}
