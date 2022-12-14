<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreUserRequest;
use App\Http\Requests\UpdateUserRequest;
use App\Models\User;
use App\Repositories\UserRepository;
use Illuminate\Http\Request;

class UserController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(UserRepository $userRepository, $isNewlyRegistered)
    {
        $results = structuredJson($userRepository->index($isNewlyRegistered));
        return response()->json($results[0], $results[1], $results[2], $results[3]);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //There is no view page in an api
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\Response
     */
    public function store(StoreUserRequest $request, UserRepository $userRepository)
    {
        $validated = $request->validated();
        $results = structuredJson($userRepository->create($validated));
        return response()->json($results[0], $results[1], $results[2], $results[3]);
    }

    /**
     * Display the specified resource.
     *
     * @param \App\Models\User $user
     * @return \Illuminate\Http\Response
     */
    public function show(User $user)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param \App\Models\User $user
     * @return \Illuminate\Http\Response
     */
    public function edit(User $user)
    {
        //There is no view page in an api
    }

    /**
     * Update the specified resource in storage.
     *
     * @param \Illuminate\Http\Request $request
     * @param \App\Models\User $user
     * @return \Illuminate\Http\Response
     */
    public function update(UpdateUserRequest $request, UserRepository $userRepository, User $user)
    {
        $validated = $request->validated();
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param \App\Models\User $user
     * @return \Illuminate\Http\Response
     */
    public function destroy(User $user)
    {
        //
    }

    public function activateUsers(UserRepository $userRepository, UpdateUserRequest $request)
    {
        $results = structuredJson($userRepository->activateUsers($request->validate()));
        return response()->json($results[0], $results[1], $results[2], $results[3]);
    }
}
