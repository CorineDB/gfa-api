<?php

namespace App\Models;

use Exception;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use SaiAshirwadInformatia\SecureIds\Models\Traits\HasSecureIds;

class ReponseCollecter extends Model
{
    protected $table = 'reponses_collecter';
    public $timestamps = true;

    use HasSecureIds, HasFactory ;

    protected $dates = ['deleted_at'];

    protected $fillable = array('source', 'enqueteDeCollecteId', 'userId', 'indicateurDeGouvernanceId', 'optionDeReponseId', 'commentaire');

    /**
     * The attributes that should be appended to the model's array form.
     *
     * @var array
     */
    protected $appends = [];
    
    /**
     * The attributes that should be appended to the model's array form.
     *
     * @var array
     */
    //protected $with = ['note'];

    protected static function boot()
    {
        parent::boot();

        static::deleted(function ($enquete) {

            DB::beginTransaction();
            try {

                DB::commit();
            } catch (\Throwable $th) {
                DB::rollBack();

                throw new Exception($th->getMessage(), 1);
            }
        });
    }

    public function enquete()
    {
        return $this->belongsTo(Enquete::class, 'enqueteDeCollecteId');
    }

    public function organisation()
    {
        return $this->belongsTo(User::class, 'userId');
    }

    public function indicateurDeGouvernance()
    {
        return $this->belongsTo(IndicateurDeGouvernance::class, 'indicateurDeGouvernanceId');
    }

    public function optionDeReponse()
    {
        return $this->belongsTo(OptionDeReponse::class, 'optionDeReponseId');
    }

    /*public function getReponseValueAttribute()
    {
        $value = 0;

        if($this->optionDeReponse){
            
            switch ($this->optionDeReponse->slug) {
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

    
    /**
     * Scope a query to include the response value based on the optionDeReponse.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    /*public function scopeWithReponseValue($query)
    {
        return $query->select('*')
            ->addSelect([
                'reponse_value' => $query->raw("
                    CASE 
                        WHEN optionDeReponse.slug = 'oui' THEN 1
                        WHEN optionDeReponse.slug = 'non' THEN 0
                        WHEN optionDeReponse.slug = 'ne-peux-repondre' THEN 1
                        WHEN optionDeReponse.slug = 'pas-du-tout' THEN 2
                        WHEN optionDeReponse.slug = 'faiblement' THEN 3
                        WHEN optionDeReponse.slug = 'moyennement' THEN 4
                        WHEN optionDeReponse.slug = 'dans-une-grande-mesure' THEN 5
                        WHEN optionDeReponse.slug = 'totalement' THEN 6
                        ELSE 0
                    END
                ")
            ]);
    }*/
}
