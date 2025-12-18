<?php

namespace App\Http\Resources;

use Carbon\Carbon;
use Illuminate\Http\Resources\Json\JsonResource;

class HistoriquesResource extends JsonResource
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
            "id" => $this->id,
            "action" =>$this->log_name,
            "description" =>$this->description,
            "ip"  =>$this->ipAdresse,
            "agent"  =>$this->userAgent,
            "created_at" => Carbon::parse($this->created_at)->format("Y-m-d H:m")
        ];
    }
}
