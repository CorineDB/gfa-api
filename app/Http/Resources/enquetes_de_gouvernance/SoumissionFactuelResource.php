<?php

namespace App\Http\Resources\enquetes_de_gouvernance;

use App\Http\Resources\enquetes_de_gouvernance\ReponsesDeLaCollecteFactuelResource;
use Illuminate\Http\Resources\Json\JsonResource;

class SoumissionFactuelResource extends JsonResource
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
            'id' => $this?->formulaireDeGouvernance?->secure_id,
            'soumissionId' => $this->secure_id,
            'libelle' => $this?->formulaireDeGouvernance?->libelle,
            'description' => $this?->formulaireDeGouvernance?->description,
            'statut' => $this?->statut,
            'pourcentage_evolution' => $this?->pourcentage_evolution,
            'options_de_reponse' => $this?->formulaireDeGouvernance?->options_de_reponse->map(function($option){
                return [
                    "id"                    => $option->secure_id,
                    'libelle'               => $option->libelle,
                    'slug'                  => $option->slug,
                    'point'                 => $option->pivot->point,
                    'preuveIsRequired'      => $option->pivot->preuveIsRequired,
                    'sourceIsRequired'      => $option->pivot->sourceIsRequired,
                    'descriptionIsRequired' => $option->pivot->descriptionIsRequired
                ];
            }),
            'comite_members' => $this?->comite_members,
            'categories_de_gouvernance' => $this->when($this?->formulaireDeGouvernance?->categories_de_gouvernance->count(), $this->sections($this?->formulaireDeGouvernance?->categories_de_gouvernance)),
            'submitted_at' => $this->submitted_at,
	    'created_at' => $this->created_at
        ];
    }

    public function sections($categories)
    {
        return $categories->map(function($sousCategorieDeGouvernance){
            $subCategories = [];
            $questions = [];

            if($sousCategorieDeGouvernance->categories_de_gouvernance->count()){
                $subCategories = ['categories_de_gouvernance' =>  $this->sections($sousCategorieDeGouvernance->categories_de_gouvernance)];
            }

            if($sousCategorieDeGouvernance->questions_de_gouvernance->count()){
                $questions = ['questions_de_gouvernance' =>  $this->questions_reponses($sousCategorieDeGouvernance->questions_de_gouvernance)];
            }

            return array_merge(array_merge([
                'id' => $sousCategorieDeGouvernance->secure_id,
                'nom' => $sousCategorieDeGouvernance->categorieable->nom,
                'categorieableId' => $sousCategorieDeGouvernance->categorieable->secure_id,
                'position' => $sousCategorieDeGouvernance->position,
                'categorieDeGouvernanceId' => optional($sousCategorieDeGouvernance->categorieDeGouvernanceParent)?->secure_id
            ], $subCategories), $questions);
        });
    }

    public function questions_reponses($questions)
    {
        // Créer un index des réponses par questionId pour accès O(1) - Évite les requêtes N+1
        $reponsesIndexed = $this->reponses_de_la_collecte->keyBy('questionId');

        return $questions->map(function($question) use ($reponsesIndexed) {
            // ❌ ANCIENNE VERSION (Requête N+1) :
            // $reponse = $question->reponse($this->id)->first();

            // ✅ NOUVELLE VERSION (Utilise les données déjà chargées) :
            $reponse = $reponsesIndexed->get($question->id);

            return [
                'id' => $question->secure_id,
                'nom' => $question->indicateur_de_gouvernance->nom,
                'position' => $question->position,
                'indicateur_de_gouvernance' => $question->indicateur_de_gouvernance ? [
                    'id' => $question->indicateur_de_gouvernance->secure_id,
                    'nom' => $question->indicateur_de_gouvernance->nom
                ] : null,
                'reponse_de_la_collecte' => $reponse ? new ReponsesDeLaCollecteFactuelResource($reponse) : null
            ];
        });
    }
}
