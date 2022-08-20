<?php

namespace App\Repositories;

use App\Http\Requests\StoreUserRequest;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class UserRepository
{
    public function create($validated)
    {

        DB::insert("INSERT INTO users (first_name, last_name, username, password, nationalCode, phone_number
                    email, avatar_image_path, department_ref_id, extra, is_normal, is_active,
                    created_at) VALUES ()", [$validated['first_name'], $validated['last_name'], $validated['username'],
                Hash::make($validated['password']), $validated['nationalCode'], $validated['phoneNumber'], $validated['email'], $validated['avatar_image_path'],
            $validated['department_ref_id'], $validated['extra'], $validated['is_normal'], 0, Carbon::now()]);

        $user = User::findOrFail(DB::getPdo()->lastInsertId());
        event(new Registered($user));


    }

    public function update($validated)
    {

    }

    public function find($id)
    {
       return DB::select("SELECT * FROM users WHERE user_id = ?", [$id]);
    }

    public function softDelete()
    {

    }

    public function forceDelete()
    {

    }

    public function restoreDeleted()
    {

    }

}
