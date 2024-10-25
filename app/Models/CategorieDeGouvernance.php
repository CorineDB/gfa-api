<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use SaiAshirwadInformatia\SecureIds\Models\Traits\HasSecureIds;

class CategorieDeGouvernance extends Model
{
    protected $table = 'categories_de_gouvernance';
    public $timestamps = true;

    use HasSecureIds, HasFactory ;

    protected $dates = ['deleted_at'];

    protected $fillable = array('categorieable_id', 'categorieable_type', 'categorieDeGouvernanceId', 'programmeId');

    protected $casts = [];

    protected $with = ["categorieable"];

    protected static function boot()
    {
        parent::boot();
    }

    public function sousCategoriesDeGouvernance()
    {
        return $this->hasMany(CategorieDeGouvernance::class, 'categorieDeGouvernanceId');
    }

    public function categorieDeGouvernanceParent()
    {
        return $this->belongsTo(CategorieDeGouvernance::class, 'categorieDeGouvernanceId');
    }

    public function programme()
    {
        return $this->belongsTo(Programme::class, 'programmeId');
    }

    public function categorieable()
    {
        return $this->morphTo();
    }

    public function formulaires_de_gouvernance()
    {
        return $this->belongsToMany(FormulaireDeGouvernance::class, 'questions_de_gouvernance', 'categorieDeGouvernanceId', 'formulaireDeGouvernanceId')->wherePivotNull('deleted_at')->withPivot(['id', 'type', 'indicateurDeGouvernanceId', 'programmeId']);
    }

    public function questions_de_gouvernance()
    {
        return $this->hasMany(QuestionDeGouvernance::class, 'categorieDeGouvernanceId');
    }
}
