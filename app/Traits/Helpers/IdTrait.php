<?php


namespace App\Traits\Helpers;
use Illuminate\Support\Str;

use Illuminate\Support\Facades\Hash;

trait IdTrait{

    public function hashID(int $length = 32){
        return bin2hex(random_bytes($length));
    }

    public function index()
    {
        return (string) Str::uuid()/*  . '-' . time() */;
    }

    public function index2()
    {
        return (string) Str::orderedUuid() /* . '-' . time() */;
    }

}