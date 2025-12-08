<?php

namespace App\Models;

use Exception;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use SaiAshirwadInformatia\SecureIds\Models\Traits\HasSecureIds;

class Enquete extends Model
{
    protected $table = 'enquetes_de_collecte';
    public $timestamps = true;

    use HasSecureIds, HasFactory ;

    protected $dates = ['deleted_at'];

    protected $fillable = array('nom', 'objectif', 'description', 'debut', 'fin', 'statut', 'programmeId');

    protected $casts = ['debut'  => 'datetime', 'fin'  => 'datetime'];

    protected static function boot()
    {
        parent::boot();

        static::deleted(function ($enquete) {

            DB::beginTransaction();
            try {


                $enquete->update([
                    'nom' => time() . '::' . $enquete->nom
                ]);

                $enquete->reponses_collecter()->delete();

                DB::commit();
            } catch (\Throwable $th) {
                DB::rollBack();

                throw new Exception($th->getMessage(), 1);
            }
        });
    }

    public function reponses_collecter()
    {
        return $this->hasMany(ReponseCollecter::class, 'enqueteDeCollecteId');
    }

    public function programme()
    {
        return $this->belongsTo(Programme::class, 'programmeId');
    }

    public function notes_resultat()
    {
        return $this->hasMany(EnqueteResultatNote::class, 'enqueteDeCollecteId');
    }
}
