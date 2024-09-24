<?php

namespace App\Models;

use App\Traits\Helpers\IdTrait;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Password extends Model
{
    use HasFactory, IdTrait;

    protected $table = 'passwords';

    public $timestamps = true;

    protected $fillable = [
        'password',
        'userId'
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'id',
        'userId',
        'password'
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $cast = [
        'id' => 'string'
    ];
    
    protected static function boot() {
        parent::boot();
    
        static::creating(function($password) {

            $password->id = $password->index2();

        });
    }



    public function user()
    {
        return $this->belongsTo(User::class, 'userId');
    }
}
