<?php

namespace App\Http\Resources\enquetes_de_gouvernance\formulaires_de_gouvernance_factuel;

use Carbon\Carbon;
use Illuminate\Http\Resources\Json\JsonResource;

class QuestionFactuelDeGouvernanceResource extends JsonResource
{
    protected bool $can_load_response = false;
    protected ?string $soumissionId = null;

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
            'nom' => $this->indicateur_de_gouvernance->nom,
            'type' => $this->type,
            'position' => $this->position,
            'score_ranges'  => $this->when($this->score_ranges, $this->score_ranges),
            'indicateur_de_gouvernance' => $this->when($this->type==='indicateur', $this->indicateur_de_gouvernance ? [
                'id' => $this->indicateur_de_gouvernance->secure_id,
                'nom' => $this->indicateur_de_gouvernance->nom
            ] : null),
            'question_operationnelle' => $this->when($this->type==='question_operationnelle', $this->indicateur_de_gouvernance ? [
                'id' => $this->indicateur_de_gouvernance->secure_id,
                'nom' => $this->indicateur_de_gouvernance->nom
            ] : null),
            'formulaireDeGouvernanceId' => $this->formulaire_de_gouvernance->secure_id,
            'programmeId' => $this->programme->secure_id,
            'created_at' => Carbon::parse($this->created_at)->format("Y-m-d"),
            'reponse_de_la_collecte'   => $this->when($this->can_load_response, new ReponsesDeLaCollecteResource($this->reponses()->where('soumissionId', $this->soumissionId)->first())),
        ];
    }
}
