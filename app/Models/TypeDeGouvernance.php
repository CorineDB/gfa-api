<?php

namespace App\Models;

use Exception;
use App\Model\CategorieDeGouvernance as CategorieDeGouvernanceParent;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use SaiAshirwadInformatia\SecureIds\Models\Traits\HasSecureIds;

class TypeDeGouvernance extends Model
{
    protected $table = 'types_de_gouvernance';
    public $timestamps = true;

    use HasSecureIds, HasFactory ;

    protected $dates = ['deleted_at'];

    protected $fillable = array('nom', 'description', 'programmeId');

    protected static function boot()
    {
        parent::boot();

        static::deleted(function ($type_de_gouvernance) {

            DB::beginTransaction();
            try {

                $type_de_gouvernance->update([
                    'nom' => time() . '::' . $type_de_gouvernance->nom
                ]);

                DB::commit();
            } catch (\Throwable $th) {
                DB::rollBack();

                throw new Exception($th->getMessage(), 1);
            }
        });
    }

    public function categories_de_gouvernance($annee_exercice = null)
    {
        $categories_de_gouvernance = $this->morphMany(CategorieDeGouvernance::class, 'categorieable');

        if($annee_exercice){
            $categories_de_gouvernance = $categories_de_gouvernance->whereHas("formulaire_de_gouvernance", function($query) use ($annee_exercice){
                $query->where('annee_exercice', $annee_exercice);
            });
        }

        return $categories_de_gouvernance;
    }

    public function sous_categories_de_gouvernance($annee_exercice = null)
    {
        $sous_categories = $this->hasManyThrough(
            CategorieDeGouvernance::class,// Final Model
            CategorieDeGouvernance::class,// Intermediate Model
            'categorieable_id',
            'categorieDeGouvernanceId',
            'id',
            'id'
        );//->where("categorieable_type", get_class(new PrincipeDeGouvernance));

        if($annee_exercice){
            $sous_categories = $sous_categories->whereHas("formulaire_de_gouvernance", function($query) use ($annee_exercice){
                $query->where('annee_exercice', $annee_exercice);
            });
        }

        return $sous_categories;
    }

    public function principes_de_gouvernance($annee_exercice = null)
    {
        return $this->sous_categories_de_gouvernance($annee_exercice)->where("categorieable_type", get_class(new PrincipeDeGouvernance));
    }

    public function programme()
    {
        return $this->belongsTo(Programme::class, 'programmeId');
    }

}
