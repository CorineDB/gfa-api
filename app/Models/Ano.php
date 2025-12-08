<?php

namespace App\Models;

use Exception;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\DB;
use SaiAshirwadInformatia\SecureIds\Models\Traits\HasSecureIds;

class Ano extends Model
{
    use HasFactory, HasSecureIds ;

    protected $fillable = [
        'dossier',
        'auteurId',
        'bailleurId',
        'destinataire',
        'dateDeSoumission',
        'dateReponse',
        'typeId',
        'statut'
    ];

    protected static function boot() {
        parent::boot();

        static::deleted(function($ano) {
            DB::beginTransaction();
            try {

                $ano->reponsesAno()->delete();
                $ano->durees()->delete();
                $ano->statuts()->delete();

                DB::commit();
            } catch (\Throwable $th) {
                DB::rollBack();

                throw new Exception($th->getMessage(), 1);

            }
        });
    }

    /* L'auteur d'un ano */
    public function auteur()
    {
        return $this->belongsTo(User::class, 'auteurId');
    }

    public function fichiers()
    {
        return $this->morphMany(Fichier::class, 'fichiertable');
    }

    public function typeAno()
    {
        return $this->belongsTo(TypeAno::class, 'typeId');
    }

    /* Le bailleur d'un ano */
    public function bailleur()
    {
        return $this->belongsTo(Bailleur::class, 'bailleurId');
    }

    /* Les statuts d'un Ano*/
    public function statuts()
    {
        return $this->morphMany(Statut::class, 'statuttable');
    }

    public function getStatusAttribute()
    {
        $statut = $this->statuts->last();
        return $statut ? $statut['etat'] : null;
    }

    public function durees()
    {
        return $this->morphMany(Duree::class, 'dureeable');
    }

    public function reponsesAno()
    {
        return $this->hasMany(ReponseAno::class, 'anoId');
    }

    public function commentaires()
    {
        return $this->morphMany(Commentaire::class, 'commentable');
    }

    public function programme()
    {
        return $this->auteur->programme;
    }
}
