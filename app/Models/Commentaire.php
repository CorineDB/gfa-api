<?php

namespace App\Models;

use Exception;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\DB;
use SaiAshirwadInformatia\SecureIds\Models\Traits\HasSecureIds;

class Commentaire extends Model
{

    protected $table = 'commentaires';
    public $timestamps = true;

    use HasSecureIds ;

    protected $dates = ['deleted_at'];
    protected $fillable = array('contenu', 'commentable_type', 'commentable_id', 'commentaireId', 'auteurId');

    protected static function boot() {
        parent::boot();

        static::deleted(function($commentaire) {
            DB::beginTransaction();
            try {

                $commentaire->commentaires()->delete();

                DB::commit();
            } catch (\Throwable $th) {
                DB::rollBack();

                throw new Exception($th->getMessage(), 1);

            }
        });
    }

    public function commentable()
    {
        return $this->morphTo();
    }

    public function commentaires()
    {
        return $this->hasMany(Commentaire::class, 'commentaireId');
    }

    public function auteur()
    {
        return $this->belongsTo(User::class, 'auteurId');
    }
}
