<?php

namespace App\Http\Resources\bailleurs;

use App\Http\Resources\fichiers\FichiersResource;
use App\Http\Resources\user\UserResource;
use Carbon\Carbon;
use Illuminate\Http\Resources\Json\JsonResource;

class BailleursResource extends JsonResource
{
    private $code;
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
            "code" => $this->when( !($request->user()->hasRole("super-admin", "administrateur")), function() use ($request) {
                return intval( optional($this->codes( $request->user()->programme->id )->first())->codePta );
            }),
            "sigle" => $this->sigle,
            "pays" => $this->pays,
            "user" => new UserResource($this->whenLoaded('user')),
            $this->mergeWhen($request->user()->hasRole("super-admin", "administrateur"), function(){
               return [
                "programme" =>$this->codes->map(function($code){
                    return [
                        "id" => $code->programmeId,
                        "codePta" => intval($code->codePta)
                    ];
                })
               ];
            }),

            "logo" => new FichiersResource($this->user->logo),
              "created_at" => Carbon::parse($this->created_at)->format("Y-m-d h:i:s")
        ];
    }
}
