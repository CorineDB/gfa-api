<?php

namespace App\Models;

use Exception;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use SaiAshirwadInformatia\SecureIds\Models\Traits\HasSecureIds;

class Survey extends Model
{
    protected $table = 'surveys';
    public $timestamps = true;

    use HasSecureIds, HasFactory ;

    protected $dates = ['deleted_at'];

    protected $default = ['statut' => -1];

    protected $fillable = array('intitule', 'description', 'debut', 'fin', 'prive', 'token', 'nbreParticipants', 'statut', 'surveyable_id', 'surveyable_type', 'surveyFormId', 'programmeId');

    protected $casts = ['statut'  => 'integer', 'nbreParticipants' => 'integer', 'debut'  => 'datetime', 'prive'  => 'boolean', 'fin'  => 'datetime'];

    protected static function boot()
    {
        parent::boot();
        
        static::deleted(function ($survey) {

            DB::beginTransaction();
            try {

                $survey->survey_reponses()->delete();

                DB::commit();
            } catch (\Throwable $th) {
                DB::rollBack();

                throw new Exception($th->getMessage(), 1);
            }
        });
    }

    public function surveyable()
    {
        return $this->morphTo();
    }

    public function programme()
    {
        return $this->belongsTo(Programme::class, 'programmeId');
    }

    public function survey_form()
    {
        return $this->belongsTo(SurveyForm::class, 'surveyFormId');
    }

    public function survey_reponses()
    {
        return $this->hasMany(SurveyReponse::class, 'surveyId');
    }

    public function survey_response()
    {
        return $this->hasOne(SurveyReponse::class, 'surveyId');
    }

    public function scopeWithSurveyResponseForParticipant($query, $idParticipant)
    {
        return $query->with(['survey_response' => function ($query) use ($idParticipant) {
            $this->applySurveyResponseFilter($query, $idParticipant);
        }]);
    }
    
    public function loadSurveyResponseForParticipant($idParticipant)
    {
        return $this->load(['survey_response' => function ($query) use ($idParticipant) {
            $this->applySurveyResponseFilter($query, $idParticipant);
        }]);
    }

    protected function applySurveyResponseFilter($query, $idParticipant)
    {
        $query->where('idParticipant', $idParticipant);
    }

}