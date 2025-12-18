<?php

namespace App\Http\Resources\enquetes_de_gouvernance;

use Carbon\Carbon;
use Illuminate\Http\Resources\Json\JsonResource;

class SourcesDeVerificationResource extends JsonResource
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
            'intitule' => $this->intitule,
            'description' => $this->description,
            'programmeId' => $this->programme->secure_id,
            'created_at' => Carbon::parse($this->created_at)->format("Y-m-d")
        ];
    }
}
