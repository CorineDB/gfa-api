<?php

namespace App\Models;

use Exception;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use SaiAshirwadInformatia\SecureIds\Models\Traits\HasSecureIds;

class ReponseDeLaCollecte extends Model
{
    protected $table = 'reponses_de_la_collecte';
    public $timestamps = true;

    use HasSecureIds, HasFactory ;

    protected $dates = ['deleted_at'];

    protected $fillable = array("point", "type", 'sourceDeVerification', 'soumissionId', 'sourceDeVerificationId', 'questionId', 'optionDeReponseId', 'preuveIsRequired', 'programmeId');

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
        return $this->belongsTo(Soumission::class, 'soumissionId');
    }

    public function option_de_reponse()
    {
        return $this->belongsTo(OptionDeReponse::class, 'optionDeReponseId');
    }

    public function question()
    {
        return $this->belongsTo(QuestionDeGouvernance::class, 'questionId');
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

        /* if(!empty($this->point)){
            $donnees_collectees++;
        } */

        if(!empty($this->type)){
            $donnees_collectees++;
        }

        if($this->type == 'indicateur'){

            if($this->preuveIsRequired){
                $donnees_attendues = 8;
            }
            else{
                $donnees_attendues = 7;
            }

            //array('soumissionId', 'questionId', 'optionDeReponseId', 'programmeId');
            //array("point", "type", 'sourceDeVerification', 'sourceDeVerificationId');

            if($this->sourceDeVerification || $this->sourceDeVerificationId){
                $donnees_collectees++;
            }

            if($this->preuveIsRequired){
                if($this->preuves_de_verification && $this->preuves_de_verification->count() > 0){
                    $donnees_collectees++;
                }
            }
            
        }
        else if($this->type == 'question_operationnelle'){
            //array("point", "type", 'soumissionId', 'questionId', 'optionDeReponseId', 'programmeId');

            $donnees_attendues = 6;
        }
        
        // Eviter la division par zéro
        if ($donnees_attendues != 0) {
            $pourcentage_collecte = ($donnees_collectees / $donnees_attendues) * 100;
        }

        return $pourcentage_collecte;
    }

}
