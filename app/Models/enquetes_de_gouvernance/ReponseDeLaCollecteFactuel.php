<?php

namespace App\Models\enquetes_de_gouvernance;

use App\Models\Fichier;
use App\Models\Programme;
use Exception;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use SaiAshirwadInformatia\SecureIds\Models\Traits\HasSecureIds;

class ReponseDeLaCollecteFactuel extends Model
{
    protected $table = 'reponses_de_la_collecte_factuel';
    public $timestamps = true;

    use HasSecureIds, HasFactory;

    protected $dates = ['deleted_at'];

    protected $fillable = array("point", 'preuveIsRequired', 'description', 'sourceDeVerification', 'sourceDeVerificationId', 'optionDeReponseId', 'questionId', 'soumissionId', 'programmeId');

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
                $reponse_de_la_collecte->preuves_de_verification()->delete();

                DB::commit();
            } catch (\Throwable $th) {
                DB::rollBack();

                throw new Exception($th->getMessage(), 1);
            }
        });
    }

    /**
     * Get the source de verification associated with the reponse de la collecte.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function source_de_verification()
    {
        return $this->belongsTo(SourceDeVerification::class, 'sourceDeVerificationId');
    }

    public function soumission()
    {
        return $this->belongsTo(SoumissionFactuel::class, 'soumissionId');
    }

    public function option_de_reponse()
    {
        return $this->belongsTo(OptionDeReponseGouvernance::class, 'optionDeReponseId');
    }

    public function question()
    {
        return $this->belongsTo(QuestionFactuelDeGouvernance::class, 'questionId');
    }

    public function programme()
    {
        return $this->belongsTo(Programme::class, 'programmeId');
    }

    public function preuves_de_verification()
    {
        return $this->morphMany(Fichier::class, "fichiertable");
    }

    public function getPourcentageEvolutionAttribute()
    {
        $donnees_collectees = 0;
        $donnees_attendues = $pourcentage_collecte = 0;

        $donnees_collectees = 5;

        if ($this->preuveIsRequired) {
            $donnees_attendues = 7;
        }
        elseif ($this->descriptionIsRequired) {
            $donnees_attendues = 6;
        }else {
            $donnees_attendues = 5;
        }

        //array('soumissionId', 'questionId', 'optionDeReponseId', 'programmeId');
        //array("point", 'sourceDeVerification', 'sourceDeVerificationId');

        if ($this->preuveIsRequired) {
            if ($this->preuves_de_verification && $this->preuves_de_verification->count() > 0) {
                $donnees_collectees++;
            }

            if ($this->sourceDeVerification || $this->sourceDeVerificationId) {
                $donnees_collectees++;
            }
        }
        elseif ($this->descriptionIsRequired) {

            if (!empty($this->description)) {
                $donnees_collectees++;
            }
        }

        // Eviter la division par zéro
        if ($donnees_attendues != 0) {
            $pourcentage_collecte = ($donnees_collectees / $donnees_attendues) * 100;
        }

        return $pourcentage_collecte;
    }
}
