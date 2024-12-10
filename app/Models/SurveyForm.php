<?php

namespace App\Models;

use Exception;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use SaiAshirwadInformatia\SecureIds\Models\Traits\HasSecureIds;

class SurveyForm extends Model
{
    protected $table = 'survey_forms';
    public $timestamps = true;

    use HasSecureIds, HasFactory ;

    protected $dates = ['deleted_at'];

    protected $fillable = array('intitule', 'description', 'form_data', 'created_by_type', 'created_by_id', 'programmeId');

    protected $casts = ['form_data'  => 'array'];

    protected static function boot()
    {
        parent::boot();

        static::deleting(function ($survey_form) {

            DB::beginTransaction();
            try {

                if (($survey_form->surveys->count() > 0) || ($survey_form->statut > -1)) {
                    // Prevent deletion by throwing an exception
                    throw new Exception("Cannot delete because there are associated resource.");
                }
                
            } catch (\Throwable $th) {
                DB::rollBack();

                throw new Exception($th->getMessage(), 1);
            }
        });

        static::deleted(function ($survey_form) {

            DB::beginTransaction();
            try {

                $survey_form->surveys()->delete();

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

    public function created_by()
    {
        return $this->morphTo();
    }

    public function surveys()
    {
        return $this->hasMany(Survey::class, 'surveyId');
    }
}