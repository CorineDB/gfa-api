<?php

namespace App\Models;

use Exception;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\DB;
use SaiAshirwadInformatia\SecureIds\Models\Traits\HasSecureIds;

class ESuivi extends Model
{

    protected $table = 'e_suivies';
    public $timestamps = true;

    use HasSecureIds, HasFactory ;

    protected $dates = ['deleted_at'];

    protected $fillable = array(
        'valeur',
        'commentaire',
        'date',
        'userId',
        'siteId',
        'checkListId',
        'activiteId',
        'formulaireId',
        'entrepriseExecutantId',
        'auteurable_id',
        'auteurable_type',
        'justification'
    );


    protected static function boot() {
        parent::boot();

        static::deleted(function($eSuivi) {

            DB::beginTransaction();
            try {

                $eSuivi->fichiers()->delete();
                $eSuivi->commentaires()->delete();

                DB::commit();
            } catch (\Throwable $th) {
                DB::rollBack();

                throw new Exception($th->getMessage(), 1);

            }
        });
    }

    public function entrepriseExecutant()
    {
        return $this->belongsTo(EntrepriseExecutant::class, 'entrepriseExecutantId');
    }

    public function fichiers()
    {
        return $this->morphMany(Fichier::class, 'fichiertable');
    }

    public function commentaires()
    {
        return $this->morphMany(Commentaire::class, 'commentable');
    }

    public function auteurable()
    {
        return $this->morphTo();
    }

    public function responsableEnquete()
    {
        return $this->belongsTo(User::class, 'userId');
    }

    public function checkList()
    {
        return $this->belongsTo(CheckList::class, 'checkListId');
    }

    public function activite()
    {
        return $this->belongsTo(EActivite::class, 'activiteId');
    }

    public function site()
    {
        return $this->belongsTo(Site::class, 'siteId');
    }

}
