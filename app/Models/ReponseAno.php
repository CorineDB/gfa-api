<?php

namespace App\Models;

use Exception;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\DB;
use SaiAshirwadInformatia\SecureIds\Models\Traits\HasSecureIds;

class ReponseAno extends Model
{
    use HasFactory, HasSecureIds ;

    protected $tables = "reponse_anos";

    protected $fillable = ['commentaire', 'anoId', 'auteurId', 'reponseId'];


    protected static function boot() {
        parent::boot();

        static::deleted(function($ano) {
            DB::beginTransaction();
            try {

                $ano->commentaires()->delete();
                $ano->documents()->delete();

                DB::commit();
            } catch (\Throwable $th) {
                DB::rollBack();

                throw new Exception($th->getMessage(), 1);

            }
        });
    }

    public function ano()
    {
        return $this->belongsTo(Ano::class, 'anoId');
    }

    public function commentaires()
    {
        return $this->morphMany(Commentaire::class, 'commentable');
    }

    public function documents()
    {
        return $this->morphMany(Fichier::class, 'fichiertable');
    }

    public function auteur()
    {
        return $this->belongsTo(User::class, 'auteurId');
    }

    public function reponses()
    {
        return $this->hasMany(ReponseAno::class, 'reponseId');
    }

    public function reponse()
    {
        return $this->belongsTo(ReponseAno::class, 'reponseId');
    }

}
