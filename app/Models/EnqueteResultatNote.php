<?php

namespace App\Models;

use Exception;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use SaiAshirwadInformatia\SecureIds\Models\Traits\HasSecureIds;

class EnqueteResultatNote extends Model
{
    protected $table = 'enquete_resultat_notes';
    public $timestamps = true;

    use HasSecureIds, HasFactory ;

    protected $dates = ['deleted_at'];

    protected $fillable = array('contenu', 'type', 'enqueteDeCollecteId', "organisationId", 'userId');
    
    /**
     * The attributes that should be appended to the model's array form.
     *
     * @var array
     */
    //protected $with = ['note'];

    protected static function boot()
    {
        parent::boot();

        static::deleted(function ($enquete) {

            DB::beginTransaction();
            try {

                DB::commit();
            } catch (\Throwable $th) {
                DB::rollBack();

                throw new Exception($th->getMessage(), 1);
            }
        });
    }

    public function enquete()
    {
        return $this->belongsTo(Enquete::class, 'enqueteDeCollecteId');
    }

    public function organisation()
    {
        return $this->belongsTo(Organisation::class, 'organisationId');
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'userId');
    }
}
