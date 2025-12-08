<?php

namespace App\Models;

use Exception;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\DB;
use SaiAshirwadInformatia\SecureIds\Models\Traits\HasSecureIds;

class CheckList extends Model
{

    protected $table = 'check_lists';
    public $timestamps = true;

    use HasSecureIds, HasFactory;

    protected $dates = ['deleted_at'];

    protected $fillable =['nom', 'code', 'inputType', 'uniteeId'];
    
    protected static function boot() {
        parent::boot();

        static::deleted(function($checkList) {
            DB::beginTransaction();
            try {
                
                $checkList->commentaires()->delete();
                $checkList->eSuivis()->delete();

                DB::commit();
            } catch (\Throwable $th) {
                DB::rollBack();

                throw new Exception($th->getMessage(), 1);
                
            }
        });
    }

    public function profilable()
    {
        return $this->morphTo();
    }

    public function eActivite()
    {
        return $this->belongsTo(EActivite::class, 'eActiviteId');
    }

    public function commentaires()
    {
        return $this->morphMany(Commentaire::class, 'commentable');
    }

    public function unitee()
    {
        return $this->belongsTo(Unitee::class, 'uniteeId');
    }

    public function eSuivis()
    {
        return $this->hasMany(ESuivi::class, 'checkListId');
    }

    public function formulaires()
    {
        $this->belongsToMany(Formulaire::class, 'checklist_formulaires', 'checklistId', 'formulaireId');
    }

}
