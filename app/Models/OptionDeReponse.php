<?php

namespace App\Models;

use Exception;

use Illuminate\Support\Str;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use SaiAshirwadInformatia\SecureIds\Models\Traits\HasSecureIds;

class OptionDeReponse extends Model
{
    protected $table = 'options_de_reponse';
    public $timestamps = true;

    use HasSecureIds, HasFactory ;

    protected $dates = ['deleted_at'];

    protected $fillable = array('libelle', 'slug', 'description', 'programmeId');

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
                $option_de_reponse->formulaires_de_gouvernance()->detach();

                DB::commit();
            } catch (\Throwable $th) {
                DB::rollBack();

                throw new Exception($th->getMessage(), 1);
            }
        });
    }

    public function indicateurs_de_gouvernance()
    {
        return $this->belongsToMany(IndicateurDeGouvernance::class,'indicateur_options_de_reponse', 'optionId', 'indicateurId')->wherePivotNull('deleted_at');
    }

    public function programme()
    {
        return $this->belongsTo(Programme::class, 'programmeId');
    }

    public function reponses()
    {
        return $this->hasMany(ReponseDeLaCollecte::class, 'optionDeReponseId');
    }

    public function formulaires_de_gouvernance()
    {
        return $this->belongsToMany(FormulaireDeGouvernance::class,'formulaire_options_de_reponse', 'optionId', 'formulaireDeGouvernanceId')->wherePivotNull('deleted_at')->withPivot(["id", "point", "preuveIsRequired"]);
    }

    /*public function getNoteAttribute()
    {
        $value = 0;

        if($this->slug){
            
            switch ($this->slug) {
                case 'oui':
                    $value = 1;
                    break;
                    
                case 'non':
                    $value = 0;
                    break;

                case 'ne-peux-repondre':
                    $value = 1;
                    break;

                case 'pas-du-tout':
                    $value = 2;
                    break;

                case 'faiblement':
                    $value = 3;
                    break;

                case 'moyennement':
                    $value = 4;
                    break;

                case 'dans-une-grande-mesure':
                    $value = 5;
                    break;

                case 'totalement':
                    $value = 6;
                    break;

                default:
                    $value = 0;
                    break;
            }
        }
        // Return a default value if no observation matches
        return $value; // or null, depending on your requirement
    }*/
}
