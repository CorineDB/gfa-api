<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Log;
use Illuminate\Database\Eloquent\SoftDeletes;

use SaiAshirwadInformatia\SecureIds\Models\Traits\HasSecureIds;

class SuiviIndicateur extends Model
{

    use HasFactory, HasSecureIds;

    protected $table = "suivi_indicateurs";

    public $timestamps = true;

    protected $dates = ['deleted_at'];

    /**
     * Transtypage des attributs de type json
     * @var array
     */
    protected $casts = [
        'valeurRealise' =>  'array',
        "estValider"    => "boolean"
    ];


    /* Les attributs qui sont assignés en masse */
    protected $fillable = [
        'trimestre',
        'estValider',
        'valeurRealise',
        'valeurCibleId', 'commentaire', 'dateSuivie',
        'sources_de_donnee',
        'programmeId', 'suivi_indicateurable_id', 'suivi_indicateurable_type'
    ];

    public function suivi_indicateurable()
    {
        return $this->morphTo();
    }

    public function valeurCible()
    {
        return $this->belongsTo(ValeurCibleIndicateur::class, 'valeurCibleId');
    }

    public function programme()
    {
        return $this->belongsTo(Programme::class, 'programmeId');
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
        else return null;
    }

    public function cumul()
    {
        /*$suivis = SuiviIndicateur::/*where('dateSuivie', '<=', $this->dateSuivie)->
                                   where('id', '!=', $this->id)->
                                   get();*/

        $suivis = SuiviIndicateur::/*where('dateSuivie', '<=', $this->dateSuivie)->*/
                                   where('id', '!=', $this->id)->
                                   get();

        $cumul = [];

        // Vérification de type
        if (!is_array($this->valeurRealise) && !is_object($this->valeurRealise)) {
            Log::error("valeurRealise n’est pas un tableau : " . print_r($this->valeurRealise, true));
            return [];
        }

        foreach($this->valeurRealise as $key => $valeur)
        {
            $total = $valeur;
            //return $total;
            foreach($suivis as $suivi)
            {
                if($suivi->indicateur()->id != $this->indicateur()->id) continue;

                // Vérifier l'existence de la clé
                if (is_array($suivi->valeurRealise) && array_key_exists($key, $suivi->valeurRealise)) {
                    $total += $suivi->valeurRealise[$key];
                }
                else {
                    $total += $suivi->valeurRealise;
                    Log::info("Clé $key absente dans le suivi ID {$suivi->id}");
                }

                //$total += $suivi->valeurRealise[$key];
            }

            $cumul[$key] = $total; // mieux que array_push ici

            //array_push($cumul, $total);
        }

        return $cumul;
    }

    public function valeursRealiser()
    {
        return $this->morphMany(IndicateurValeur::class, 'indicateur_valueable');
    }

}
