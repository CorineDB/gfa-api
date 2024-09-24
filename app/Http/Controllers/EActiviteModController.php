<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Requests\eActiviteMod\StoreRequest;
use App\Http\Requests\eActiviteMod\UpdateRequest;
use Core\Services\Interfaces\EActiviteModServiceInterface;


class EActiviteModController extends Controller
{
    /**
     * @var service
     */
    private $eActiviteMod;

    /**
     * Instantiate a new EActiviteModController instance.
     * @param EActiviteModServiceInterface $eActiviteServiceInterface
     */
    public function __construct(EActiviteModServiceInterface $eActiviteServiceInterface)
    {

        $this->eActiviteMod = $eActiviteServiceInterface;

    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        return $this->eActiviteMod->all();
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(StoreRequest $request)
    {
        return $this->eActiviteMod->create($request->all());
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        return $this->eActiviteMod->findById($id);
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
        return $this->eActiviteMod->update($id, $request->all());
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        return $this->eActiviteMod->deleteById($id);
    }
}
