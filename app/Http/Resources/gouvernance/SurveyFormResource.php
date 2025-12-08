<?php

namespace App\Http\Resources\gouvernance;

use Carbon\Carbon;
use Illuminate\Http\Resources\Json\JsonResource;

class SurveyFormResource extends JsonResource
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
            'id'            => $this->secure_id,
            'libelle'       => $this->libelle,
            'description'   => $this->description,
            'form_data'     => $this->form_data,
            'created_at'    => Carbon::parse($this->created_at)->format("Y-m-d")
        ];
    }
}
