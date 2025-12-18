<?php

namespace App\Models\enquetes_de_gouvernance;

use App\Models\Programme;
use Exception;

use Illuminate\Support\Str;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use SaiAshirwadInformatia\SecureIds\Models\Traits\HasSecureIds;

class OptionDeReponseGouvernance extends Model
{
    protected $table = 'options_de_reponse_gouvernance';
    public $timestamps = true;

    use HasSecureIds, HasFactory ;

    protected $dates = ['deleted_at'];

    protected $fillable = array('libelle', 'slug', 'type', 'description', 'programmeId');

    protected static function boot()
    {
        parent::boot();

        static::saving(function ($option_de_reponse) {
            if (!$option_de_reponse->slug) {
                $option_de_reponse->slug = Str::slug($option_de_reponse->libelle);
            }
        });

        static::deleting(function ($option_de_reponse) {
            if ($option_de_reponse->reponses->count() > 0) {
                // Prevent deletion by throwing an exception
                throw new Exception("Impossible de supprimer cette option de réponse. Veuillez d'abord supprimer toutes les réponses associées.");
            }
        });

        static::deleted(function ($option_de_reponse) {

            DB::beginTransaction();
            try {

                $option_de_reponse->update([
                    'nom' => time() . '::' . $option_de_reponse->nom
                ]);

                $option_de_reponse->indicateurs_de_gouvernance()->detach();
                //$option_de_reponse->formulaires_de_gouvernance()->detach();
                $option_de_reponse->formulaires_factuel_de_gouvernance()->detach();
                $option_de_reponse->formulaires_de_perception_de_gouvernance()->detach();


                DB::commit();
            } catch (\Throwable $th) {
                DB::rollBack();

                throw new Exception($th->getMessage(), 1);
            }
        });
    }

    public function indicateurs_de_gouvernance()
    {
        return $this->belongsToMany(IndicateurDeGouvernanceFactuel::class,'indicateur_options_de_reponse', 'optionId', 'indicateurId')->wherePivotNull('deleted_at');
    }

    public function programme()
    {
        return $this->belongsTo(Programme::class, 'programmeId');
    }

    public function reponses()
    {
        return $this->hasMany(ReponseDeLaCollecte::class, 'optionDeReponseId');
    }

    public function reponses_factuel()
    {
        return $this->hasMany(ReponseDeLaCollecteFactuel::class, 'optionDeReponseId')->where("type", 'factuel');
    }

    public function reponses_de_perception()
    {
        return $this->hasMany(ReponseDeLaCollecteDePerception::class, 'optionDeReponseId')->where("type", 'perception');
    }

    public function formulaires_factuel_de_gouvernance()
    {
        return $this->belongsToMany(FormulaireFactuelDeGouvernance::class,'formulaire_factuel_options', 'optionId', 'formulaireFactuelId')->withPivot(["id", "point", "preuveIsRequired", "sourceIsRequired", "descriptionIsRequired", 'programmeId']);
    }

    public function formulaires_de_perception_de_gouvernance()
    {
        return $this->belongsToMany(FormulaireDePerceptionDeGouvernance::class,'formulaire_de_perception_options', 'optionId', 'formulaireDePerceptionId')->withPivot(["id", "point", "preuveIsRequired", "sourceIsRequired", "descriptionIsRequired", 'programmeId']);
    }
}
