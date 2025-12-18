<?php

namespace App\Http\Resources\suivis;

use App\Http\Resources\ActiviteResource;
use App\Http\Resources\activites\ActivitesResource;
use App\Http\Resources\ComposanteResource;
use App\Http\Resources\taches\TachesResource;
use App\Models\Activite;
use App\Models\ArchiveActivite;
use App\Models\ArchiveComposante;
use App\Models\ArchiveTache;
use App\Models\Composante;
use App\Models\Tache;
use Carbon\Carbon;
use Illuminate\Http\Resources\Json\JsonResource;

class SuivisResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array|\Illuminate\Contracts\Support\Arrayable|\JsonSerializable
     */
    public function toArray($request)
    {
        $morphisme =  $this->suivitable;

        return [
            "id"            => $this->secure_id,
            "poidsActuel"   => $this->poidsActuel,
            "commentaire"   => $this->commentaire,
            /*$this->mergeWhen($morphisme instanceof Tache || $morphisme instanceof ArchiveTache, ["tache" => new TachesResource($this->suivitable)]),
            $this->mergeWhen($morphisme instanceof Activite || $morphisme instanceof ArchiveActivite, ["activite" => new ActivitesResource($this->suivitable)]),
            $this->mergeWhen($morphisme instanceof Composante || $morphisme instanceof ArchiveComposante, ["composante" => new ComposanteResource($this->suivitable)]),*/
              "created_at" => Carbon::parse($this->created_at)->format("Y-m-d h:i:s")
        ];
    }
}
