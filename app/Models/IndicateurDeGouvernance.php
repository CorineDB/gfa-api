<?php

namespace App\Models;

use Exception;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use SaiAshirwadInformatia\SecureIds\Models\Traits\HasSecureIds;

class IndicateurDeGouvernance extends Model
{
    protected $table = 'indicateurs_de_gouvernance';
    public $timestamps = true;

    use HasSecureIds, HasFactory ;

    protected $dates = ['deleted_at'];

    protected $fillable = array('nom', 'description', 'type', 'can_have_multiple_reponse');

    protected $attributes = ["can_have_multiple_reponse" => false];

    protected $casts = ["can_have_multiple_reponse" => 'boolean'];

    protected $with = ['options_de_reponse'];

    /**
     * The attributes that should be appended to the model's array form.
     *
     * @var array
     */
    protected $appends = [];

    protected static function boot()
    {
        parent::boot();

        static::deleted(function ($indicateur_de_gouvernance) {

            DB::beginTransaction();

            try {

                $indicateur_de_gouvernance->update([
                    'nom' => time() . '::' . $indicateur_de_gouvernance->nom
                ]);

                DB::commit();
            } catch (\Throwable $th) {
                DB::rollBack();

                throw new Exception($th->getMessage(), 1);
            }
        });
    }

    /**
     * Renvoie la liste des catégories de gouvernance liées au indicateur de gouvernance
     * Si l'année d'exercice est fournie, seules les catégories liées  des formulaires
     * de gouvernance de l'année d'exercice sont renvoyées.
     *
     * @param int|null $annee_exercice L'année d'exercice
     * @return \Illuminate\Database\Eloquent\Relations\MorphMany
     */
    public function categories_de_gouvernance($annee_exercice = null)
    {
        $categories_de_gouvernance = $this->morphMany(CategorieDeGouvernance::class, 'categorieable');

        if($annee_exercice){
            $categories_de_gouvernance = $categories_de_gouvernance->whereHas("questions_de_gouvernance.formulaire_de_gouvernance", function($query) use ($annee_exercice){
                $query->where('annee_exercice', $annee_exercice);
            });
        }

        return $categories_de_gouvernance;
    }

    public function options_de_reponse()
    {
        return $this->belongsToMany(OptionDeReponse::class,'indicateur_options_de_reponse', 'indicateurId', 'optionId')->wherePivotNull('deleted_at');
    }

    public function observations()
    {
        return $this->hasMany(ReponseCollecter::class, 'indicateurDeGouvernanceId');
    }
}