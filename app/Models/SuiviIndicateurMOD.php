<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

use SaiAshirwadInformatia\SecureIds\Models\Traits\HasSecureIds;

class SuiviIndicateurMOD extends Model
{

    use HasSecureIds, HasFactory ;

    protected $table = 'suivi_indicateur_mods';

    public $timestamps = true;

    protected $dates = ['deleted_at'];

    /**
     * Transtypage des attributs de type json
     * @var array
     */
    protected $casts = [
        'valeurRealise' =>  'array',
    ];


    /* Les attributs qui sont assignés en masse */
    protected $fillable = [
        'trimestre',
        'valeurRealise',
        'valeurCibleId', 'commentaire', 'dateSuivie'
    ];

    public function valeurCible()
    {
        return $this->belongsTo(ValeurCibleIndicateur::class, 'valeurCibleId');
    }

    public function commentaires()
    {
        return $this->morphMany(Commentaire::class, 'commentable');
    }

    public function indicateur()
    {
        $valeurCible = $this->valeurCible;

        if($valeurCible->cibleable_type == get_class(new Indicateur()))
        {
            return Indicateur::find($valeurCible->cibleable_id);
        }

        else if($valeurCible->cibleable_type == get_class(new IndicateurMod()))
        {
            return IndicateurMod::find($valeurCible->cibleable_id);
        }
    }

    public function cumul()
    {
        $suivis = SuiviIndicateur::/*where('dateSuivie', '<=', $this->dateSuivie)->*/
                                   where('id', '!=', $this->id)->
                                   get();
        $cumul = [];

        foreach($this->valeurRealise as $key => $valeur)
        {
            $total = $valeur;
            //return $total;
            foreach($suivis as $suivi)
            {
                if($suivi->indicateur()->id != $this->indicateur()->id) continue;

                $total += $suivi->valeurRealise[$key];
            }

            array_push($cumul, $total);
        }

        return $cumul;
    }

}
