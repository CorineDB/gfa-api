<?php

namespace App\Models\enquetes_de_gouvernance;

use App\Models\Programme;
use Exception;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use SaiAshirwadInformatia\SecureIds\Models\Traits\HasSecureIds;

class TypeDeGouvernanceFactuel extends Model
{
    protected $table = 'types_de_gouvernance_factuel';
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
        return $this->morphMany(CategorieFactuelDeGouvernance::class, 'categorieable');
    }

    public function sous_categories_de_gouvernance($annee_exercice = null)
    {
        return $this->hasManyThrough(
            CategorieFactuelDeGouvernance::class,// Final Model
            CategorieFactuelDeGouvernance::class,// Intermediate Model
            'categorieable_id',
            'categorieFactuelDeGouvernanceId',
            'id',
            'id'
        );
    }

    public function principes_de_gouvernance($annee_exercice = null)
    {
        return $this->sous_categories_de_gouvernance($annee_exercice)->where("categorieable_type", get_class(new PrincipeDeGouvernanceFactuel()));
    }

    public function programme()
    {
        return $this->belongsTo(Programme::class, 'programmeId');
    }

}
