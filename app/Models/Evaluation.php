<?php

namespace App\Models;

use Exception;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use SaiAshirwadInformatia\SecureIds\Models\Traits\HasSecureIds;

class Evaluation extends Model

{
    protected $table = 'enquetes_de_gouvernance';
    public $timestamps = true;

    use HasSecureIds, HasFactory ;

    protected $dates = ['deleted_at'];

    protected $fillable = array('code', 'start_at', 'submitted_at', 'enqueteId', 'commentaire', 'organisationId');

    protected static function boot()
    {
        parent::boot();

        static::deleted(function ($enquete) {

            DB::beginTransaction();
            try {

                $enquete->evaluations()->delete();

                DB::commit();
            } catch (\Throwable $th) {
                DB::rollBack();

                throw new Exception($th->getMessage(), 1);
            }
        });
    }

    public function evaluations()
    {
        return $this->hasMany(Evaluation::class, 'enqueteId');
    }

    public function organisation()
    {
        return $this->belongsTo(Organisation::class, 'organisationId');
    }

}