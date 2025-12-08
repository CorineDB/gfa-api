<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\DB;
use SaiAshirwadInformatia\SecureIds\Models\Traits\HasSecureIds;
use Exception;

class IndicateurValueKey extends Model
{
    protected $table = 'indicateur_value_keys';
    public $timestamps = true;

    use HasSecureIds, SoftDeletes, HasFactory;

    protected $dates = ['deleted_at'];

    protected $fillable = array('libelle', 'key', 'type', 'description', 'uniteeMesureId', 'programmeId');

    protected static function boot()
    {
        parent::boot();

        static::saving(function ($indicateur_value_key) {
            $indicateur_value_key->type = $indicateur_value_key->uniteeMesure->nom;
        });

        static::deleting(function ($indicateur_value_key) {
            DB::beginTransaction();
            try {
                $indicateur_value_key->update([
                    'libelle' => time() . '::' . $indicateur_value_key->libelle,
                    'key' => time() . '::' . $indicateur_value_key->key
                ]);

                $indicateur_value_key->indicateurs()->detach();

                DB::commit();
            } catch (\Throwable $th) {
                DB::rollBack();

                throw new \Exception($th->getMessage(), 1);
            }
        });
    }

    public function indicateurs()
    {
        return $this->belongsToMany(Indicateur::class, 'indicateur_value_keys_mapping', 'indicateurValueKeyId', 'indicateurId')->withPivot(['id', 'uniteeMesureId', 'type'])->wherePivotNull('deleted_at');
    }

    public function uniteeMesure()
    {
        return $this->belongsTo(Unitee::class, 'uniteeMesureId');
    }

    public function programme()
    {
        return $this->belongsTo(Programme::class, 'programmeId');
    }
}
