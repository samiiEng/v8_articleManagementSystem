<?php

namespace App\Http\Controllers;

use App\Models\DefaultMessages;
use App\Http\Requests\StoreDefaultMessagesRequest;
use App\Http\Requests\UpdateDefaultMessagesRequest;

class DefaultMessagesController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        //
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \App\Http\Requests\StoreDefaultMessagesRequest  $request
     * @return \Illuminate\Http\Response
     */
    public function store(StoreDefaultMessagesRequest $request)
    {
        //
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\DefaultMessages  $defaultMessages
     * @return \Illuminate\Http\Response
     */
    public function show(DefaultMessages $defaultMessages)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\DefaultMessages  $defaultMessages
     * @return \Illuminate\Http\Response
     */
    public function edit(DefaultMessages $defaultMessages)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \App\Http\Requests\UpdateDefaultMessagesRequest  $request
     * @param  \App\Models\DefaultMessages  $defaultMessages
     * @return \Illuminate\Http\Response
     */
    public function update(UpdateDefaultMessagesRequest $request, DefaultMessages $defaultMessages)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\DefaultMessages  $defaultMessages
     * @return \Illuminate\Http\Response
     */
    public function destroy(DefaultMessages $defaultMessages)
    {
        //
    }
}
