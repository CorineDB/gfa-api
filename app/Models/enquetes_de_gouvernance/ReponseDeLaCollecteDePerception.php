<?php

namespace App\Models\enquetes_de_gouvernance;

use App\Models\Programme;
use Exception;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use SaiAshirwadInformatia\SecureIds\Models\Traits\HasSecureIds;

class ReponseDeLaCollecteDePerception extends Model
{
    protected $table = 'reponses_de_la_collecte_de_perception';
    public $timestamps = true;

    use HasSecureIds, HasFactory ;

    protected $dates = ['deleted_at'];

    protected $fillable = array("point", 'soumissionId', 'questionId', 'optionDeReponseId', 'programmeId');

    protected $casts = [
        "point" => 'float'
    ];

    protected $appends = ['pourcentage_evolution'];

    protected static function boot()
    {
        parent::boot();

        static::deleted(function ($reponse_de_la_collecte) {

            DB::beginTransaction();
            try {

                DB::commit();
            } catch (\Throwable $th) {
                DB::rollBack();

                throw new Exception($th->getMessage(), 1);
            }
        });
    }
    
    public function soumission()
    {
        return $this->belongsTo(SoumissionDePerception::class, 'soumissionId');
    }

    public function option_de_reponse()
    {
        return $this->belongsTo(OptionDeReponseGouvernance::class, 'optionDeReponseId');
    }

    public function question()
    {
        return $this->belongsTo(QuestionDePerceptionDeGouvernance::class, 'questionId');
    }

    public function programme()
    {
        return $this->belongsTo(Programme::class, 'programmeId');
    }

    public function getPourcentageEvolutionAttribute()
    {
        $donnees_collectees = 0;
        $donnees_attendues = $pourcentage_collecte = 0;

        $donnees_attendues = 5;

        $donnees_collectees = 5;
            //array("point", 'soumissionId', 'questionId', 'optionDeReponseId', 'programmeId');

        // Eviter la division par zéro
        if ($donnees_attendues != 0) {
            $pourcentage_collecte = ($donnees_collectees / $donnees_attendues) * 100;
        }

        return $pourcentage_collecte;
    }

}
