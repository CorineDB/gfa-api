<?php

namespace App\Http\Resources\gouvernance;

use App\Traits\Helpers\HelperTrait;
use Carbon\Carbon;
use Illuminate\Http\Resources\Json\JsonResource;

class SurveyResource extends JsonResource
{
    use HelperTrait;
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array|\Illuminate\Contracts\Support\Arrayable|\JsonSerializable
     */
    public function toArray($request)
    {

        $url = config("app.url");

        // If the URL is localhost, append the appropriate IP address and port
        if (strpos($url, 'localhost') == false) {
            $url = $this->getUserTypeAppUrl($this->surveyable->user);
        }

        return [
            'id'                        => $this->secure_id,
            'intitule'                  => $this->intitule,
            'description'               => $this->description,
            'nbreParticipants'          => $this->nbreParticipants,
            'debut'                     => Carbon::parse($this->debut)->format("Y-m-d"),
            'fin'                       => Carbon::parse($this->fin)->format("Y-m-d"),
            'statut'                    => $this->statut,
            'prive'                     => $this->prive,
            'surveyFormId'              => $this->survey_form->secure_id,
            'created_at'                => Carbon::parse($this->created_at)->format("Y-m-d"),
            "survey_form_link"          => $url . "/dashboard/form-individuel/{$this->token}",
            "survey_form_link_token"    => $this->token,

            // Include survey_form only if survey_response is NOT loaded
            'survey_form'           => $this->when(
                !$this->relationLoaded('survey_response'),
                fn() => new SurveyFormResource($this->survey_form)
            ),

            // Include survey_response only if it's loaded
            'survey_response'           => $this->when(
                $this->relationLoaded('survey_response'),
                fn() => new SurveyReponseResource($this->survey_response)
            )
        ];
    }
}
