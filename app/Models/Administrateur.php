<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

use SaiAshirwadInformatia\SecureIds\Models\Traits\HasSecureIds;

class Administrateur extends Model
{
    use HasSecureIds ;

    protected $table = 'users';

    protected static function boot() {
        parent::boot();
    }
}
