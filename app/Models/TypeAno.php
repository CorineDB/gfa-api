<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use SaiAshirwadInformatia\SecureIds\Models\Traits\HasSecureIds;

class TypeAno extends Model
{
    use HasSecureIds, HasFactory ;
    protected $fillable = array('nom','duree');

    public function anos()
    {
        return $this->hasMany(Ano::class, 'typeId');
    }
}
