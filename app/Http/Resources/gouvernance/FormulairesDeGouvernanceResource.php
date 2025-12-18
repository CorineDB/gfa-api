<?php

namespace App\Http\Resources\gouvernance;

use Carbon\Carbon;
use Illuminate\Http\Resources\Json\JsonResource;

class FormulairesDeGouvernanceResource extends JsonResource
{
    protected $can_load_response = false;
    protected $soumissionId = null;

    public function __construct($resource, $can_load_response = false, $soumissionId = null)
    {
        // Call the parent constructor to initialize the resource
        parent::__construct($resource);
        $this->can_load_response = $can_load_response;
        $this->soumissionId = $soumissionId;
    }

    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array|\Illuminate\Contracts\Support\Arrayable|\JsonSerializable
     */
    public function toArray($request)
    {
        $categories = $this->categories_de_gouvernance()->whereNull('categorieDeGouvernanceId')->with('questions_de_gouvernance')->get();
        return [
            'id' => $this->secure_id,
            'libelle' => $this->libelle,
            'description' => $this->description,
            'type' => $this->type,
            'lien' => $this->lien,
            'annee_exercice' => $this->annee_exercice,
            'created_by' => $this->createdBy->secure_id,
            'programmeId' => $this->programme->secure_id,
            'created_at' => Carbon::parse($this->created_at)->format("Y-m-d"),
            'options_de_reponse' => $this->options_de_reponse->map(function($option){
                return [
                    "id"                    => $option->secure_id,
                    'libelle'               => $option->libelle,
                    'slug'                  => $option->slug,
                    'point'                 => $option->pivot->point
                ];
            }),
            'categories_de_gouvernance' => $categories->map(function($categorieDeGouvernance){
                return new CategoriesDeGouvernanceResource($categorieDeGouvernance, $this->can_load_response, $this->soumissionId);
            }),
            //'categories_de_gouvernance' => CategoriesDeGouvernanceResource::collection($this->categories_de_gouvernance()->whereNull('categorieDeGouvernanceId')->with('questions_de_gouvernance')->get()),
            
            /*
            'categories_de_gouvernance' => CategoriesDeGouvernanceResource::collection($this->categories_de_gouvernance->load([
                'questions_de_gouvernance' => function($query) use ($formulaireDeGouvernanceId) {
                    $query->when($formulaireDeGouvernanceId, function($q) use ($formulaireDeGouvernanceId) {
                        return $q->where('formulaireDeGouvernanceId', $formulaireDeGouvernanceId);
                    });
                }
            ])),
            */
            //'categories_de_gouvernance' => $this->categories_de_gouvernance->load(['questions_de_gouvernance'])//QuestionsDeGouvernanceResource::collection($this->questions_operationnelle)
        ];
    }
}
