<?php

namespace App\Http\Resources\gouvernance;

use Carbon\Carbon;
use Illuminate\Http\Resources\Json\JsonResource;

class CategoriesDeGouvernanceResource extends JsonResource
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

        return [
            'id' => $this->secure_id,
            'nom' => $this->categorieable->nom,
            'categorieableId' => $this->categorieable->secure_id,
            //'position' => $this->position,
            /*'categorieDeGouvernanceParent' => $this->when($this->categorieDeGouvernanceParent, function(){
                // Unset multiple relations individually
                $this->categorieDeGouvernanceParent->unsetRelation('categorieable');
                return [
                    'id' => $this->categorieDeGouvernanceParent->secure_id,
                    'nom' => $this->categorieDeGouvernanceParent->categorieable->nom,
                ];
            }),*/
            'score_ranges'  => $this->when(isset($this['score_ranges']), $this->score_ranges),
            'categorieDeGouvernanceId' => optional($this->categorieDeGouvernanceParent)->secure_id,
            'programmeId' => $this->programme->secure_id,
            'created_at' => Carbon::parse($this->created_at)->format("Y-m-d"),
            'categories_de_gouvernance' => $this->when($this->categories_de_gouvernance->count(), $this->categories_de_gouvernance->map(function($sousCategorieDeGouvernance){
                return new CategoriesDeGouvernanceResource($sousCategorieDeGouvernance, $this->can_load_response, $this->soumissionId);
            })),
            //'categories_de_gouvernance' => $this->when($this->categories_de_gouvernance->count(), CategoriesDeGouvernanceResource::collection($this->categories_de_gouvernance)),

            'questions_de_gouvernance' => $this->when(!$this->categories_de_gouvernance->count(), $this->questions_de_gouvernance->map(function($questionDeGouvernance){
                return new QuestionsDeGouvernanceResource($questionDeGouvernance, $this->can_load_response, $this->soumissionId);
            })),
            //'questions_de_gouvernance' => $this->when(!$this->categories_de_gouvernance->count(), QuestionsDeGouvernanceResource::collection($this->questions_de_gouvernance))

            //'questions_de_gouvernance' => QuestionsDeGouvernanceResource::collection($this->questions_de_gouvernance)
        ];
    }
}
