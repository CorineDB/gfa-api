<?php

namespace App\Models\enquetes_de_gouvernance;

use App\Models\Programme;
use Exception;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use SaiAshirwadInformatia\SecureIds\Models\Traits\HasSecureIds;

class SurveyReponse extends Model
{
    protected $table = 'survey_reponses';
    public $timestamps = true;

    use HasSecureIds, HasFactory ;

    protected $dates = ['deleted_at'];

    protected $fillable = array('response_data', 'submitted_at', 'idParticipant', 'statut', 'surveyId', 'programmeId');

    protected $casts = ['statut'  => 'boolean', 'response_data'  => 'array', 'submitted_at'  => 'datetime'];

    protected static function boot()
    {
        parent::boot();

        static::deleting(function ($survey) {

            DB::beginTransaction();
            try {

            } catch (\Throwable $th) {
                DB::rollBack();

                throw new Exception($th->getMessage(), 1);
            }
        });

        static::deleted(function ($survey) {

            DB::beginTransaction();
            try {

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

    public function survey()
    {
        return $this->belongsTo(Survey::class, 'surveyId');
    }

}
