<?php

use App\Models\Permission;
use App\Models\Role;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

/*$nom = Request::segment(3);
$tab = explode(".", $nom);
$nom = "";

for($i = 0; $i < count($tab)-1; $i++)
{
    $nom .= $tab[$i];
}

Route::get('/', function () {
    return view('welcome');
});


Route::get("/share-permissions", function(){
    $role = Role::find(4);

    $permissions = Permission::all();

    $role->permissions()->sync($permissions);
});

Route::get("/okay", function(){

    return Auth::user();

    $var = [
        1,
        ["nom" => "Corine"],
        ["nom" => "Carine"],
    ];

    foreach ($var as $value) {
        if(is_int($value))
            echo ("true");
        elseif(isset($value['nom']))
            echo ("object");
        else
            echo ("nothing");

    }


});

Route::group(['middleware' => ['auth:sanctum']], function () {

    Route::group(['prefix' => 'storage', 'as' => 'storage.'], function () {
        Route::get("{path}", function () {
            return null;
        })->where('path', '.+');
    });
});*/

Route::get("/", function(){
    return "Welcome";
});