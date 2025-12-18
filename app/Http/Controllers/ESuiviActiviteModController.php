<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Requests\eSuiviActiviteMod\StoreRequest;
use App\Http\Requests\eSuiviActiviteMod\UpdateRequest;
use Core\Services\Interfaces\ESuiviActiviteModServiceInterface;

class ESuiviActiviteModController extends Controller
{

    /**
     * @var service
     */
    private $eSuiviActiviteMod;

    /**
     * Instantiate a new ESuiviActiviteModController instance.
     * @param ESuiviActiviteModServiceInterface $eSuiviActiviteServiceInterface
     */
    public function __construct(ESuiviActiviteModServiceInterface $eSuiviActiviteServiceInterface)
    {

        $this->eSuiviActiviteMod = $eSuiviActiviteServiceInterface;

    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        return $this->eSuiviActiviteMod->all();
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(StoreRequest $request)
    {
        return $this->eSuiviActiviteMod->create($request->all());
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        return $this->eSuiviActiviteMod->findById($id);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(UpdateRequest $request, $id)
    {
        return $this->eSuiviActiviteMod->update($id, $request->all());
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        return $this->eSuiviActiviteMod->deleteById($id);
    }
}
