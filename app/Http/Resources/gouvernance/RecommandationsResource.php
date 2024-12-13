<?php

namespace App\Http\Resources\gouvernance;

use App\Models\UniteeDeGestion;
use Carbon\Carbon;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Auth;

class RecommandationsResource extends JsonResource
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
            'recommandation' => $this->recommandation,
            'recommandationable' => $this->recommandationable,
            'evaluationId' => $this->evaluation->secure_id,
            'organisation' => $this->when(((Auth::user()->type == 'unitee-de-gestion') || (get_class(auth()->user()->profilable) == UniteeDeGestion::class)), function(){
                return [
                    "id"                    => $this->organisation->secure_id,
                    'nom'                   => optional($this->organisation->user)->nom ?? null,
                    'sigle'                 => $this->when($this->organisation->sigle, $this->organisation->sigle),
                    'code'                  => $this->when($this->organisation->code, $this->organisation->code),
                    'nom_point_focal'       => $this->organisation->nom_point_focal,
                    'prenom_point_focal'    => $this->organisation->prenom_point_focal,
                    'contact_point_focal'   => $this->organisation->contact_point_focal
                ];
            }),
            'actions_a_mener' => $this->when($this->relationLoaded('actions_a_mener'), ActionsAMenerResource::collection($this->actions_a_mener)),

            'created_at' => Carbon::parse($this->created_at)->format("Y-m-d")
        ];
    }
}
