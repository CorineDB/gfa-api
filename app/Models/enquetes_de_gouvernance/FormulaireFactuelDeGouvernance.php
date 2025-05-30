<?php

namespace App\Models\enquetes_de_gouvernance;

use App\Models\Programme;
use App\Models\User;
use Exception;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use SaiAshirwadInformatia\SecureIds\Models\Traits\HasSecureIds;

class FormulaireFactuelDeGouvernance extends Model
{
    protected $table = 'formulaires_factuel_de_gouvernance';

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
        return $this->hasMany(CategorieFactuelDeGouvernance::class, 'formulaireFactuelId')
                    ->whereNull('categorieFactuelDeGouvernanceId')
                    ->orderBy('position','asc');
    }

    public function all_categories_de_gouvernance()
    {
        return $this->hasMany(CategorieFactuelDeGouvernance::class, 'formulaireFactuelId');
    }

    public function categorie_de_gouvernance()
    {
        return $this->belongsToMany(CategorieFactuelDeGouvernance::class, 'questions_factuel_de_gouvernance', 'formulaireFactuelId', 'categorieFactuelDeGouvernanceId')->wherePivotNull('deleted_at')->withPivot(['id', 'position', 'indicateurFactuelDeGouvernanceId', 'programmeId']);
    }

    public function questions_de_gouvernance($annee_exercice = null)
    {
        return $this->hasMany(QuestionFactuelDeGouvernance::class, 'formulaireFactuelId');
    }

    public function options_de_reponse()
    {
        return $this->belongsToMany(OptionDeReponseGouvernance::class,'formulaire_factuel_options', 'formulaireFactuelId', 'optionId')->withPivot(["id", "point", "preuveIsRequired", "sourceIsRequired", "descriptionIsRequired", 'programmeId']);
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
        return $this->belongsToMany(EvaluationDeGouvernance::class,'evaluation_de_gouvernance_formulaires', 'formulaireFactuelId', 'evaluationDeGouvernanceId')->wherePivotNull('deleted_at')->whereNotNull('formulaireDePerceptionId');
    }

    public function principes_de_gouvernance()
    {
        return $this->all_categories_de_gouvernance()->whereNotNull('categorieFactuelDeGouvernanceId')->whereHas('categorieDeGouvernanceParent', function($query){
            $query->where("categorieable_type", TypeDeGouvernanceFactuel::class);
        })->get()->map(function($categorie_de_gouvernance){
            return $categorie_de_gouvernance->categorieable;
        })
        ->filter(); // remove any null categorieable
    }

    public function principesIds()
    {
        return $this->principes_de_gouvernance()->pluck("id");
    }
}