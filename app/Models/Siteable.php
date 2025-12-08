<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use SaiAshirwadInformatia\SecureIds\Models\Traits\HasSecureIds;

class Siteable extends Model
{
    use HasSecureIds, SoftDeletes;

    protected $table = 'siteables';

    public $timestamps = true;

    protected $dates = ['deleted_at'];

    protected $fillable = array('site_id', 'siteable_id', 'siteable_type', 'programmeId');
}
