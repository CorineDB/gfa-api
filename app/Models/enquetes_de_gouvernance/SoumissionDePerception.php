<?php

namespace App\Models\enquetes_de_gouvernance;

use App\Models\Organisation;
use App\Models\Programme;
use App\Models\User;
use Exception;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use SaiAshirwadInformatia\SecureIds\Models\Traits\HasSecureIds;

class SoumissionDePerception extends Model
{
    protected $table = 'soumissions_de_perception';
    public $timestamps = true;

    use HasSecureIds, HasFactory ;

    protected $dates = ['deleted_at'];

    protected $fillable = array('commentaire', 'submitted_at', 'statut', 'sexe', 'age', 'categorieDeParticipant', 'identifier_of_participant', 'submittedBy', 'organisationId', 'formulaireDePerceptionId', 'evaluationId', 'programmeId');

    protected $casts = [
        "statut" => "boolean",
        "submitted_at" => "datetime"
    ];

    protected $appends = ['pourcentage_evolution', 'reponses_uniques'];
    protected $with = [];

    protected static function boot()
    {
        parent::boot();

        static::deleting(function ($soumission) {

            DB::beginTransaction();
            try {

                if (($soumission->statut)) {
                    // Prevent deletion by throwing an exception
                    throw new Exception("Impossible de supprimer cette soumission validee.");
                }

            } catch (\Throwable $th) {
                DB::rollBack();

                throw new Exception($th->getMessage(), 1);
            }
        });

        static::deleted(function ($soumission) {

            DB::beginTransaction();
            try {

                $soumission->reponses_de_la_collecte()->delete();

                DB::commit();
            } catch (\Throwable $th) {
                DB::rollBack();

                throw new Exception($th->getMessage(), 1);
            }
        });
    }

    public function evaluation_de_gouvernance()
    {
        return $this->belongsTo(EvaluationDeGouvernance::class, 'evaluationId');
    }

    public function formulaireDeGouvernance()
    {
        return $this->belongsTo(FormulaireDePerceptionDeGouvernance::class, 'formulaireDePerceptionId');
    }

    public function authoredBy()
    {
        return $this->belongsTo(User::class, 'submittedBy');
    }

    public function organisation()
    {
        return $this->belongsTo(Organisation::class, 'organisationId');
    }

    public function programme()
    {
        return $this->belongsTo(Programme::class, 'programmeId');
    }

    public function reponses_de_la_collecte()
    {
        return $this->hasMany(ReponseDeLaCollecteDePerception::class, 'soumissionId');
    }

    /**
     * Retourne les réponses uniques (sans doublons) pour cette soumission
     * Une question ne peut avoir qu'une seule réponse, on prend la plus récente en cas de doublon
     */
    public function getReponsesUniquesAttribute()
    {
        // Grouper les réponses par questionId
        $reponses_groupees = $this->reponses_de_la_collecte->groupBy('questionId');

        // Ne prendre que la dernière réponse par question (la plus récente)
        return $reponses_groupees->map(function ($reponses_par_question) {
            return $reponses_par_question->sortByDesc('created_at')->first();
        })->values();
    }

    public function getPourcentageEvolutionAttribute()
    {
        /* ========== ANCIEN CODE (COMMENTÉ - PERMETTAIT DES DOUBLONS ET POURCENTAGES > 100%) ==========
        $nombre_de_questions = $this->formulaireDeGouvernance->questions_de_gouvernance->count();

        $total_pourcentage_de_reponse = $this->reponses_de_la_collecte->sum(function ($reponse_de_la_collecte) {
            return $reponse_de_la_collecte->pourcentage_evolution;
        });

        $pourcentage_global = 0;

        // Eviter la division par zéro
        if ($nombre_de_questions != 0) {
            $pourcentage_global = $total_pourcentage_de_reponse / $nombre_de_questions;
        }

        return round($pourcentage_global, 2);
        ========== FIN ANCIEN CODE ========== */

        // ========== NOUVEAU CODE (CORRIGÉ - ÉLIMINE LES DOUBLONS) ==========
        $nombre_de_questions = $this->formulaireDeGouvernance->questions_de_gouvernance->count();

        // Utiliser les réponses uniques (sans doublons) via l'attribut reponses_uniques
        $reponses_uniques = $this->reponses_uniques; // NOUVEAU

        // Calculer le total à partir des réponses uniques uniquement
        $total_pourcentage_de_reponse = $reponses_uniques->sum(function ($reponse_de_la_collecte) {
            return $reponse_de_la_collecte->pourcentage_evolution;
        });

        $pourcentage_global = 0;

        // Eviter la division par zéro
        if ($nombre_de_questions != 0) {
            $pourcentage_global = $total_pourcentage_de_reponse / $nombre_de_questions;
        }

        // Limiter à 100% par sécurité (évite les valeurs aberrantes)
        return round(min(100, $pourcentage_global), 2);
    }
}
