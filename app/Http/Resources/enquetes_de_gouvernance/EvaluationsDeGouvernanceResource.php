<?php

namespace App\Http\Resources\enquetes_de_gouvernance;

use App\Http\Resources\enquetes_de_gouvernance\formulaires_de_gouvernance_de_perception\ListFormulaireDeGouvernanceDePerceptionResource;
use App\Http\Resources\enquetes_de_gouvernance\formulaires_de_gouvernance_factuel\ListFormulaireDeGouvernanceFactuelResource;
use App\Models\Organisation;
use App\Models\UniteeDeGestion;
use Carbon\Carbon;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Auth;

class EvaluationsDeGouvernanceResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array|\Illuminate\Contracts\Support\Arrayable|\JsonSerializable
     */
    public function toArray($request)
    {
        $user = $request->user();

        return [
            'id' => $this->secure_id,
            'intitule' => $this->intitule,
            'description' => $this->description,
            'debut' => Carbon::parse($this->debut)->format("Y-m-d"),
            'fin' => Carbon::parse($this->fin)->format("Y-m-d"),
            'annee_exercice' => $this->annee_exercice,
            'statut' => $this->statut,
            'pourcentage_evolution' => $this->pourcentage_evolution,
            'pourcentage_evolution_organisations' => 0,//$this->pourcentage_evolution_organisations,
            'pourcentage_evolution_factuel_organisations' => 0,//$this->pourcentage_evolution_factuel_organisations,
            'pourcentage_evolution_perception_organisations' => 0,//$this->pourcentage_evolution_perception_organisations,
            'pourcentage_evolution_des_soumissions_factuel' => $this->pourcentage_evolution_des_soumissions_factuel,

            $this->mergeWhen(((Auth::user()->type == 'unitee-de-gestion') || get_class(auth()->user()->profilable) == UniteeDeGestion::class), function(){
                return [
                    'pourcentage_evolution_organisations'               => $this->pourcentage_evolution_organisations,
                    'pourcentage_evolution_perception_organisations'    => $this->pourcentage_evolution_perception_organisations,
                    'pourcentage_evolution_factuel_organisations'       => $this->pourcentage_evolution_factuel_organisations,
                ];
            }),

            $this->mergeWhen(((Auth::user()->type == 'organisation') || get_class(auth()->user()->profilable) == Organisation::class), function(){
                return [

                    //'pourcentage_evolution_des_soumissions_de_perception' => optional(Auth::user()->profilable)->getPerceptionSubmissionsCompletionAttribute($this->id) ?? 0,

                    'pourcentage_evolution_organisations'       => optional(Auth::user()->profilable)->getSubmissionRateAttribute($this->id) ?? 0,
                    'pourcentage_evolution_perception_organisations'     => optional(Auth::user()->profilable)->getPerceptionSubmissionRateAttribute($this->id) ?? 0,
                    'pourcentage_evolution_factuel_organisations'    => optional(Auth::user()->profilable)->getFactuelSubmissionRateAttribute($this->id) ?? 0,
                ];
            }),

            // Perception

            $this->mergeWhen(((Auth::user()->type == 'unitee-de-gestion') || get_class(auth()->user()->profilable) == UniteeDeGestion::class), function(){
                return [
                    'pourcentage_evolution_des_soumissions_de_perception' => $this->pourcentage_evolution_des_soumissions_de_perception,
                ];
            }),

            $this->mergeWhen(((Auth::user()->type == 'organisation') || get_class(auth()->user()->profilable) == Organisation::class), function(){
                return [

                    //'pourcentage_evolution_des_soumissions_de_perception' => optional(Auth::user()->profilable)->getPerceptionSubmissionsCompletionAttribute($this->id) ?? 0,

                    'pourcentage_evolution_des_soumissions_de_perception' => optional(Auth::user()->profilable)->getPerceptionSubmissionsCompletionRateAttribute($this->id) ?? 0,
                    'nbreDeParticipants' => optional(Auth::user()->profilable)->getNbreDeParticipantsAttribute($this->id) ?? 0
                ];
            }),

            'total_participants_evaluation_factuel' => $this->total_participants_evaluation_factuel,
            'total_participants_evaluation_de_perception' => $this->total_participants_evaluation_de_perception,

            'total_soumissions_factuel' => $this->total_soumissions_factuel,
            'total_soumissions_de_perception' => $this->total_soumissions_de_perception,
            'total_soumissions_factuel_non_demarrer' => $this->total_soumissions_factuel_non_demarrer,
            'total_soumissions_de_perception_non_demarrer' => $this->total_soumissions_de_perception_non_demarrer,

            'total_soumissions_factuel_terminer' => $this->total_soumissions_factuel_terminer,
            'total_soumissions_de_perception_terminer' => $this->total_soumissions_de_perception_terminer,

            'organisations_ranking' => $this->organisations_ranking,
            'options_de_reponse_stats' => $this->options_de_reponse_gouvernance_stats,
            'programmeId' => $this->programme->secure_id,
            'created_at' => Carbon::parse($this->created_at)->format("Y-m-d"),
            'formulaire_factuel_de_gouvernance' => $this->formulaire_factuel_de_gouvernance() ? $this->formulaire_factuel_de_gouvernance()->secure_id : null,
            'formulaire_de_perception_de_gouvernance' => $this->formulaire_de_perception_de_gouvernance() ? $this->formulaire_de_perception_de_gouvernance()->secure_id : null,
            'formulaires_de_gouvernance' => [new ListFormulaireDeGouvernanceFactuelResource($this->formulaire_factuel_de_gouvernance()), new ListFormulaireDeGouvernanceDePerceptionResource($this->formulaire_de_perception_de_gouvernance())],

            'organisations' => $this->organisations->map(function($organisation){
                return [
                    "id"                    => $organisation->secure_id,
                    'nom'                   => optional($organisation->user)->nom ?? null,
                    'sigle'                 => $this->when($organisation->sigle, $organisation->sigle),
                    'code'                  => $this->when($organisation->code, $organisation->code),
                    'nom_point_focal'       => $organisation->nom_point_focal,
                    'prenom_point_focal'    => $organisation->prenom_point_focal,
                    'contact_point_focal'   => $organisation->contact_point_focal
                ];
            })
        ];
    }
}
