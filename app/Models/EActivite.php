<?php

namespace App\Models;

use Exception;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use SaiAshirwadInformatia\SecureIds\Models\Traits\HasSecureIds;

class EActivite extends Model
{

    protected $table = 'e_activites';
    public $timestamps = true;

    use HasSecureIds, HasFactory;

    protected $dates = ['deleted_at'];

    protected $fillable = array('nom', 'code', 'programmeId');

    protected static function boot() {
        parent::boot();

        static::deleted(function($commentaire) {

            DB::beginTransaction();
            try {

                $commentaire->formulaires()->delete();
                $commentaire->statuts()->delete();
                $commentaire->durees()->delete();
                $commentaire->eSuivis()->delete();

                DB::commit();
            } catch (\Throwable $th) {
                DB::rollBack();

                throw new Exception($th->getMessage(), 1);

            }
        });
    }

    public function formulaires()
    {
        return $this->hasMany(ChecklistFormulaire::class, 'activiteId');
    }

    public function statuts()
    {
        return $this->hasMany(EActiviteStatut::class, 'activiteId');
    }

    public function statutByEntreprise($entrepriseId)
    {
        $statut = $this->statuts->where('entrepriseId', $entrepriseId)->last;
        return $statut['etat'];
    }

    public function programme()
    {
        return $this->belongsTo(Programme::class, 'programmeId');
    }

    public function entrepriseExecutants()
    {
        return $this->belongsToMany(EntrepriseExecutant::class, 'entreprise_executant_e_activites', 'eActiviteId', 'entrepriseExecutantId');
    }

    public function durees()
    {
        return $this->morphMany(Duree::class, 'dureeable');
    }

    public function getDureeAttribute()
    {
        $duree = $this->durees->last();
        return $duree;
    }

    public function eSuivis()
    {
        return $this->hasMany(ESuivi::class, 'activiteId');
    }

}
