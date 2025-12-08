<?php

namespace App\Models;

use App\Http\Resources\gouvernance\OptionsDeReponseResource;
use Exception;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use SaiAshirwadInformatia\SecureIds\Models\Traits\HasSecureIds;

class FormulaireDeGouvernance extends Model
{
    protected $table = 'formulaires_de_gouvernance';
    
    public $timestamps = true;

    use HasSecureIds, HasFactory ;

    protected $dates = ['deleted_at'];

    protected $fillable = array('libelle', 'description', 'type', 'lien', 'created_by', 'programmeId', 'annee_exercice');

    protected $casts = [];

    protected $with = [];

    protected static function boot()
    {
        parent::boot();

        static::deleting(function ($formulaire_de_gouvernance) {
            if ($formulaire_de_gouvernance->evaluations_de_gouvernance->count() > 0) {
                // Prevent deletion by throwing an exception
                throw new Exception("Impossible de supprimer ce formulaire de gouvernance. Veuillez d'abord supprimer toutes les évaluations associées.");
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

    public function categories_de_gouvernance()
    {
        return $this->hasMany(CategorieDeGouvernance::class, 'formulaireDeGouvernanceId')->whereNull('categorieDeGouvernanceId');
    }

    public function all_categories_de_gouvernance()
    {
        return $this->hasMany(CategorieDeGouvernance::class, 'formulaireDeGouvernanceId');
    }

    public function categorie_de_gouvernance()
    {
        return $this->belongsToMany(CategorieDeGouvernance::class, 'questions_de_gouvernance', 'formulaireDeGouvernanceId', 'categorieDeGouvernanceId')->wherePivotNull('deleted_at')->withPivot(['id', 'type', 'position', 'indicateurDeGouvernanceId', 'programmeId']);
    }

    public function questions_de_gouvernance($annee_exercice = null)
    {
        return $this->hasMany(QuestionDeGouvernance::class, 'formulaireDeGouvernanceId');
    }

    public function options_de_reponse()
    {
        return $this->belongsToMany(OptionDeReponse::class,'formulaire_options_de_reponse', 'formulaireDeGouvernanceId', 'optionId')->wherePivotNull('deleted_at')->withPivot(["id", "point", "preuveIsRequired"]);
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
        return $this->belongsToMany(EvaluationDeGouvernance::class,'evaluation_formulaires_de_gouvernance', 'formulaireDeGouvernanceId', 'evaluationDeGouvernanceId')->wherePivotNull('deleted_at');
    }

    public function principes_de_gouvernance()
    {
        return $this->categories_de_gouvernance->map(function($categorie_de_gouvernance){
            return $categorie_de_gouvernance->categorieable;
        });
    }
}