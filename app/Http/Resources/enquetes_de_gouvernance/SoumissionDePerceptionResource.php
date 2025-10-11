<?php

namespace App\Http\Resources\enquetes_de_gouvernance;

use Illuminate\Http\Resources\Json\JsonResource;

class SoumissionDePerceptionResource extends JsonResource
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
            'id' => $this->formulaireDeGouvernance->secure_id,
            'soumissionId' => $this->secure_id,
            'libelle' => $this->formulaireDeGouvernance->libelle,
            'description' => $this->formulaireDeGouvernance->description,
            'statut' => $this->statut,
            'pourcentage_evolution' => $this->pourcentage_evolution,
            'options_de_reponse' => $this->formulaireDeGouvernance->options_de_reponse->map(function($option){
                return [
                    "id"                    => $option->secure_id,
                    'libelle'               => $option->libelle,
                    'slug'                  => $option->slug,
                    'point'                 => $option->pivot->point
                ];
            }),

            'categories_de_gouvernance' => $this->when($this->formulaireDeGouvernance->categories_de_gouvernance->count(), $this->sections($this->formulaireDeGouvernance->categories_de_gouvernance)),
        ];
    }

    public function sections($categories)
    {
        if(!$categories) return [];

        return $categories->map(function($sousCategorieDeGouvernance){

            $questions = [];

            if($sousCategorieDeGouvernance->questions_de_gouvernance->count()){
                $questions = ['questions_de_gouvernance' =>  $this->questions_reponses($sousCategorieDeGouvernance->questions_de_gouvernance)];
            }

            return array_merge([
                'id' => $sousCategorieDeGouvernance->secure_id,
                'nom' => $sousCategorieDeGouvernance->categorieable->nom,
                'categorieableId' => $sousCategorieDeGouvernance->categorieable->secure_id,
                'position' => $sousCategorieDeGouvernance->position,
                'categorieDeGouvernanceId' => optional($sousCategorieDeGouvernance->categorieDeGouvernanceParent)->secure_id
            ], $questions);
        });
    }

    public function questions_reponses($questions)
    {
        // Créer un index des réponses par questionId pour accès O(1) - Évite les requêtes N+1
        // $reponsesIndexed = $this->reponses_de_la_collecte->keyBy('questionId'); // ANCIEN (permettait les doublons)
        $reponsesIndexed = $this->reponses_uniques->keyBy('questionId'); // NOUVEAU (sans doublons)

        return $questions->map(function($question) use ($reponsesIndexed) {
            // ❌ ANCIENNE VERSION (Requête N+1 + doublons possibles) :
            // $reponse = $question->reponse($this->id)->first();

            // ✅ NOUVELLE VERSION (Utilise les données déjà chargées + sans doublons) :
            $reponse = $reponsesIndexed->get($question->id);

            return [
                'id' => $question->secure_id,
                'nom' => $question->question_operationnelle->nom,
                'position' => $question->position,

                'question_operationnelle' => $question->question_operationnelle ? [
                    'id' => $question->question_operationnelle->secure_id,
                    'nom' => $question->question_operationnelle->nom
                ] : null,

                'reponse_de_la_collecte' => $reponse ? new ReponsesDeLaCollecteDePerceptionResource($reponse) : null
            ];
        });
    }
}
