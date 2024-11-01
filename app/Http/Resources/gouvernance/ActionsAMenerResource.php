<?php

namespace App\Http\Resources\gouvernance;

use App\Http\Resources\FichierResource;
use Carbon\Carbon;
use Illuminate\Http\Resources\Json\JsonResource;

class ActionsAMenerResource extends JsonResource
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
            'id' => $this->secure_id,
            'action' => $this->action,
            'statut' => $this->statut,
            'est_valider' => $this->est_valider,
            'start_at' => Carbon::parse($this->start_at)->format("Y-m-d"),
            'end_at' => Carbon::parse($this->end_at)->format("Y-m-d"),
            'validated_at' => Carbon::parse($this->validated_at)->format("Y-m-d"),
            'programmeId' => $this->programme->secure_id,
            'created_at' => Carbon::parse($this->created_at)->format("Y-m-d"),
            'actionable' => $this->actionable,
            'preuves' => FichierResource::collection($this->preuves_de_verification)
        ];
    }
}
