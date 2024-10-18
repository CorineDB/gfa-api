<?php

namespace App\Http\Resources\gouvernance;

use App\Models\Indicateur;
use App\Models\IndicateurDeGouvernance;
use App\Models\ReponseCollecter;
use App\Models\TypeDeGouvernance;
use Illuminate\Http\Resources\Json\JsonResource;
use Carbon\Carbon;

class EnqueteDeGouvernanceResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array|\Illuminate\Contracts\Support\Arrayable|\JsonSerializable
     */
    public function toArray($request)
    {

        // Load responses with their associated organisation and indicator governance
        $responses = $this->reponses_collecter->load(['organisation', 'indicateurDeGouvernance']);

        // Group the responses by organization
        $groupedResponses = $responses->groupBy('organisation.id')->map(function ($organisationResponses, $organisationId) {
            $organisation = optional(optional($organisationResponses->first())->organisation);
            // Collect organisation information without using an 'organisation' key
            $organisationInfo = [
                'id' => $organisation->secure_id ?? null,
                'nom' => $organisation->user->nom ?? null,
                'nom_point_focal' => $organisation->nom_point_focal ?? null,
                'prenom_point_focal' => $organisation->prenom_point_focal ?? null,
                'contact_point_focal' => $organisation->contact_point_focal ?? null,
                "submitted_by"=>  ReponseCollecter::where('organisationId', $organisationId)->where('enqueteDeCollecteId', $this->id)->orderByDesc("created_at")->first()->user,

                "submitted_at"=>  Carbon::parse(ReponseCollecter::where('organisationId', $organisationId)->where('enqueteDeCollecteId', $this->id)->orderByDesc("updated_at")->first()->updated_at)->format("Y-m-d H:i:s"),
                'levelOfSubmission' => $this->getLevelOfSubmission($organisationId)
            ];

            // Group observations by category
            $observations = $organisationResponses->groupBy('indicateurDeGouvernance.type')->map(function ($categorieResponses, $type) {
                return 
                    $categorieResponses->map(function ($response) {
                        return [
                            'indicateurDeGouvernance' => [

                                'id' => $response->indicateurDeGouvernance->secure_id,
                                'nom' => $response->indicateurDeGouvernance->nom
                            ],
                            'reponse' => [
                                'id' => $response->optionDeReponse->secure_id,
                                'libelle' => $response->optionDeReponse->libelle,
                                'slug' => $response->optionDeReponse->slug
                            ],
                            'source' => $response->source,
                            'commentaire' => $response->commentaire,
                        ];
                    })->toArray();
            })->toArray(); // Convert to array

            // Return combined data as a flat structure
            return array_merge($organisationInfo, ['observations' => $observations]);
        })->values()->toArray(); // Convert to array and reset keys

        // Format the final structured output as an array
        return [
            'id' => $this->secure_id,
            'nom' => $this->nom,
            'objectif' => $this->objectif,
            'description' => $this->description,
            'debut' => Carbon::parse($this->debut),
            'fin' => Carbon::parse($this->fin),
            'programmeId' => $this->programme->secure_id,
            'reponses' => $groupedResponses
        ];
    }

    public function getLevelOfSubmission($organisationId)
    {
        // Step 1: Count the number of responses collected for the organization
        $responsesCount = ReponseCollecter::where('organisationId', $organisationId)->where('enqueteDeCollecteId', $this->id)->count();

        // Step 2: Count the number of indicators in the form associated with the organization
        //$form = TypeDeGouvernance::where('programmeId', auth()->user()->programme->id)->loadCount('indicateurs', $organisationId);

        $indicateursCount = IndicateurDeGouvernance::where(function($query){
            $query->where("principeable_type", "App\\Models\\PrincipeDeGouvernance")->with('principeable', function ($query) {
                $query->whereHas('type_de_gouvernance', function ($type_query) {
                    //dd(auth()->user()->programme->id);
                    $type_query->where('programmeId', auth()->user()->programme->id);
                });
            });
        })->orWhere(function($cq){
            $cq->where("principeable_type", "App\\Models\\CritereDeGouvernance")
                ->with('principeable', function ($query) {
                    $query->whereHas('principe_de_gouvernance', function ($type_query) {
                        $type_query->whereHas("type_de_gouvernance", function($type_query){
                            $type_query->where('programmeId', auth()->user()->programme->id);
                        });
                    });
                });
        })->count();

        // Step 3: Calculate the level of submission
        if ($indicateursCount > 0) {
            $levelOfSubmission = ($responsesCount / $indicateursCount) * 100;
        } else {
            $levelOfSubmission = 0; // If there are no indicators, the submission level is 0
        }

        // Return the calculated level of submission
        return $levelOfSubmission;
    }

}
