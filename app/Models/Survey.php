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

    protected $fillable = array('intitule', 'description', 'debut', 'fin', 'nbreParticipants', 'statut', 'surveyFormId', 'programmeId');

    protected $casts = ['statut'  => 'integer', 'nbreParticipants' => 'integer', 'debut'  => 'datetime', 'fin'  => 'datetime'];

    protected static function boot()
    {
        parent::boot();

        static::deleting(function ($survey) {

            DB::beginTransaction();
            try {

                if (($survey->survey_reponses->count() > 0) || ($survey->statut > -1)) {
                    // Prevent deletion by throwing an exception
                    throw new Exception("Cannot delete because there are associated resource.");
                }
                
            } catch (\Throwable $th) {
                DB::rollBack();

                throw new Exception($th->getMessage(), 1);
            }
        });

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
}