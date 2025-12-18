<?php

namespace App\Models;

use Exception;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\DB;
use SaiAshirwadInformatia\SecureIds\Models\Traits\HasSecureIds;

class MaitriseOeuvre extends Model
{

    protected $table = 'maitrise_oeuvres';
    public $timestamps = true;

    use HasSecureIds ;

    protected $dates = ['deleted_at'];
    protected $fillable = array('nom', 'estimation', 'engagement', 'reference', 'bailleurId', 'programmeId');

    protected static function boot() {
        parent::boot();

        static::deleting(function($maitriseOeuvre) {

            DB::beginTransaction();
            try {

                $maitriseOeuvre->commentaires()->delete();
                $maitriseOeuvre->suiviFinancierMods()->delete();

                DB::commit();
            } catch (\Throwable $th) {
                DB::rollBack();

                throw new Exception($th->getMessage(), 1);
            }

        });
    }

    public function bailleur()
    {
        return $this->belongsTo(Bailleur::class, 'bailleurId');
    }

    public function commentaires()
    {
        return $this->morphMany(Commentaire::class, 'commentable');
    }

    public function entrepriseExecutants()
    {
        return $this->belongsToMany(EntrepriseExecutant::class, 'entreprise_executant_maitrise_oeuvres', 'maitriseOeuvreId', 'entrepriseExecutantId');
    }

    public function eActivites()
    {
        return $this->belongsToMany(EActivite::class, 'maitriseOeuvreId', 'eActiviteId');
    }

    public function suiviFinancierMods()
    {
        return $this->hasMany(SuiviFinancierMod::class, 'maitriseOeuvreId');
    }

    public function programme()
    {
        return $this->belongsTo(Programme::class, 'programmeId');
    }

}
