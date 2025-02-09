<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TeamMember extends Model
{
    use HasFactory;

    protected $table = 'member_teams';

    public $timestamps = true;

    protected $dates = ['deleted_at'];

    protected $fillable = [
        'profilable_type', 
        'profilable_id', 
        'roleId', 
        'userId', 'programmeId'
    ];

    protected $with = [];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'roleId',
        'userId',
        'profilable_id',
        'profilable_type',
        'updated_at', 'deleted_at'
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        "created_at" => "datetime:Y-m-d",
        "updated_at" => "datetime:Y-m-d",
        "deleted_at" => "datetime:Y-m-d"
    ];

    protected static function boot() {
        parent::boot();
    }

    public function profilable()
    {
        return $this->morphTo('profilable');
    }

    public function member()
    {
        return $this->belongsTo(User::class, 'userId');
    }

    public function role()
    {
        return $this->belongsTo(Role::class, 'roleId');
    }

    public function programme()
    {
        return $this->belongsTo(Programme::class, 'programmeId');
    }
}
