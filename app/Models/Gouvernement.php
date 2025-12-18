<?php

namespace App\Models;

use Exception;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\DB;
use SaiAshirwadInformatia\SecureIds\Models\Traits\HasSecureIds;

class Gouvernement extends Model
{

    use HasSecureIds, HasFactory ;

    protected $table = 'gouvernements';

    protected $fillable = [];

    public $timestamps = true;

    protected $with = ['user'];

    protected $hidden = ['updated_at', 'deleted_at'];

    protected $cast = [
        "created_at" => "datetime:Y-m-d",
        "updated_at" => "datetime:Y-m-d",
        "deleted_at" => "datetime:Y-m-d"
    ];

    protected static function boot() {
        parent::boot();

        static::deleting(function($gouvernement) {

            DB::beginTransaction();
            try {

                $gouvernement->user()->delete();

                $gouvernement->decaissements()->delete();

                $gouvernement->suiviFinanciers()->delete();

                DB::commit();
            } catch (\Throwable $th) {
                DB::rollBack();

                throw new Exception($th->getMessage(), 1);
            }

        });
    }

    public function user()
    {
        return $this->morphOne(User::class, 'profilable');
    }

    public function projets()
    {
        return $this->user()->programme->projets;
    }

    public function decaissements()
    {
        return $this->morphMany(Decaissement::class, 'decaissementable');
    }

    public function projetDecaissements($projetId)
    {
        return $this->decaissements()->where('projetId', $projetId)->get();
    }

    public function suiviFinanciers()
    {
        return $this->morphMany(SuiviFinancier::class, 'suivi_financierable');
    }

}
