<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

use SaiAshirwadInformatia\SecureIds\Models\Traits\HasSecureIds;

class Duree extends Model
{
    use HasSecureIds, HasFactory ;

    protected $table = 'durees';

    protected $fillable = array('debut', 'fin', 'dureeable_type', 'dureeable_id');

    public function dureeable()
    {
        return $this->morphTo();
    }
}
