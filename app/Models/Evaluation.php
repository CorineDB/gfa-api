<?php

namespace App\Models;

use Exception;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use SaiAshirwadInformatia\SecureIds\Models\Traits\HasSecureIds;

class Evaluation extends Model

{
    protected $table = 'evaluations';
    public $timestamps = true;

    use HasSecureIds, HasFactory ;

    protected $dates = ['deleted_at'];

    protected $fillable = array('code', 'start_at', 'submitted_at', 'enqueteId', 'commentaire', 'organisationId');

    protected $casts = ['start_at'  => 'datetime', 'submitted_at'  => 'datetime'];

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

    public function enquete()
    {
        return $this->belongsTo(Enquete::class, 'enqueteId');
    }

    public function organisation()
    {
        return $this->belongsTo(Organisation::class, 'organisationId');
    }

    public function responses()
    {
        return $this->hasMany(ReponseEvaluation::class, 'evaluationId');
    }

}