<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use SaiAshirwadInformatia\SecureIds\Models\Traits\HasSecureIds;

class Actionable extends Model
{
    use HasSecureIds;

    protected $table = 'actionables';

    public $timestamps = true;

    protected $dates = ['deleted_at'];

    protected $fillable = array('action_a_mener_id', 'actionable_id', 'actionable_type', 'programmeId');
}
