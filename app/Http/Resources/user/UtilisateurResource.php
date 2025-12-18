<?php

namespace App\Http\Resources\user;

use Carbon\Carbon;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Auth;

class UtilisateurResource extends JsonResource
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
            "id" => $this->secure_id,
            'sigle' => $this->when($this->sigle, $this->sigle),
            'code' => $this->when($this->code, $this->code),
            "user" => new UserResource($this->user),
            "mod" => $this->when($this->user->hasRole("entreprise-executant"), function(){
                return $this->modByProgramme(Auth::user()->programmeId);
            }),
            "created_at" => Carbon::parse($this->created_at)->format("Y-m-d h:i:s")
        ];
    }
}
