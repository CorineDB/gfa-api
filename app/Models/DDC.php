<?php

namespace App\Models;

use Exception;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use SaiAshirwadInformatia\SecureIds\Models\Traits\HasSecureIds;

class DDC extends Model
{

    use HasSecureIds, HasFactory;

    protected $table = 'ddc';

    protected $fillable = [];

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

        static::deleting(function($ddc) {

            DB::beginTransaction();
            try {

                $ddc->user()->delete();

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
}
