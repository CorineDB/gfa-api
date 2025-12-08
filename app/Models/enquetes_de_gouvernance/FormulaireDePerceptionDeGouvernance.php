<?php

namespace App\Models\enquetes_de_gouvernance;

use App\Models\Programme;
use App\Models\User;
use Exception;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use SaiAshirwadInformatia\SecureIds\Models\Traits\HasSecureIds;

class FormulaireDePerceptionDeGouvernance extends Model
{
    protected $table = 'formulaires_de_perception_de_gouvernance';

    public $timestamps = true;

    use HasSecureIds, HasFactory ;

    protected $dates = ['deleted_at'];

    protected $fillable = array('libelle', 'description', 'created_by', 'programmeId');

    protected $casts = [];

    protected $with = [];

    protected static function boot()
    {
        parent::boot();

        static::deleting(function ($formulaire_de_gouvernance) {
            if ($formulaire_de_gouvernance->evaluations_de_gouvernance->count() > 0) {
                // Prevent deletion by throwing an exception
                throw new Exception("Impossible de supprimer ce formulaire factuel de gouvernance. Veuillez d'abord supprimer toutes les évaluations associées.");
            }
        });

        static::deleted(function ($formulaire_de_gouvernance) {

            DB::beginTransaction();
            try {

                $formulaire_de_gouvernance->options_de_reponse()->delete();
                $formulaire_de_gouvernance->questions_de_gouvernance()->delete();
                $formulaire_de_gouvernance->categories_de_gouvernance()->delete();
                $formulaire_de_gouvernance->categorie_de_gouvernance()->detach();
                $formulaire_de_gouvernance->evaluations_de_gouvernance()->delete();

                DB::commit();
            } catch (\Throwable $th) {
                DB::rollBack();

                throw new Exception($th->getMessage(), 1);
            }
        });
    }
    public function fichesDeSynthese()
    {
        return $this->morphMany(FicheDeSynthese::class, 'formulaireDeGouvernance');
    }

    public function categories_de_gouvernance()
    {
        return $this->hasMany(CategorieDePerceptionDeGouvernance::class, 'formulaireDePerceptionId')
                    ->whereNull('categorieDePerceptionDeGouvernanceId')
                    ->orderBy('position','asc');
    }

    public function all_categories_de_gouvernance()
    {
        return $this->hasMany(CategorieDePerceptionDeGouvernance::class, 'formulaireDePerceptionId');
    }

    public function categorie_de_gouvernance()
    {
        return $this->belongsToMany(CategorieDePerceptionDeGouvernance::class, 'questions_de_perception_de_gouvernance', 'formulaireDePerceptionId', 'categorieDePerceptionDeGouvernanceId')->wherePivotNull('deleted_at')->withPivot(['id', 'position', 'questionOperationnelleId', 'programmeId']);
    }

    public function questions_de_gouvernance($annee_exercice = null)
    {
        return $this->hasMany(QuestionDePerceptionDeGouvernance::class, 'formulaireDePerceptionId');
    }

    public function options_de_reponse()
    {
        return $this->belongsToMany(OptionDeReponseGouvernance::class,'formulaire_de_perception_options', 'formulaireDePerceptionId', 'optionId')->withPivot(["id", "point", "preuveIsRequired", "sourceIsRequired", "descriptionIsRequired", 'programmeId']);
    }

    public function programme()
    {
        return $this->belongsTo(Programme::class, 'programmeId');
    }

    public function createdBy()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function evaluations_de_gouvernance()
    {
        return $this->belongsToMany(EvaluationDeGouvernance::class,'evaluation_de_gouvernance_formulaires', 'formulaireDePerceptionId', 'evaluationDeGouvernanceId')->wherePivotNull('deleted_at')->whereNotNull('formulaireDePerceptionId');
    }

    public function principes_de_gouvernance()
    {
        return $this->categories_de_gouvernance->map(function($categorie_de_gouvernance){
            return $categorie_de_gouvernance->categorieable;
        });
    }

    public function loadForm()
    {
        return $this->load([
            'categories_de_gouvernance' => function ($query) {
                $query->with([
                    'questions_de_gouvernance' => function ($q) {
                        $q->orderBy('position');
                    }
                ]);
            }
        ]);
    }
}