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

class SoumissionFactuel extends Model
{
    protected $table = 'soumissions_factuel';
    public $timestamps = true;

    use HasSecureIds, HasFactory ;

    protected $dates = ['deleted_at'];

    protected $fillable = array('submitted_at', 'statut', 'comite_members', 'submittedBy', 'evaluationId', 'formulaireFactuelId', 'organisationId', 'programmeId');

    protected $casts = [
        "comite_members" => "json",
        "statut" => "boolean",
        "submitted_at" => "datetime",
	"created_at"	=> "datetime"
    ];

    protected $appends = ['pourcentage_evolution'];
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
        return $this->belongsTo(FormulaireFactuelDeGouvernance::class, 'formulaireFactuelId');
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
        return $this->hasMany(ReponseDeLaCollecteFactuel::class, 'soumissionId');
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

        // Grouper les réponses par questionId pour détecter et gérer les doublons
        $reponses_groupees = $this->reponses_de_la_collecte->groupBy('questionId');

        // Ne prendre que la dernière réponse par question (la plus récente) en cas de doublon
        $reponses_uniques = $reponses_groupees->map(function ($reponses_par_question) {
            // Prendre la dernière réponse (created_at le plus récent) en cas de doublon
            return $reponses_par_question->sortByDesc('created_at')->first();
        });

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
