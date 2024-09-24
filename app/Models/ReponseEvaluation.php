<?php

namespace App\Models;

use Exception;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use SaiAshirwadInformatia\SecureIds\Models\Traits\HasSecureIds;

class ReponseEvaluation extends Model
{
    protected $table = 'reponses_evaluation';
    public $timestamps = true;

    use HasSecureIds, HasFactory ;

    protected $dates = ['deleted_at'];

    protected $fillable = array('response_data', 'source', 'evaluationId', 'indicateurDeGouvernanceId', 'commentaire');

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

    public function evaluation()
    {
        return $this->belongsTo(Evaluation::class, 'evaluationId');
    }

    public function indicateurDeGouvernance()
    {
        return $this->belongsTo(IndicateurDeGouvernance::class, 'indicateurDeGouvernanceId');
    }

}
