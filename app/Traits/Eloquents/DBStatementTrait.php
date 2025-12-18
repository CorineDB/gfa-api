<?php
namespace App\Traits\Eloquents;

use Illuminate\Support\Facades\DB;

trait DBStatementTrait
{
    public function changeState($value)
    {
        DB::statement("SET FOREIGN_KEY_CHECKS={$value};");
    }
}