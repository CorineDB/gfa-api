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

class Soumission extends Model
{
    protected $table = 'soumissions';
    public $timestamps = true;

    use HasSecureIds, HasFactory ;

    protected $dates = ['deleted_at'];

    protected $fillable = array('type', 'commentaire', 'submitted_at', 'statut', 'comite_members', 'sexe', 'age', 'categorieDeParticipant', 'identifier_of_participant', 'submittedBy', 'evaluationId', 'formulaireDeGouvernanceId', 'organisationId', 'programmeId');

    protected $casts = [
        "comite_members" => "json",
        "statut" => "boolean",
        "submitted_at" => "datetime"
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
        return $this->belongsTo(FormulaireDeGouvernance::class, 'formulaireDeGouvernanceId');
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
        return $this->hasMany(ReponseDeLaCollecte::class, 'soumissionId');
    }

    public function getPourcentageEvolutionAttribute()
    {
        /* $formulaireDeGouvernance = $this->formulaireDeGouvernance()->with(['questions_de_gouvernance.reponses' => function($query){
            $query->where('reponses_de_la_collecte.soumissionId',$this->id);
        }])->first();

        if (!$formulaireDeGouvernance || !$formulaireDeGouvernance->questions_de_gouvernance) {
            return 0; // Return 0 if the governance form or questions are missing
        }

        $allReponses = $formulaireDeGouvernance
            ->questions_de_gouvernance
            ->flatMap(function ($question) {
                return $question->reponses;
            });

        return $allReponses->isNotEmpty() ? $allReponses->avg('pourcentage_evolution') : 0; */

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
    }
}
