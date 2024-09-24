<?php

namespace App\Models;

use Exception;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use SaiAshirwadInformatia\SecureIds\Models\Traits\HasSecureIds;

class Organisation extends Model
{

    use HasSecureIds, HasFactory;

    protected $table = 'organisations';

    protected $fillable = ['sigle'];

    protected $dates = ['deleted_at'];

    protected $with = ['user'];

    protected $cast = [
        "created_at" => "datetime:Y-m-d",
        'updated_at' => 'datetime:Y-m-d H:i:s',
        'deleted_at' => 'datetime:Y-m-d H:i:s'
    ];

    protected $hidden = ['updated_at', 'deleted_at'];

    protected static function boot() {
        parent::boot();

        static::deleting(function($bailleur) {

            DB::beginTransaction();
            try {

                $bailleur->update([
                    'sigle' => time() . '::' . $bailleur->sigle
                ]);

                $bailleur->user()->delete();
                
                $bailleur->projets()->delete();

                $bailleur->suivis()->delete();

                $bailleur->decaissements()->delete();

                $bailleur->suiviFinanciers()->delete();

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

    public function projets($programmeId = null)
    {

        if(!$programmeId)
        {
            return $this->hasOne(Projet::class, 'bailleurId');
        }

        return $this->hasMany(Projet::class, 'bailleurId')->where('programmeId', $programmeId)->first();
    }

    public function programmes()
    {
        return $this->hasMany(Projet::class, 'bailleurId');
    }

    public function suiviFinanciers()
    {
        return $this->morphMany(SuiviFinancier::class, 'suivi_financierable');
    }

    /*public function sinistres()
    {
        return $this->hasMany(Sinistre::class, 'bailleurId');
    }*/

    public function tepGlobal()
    {
        return 0;
    }
}
