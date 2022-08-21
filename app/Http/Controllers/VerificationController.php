<?php

namespace App\Http\Controllers;

use App\Repositories\UserRepository;
use Illuminate\Foundation\Auth\EmailVerificationRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use \Illuminate\Support\Str;

class VerificationController extends Controller
{

    public function clickedEmailVerificationLink(EmailVerificationRequest $request, UserRepository $repository, $id, $hash)
    {
        $request->fulfill();
        if (($repository->find($id))[0]->phone_number_verified_at)
            $results = structuredJson("Your account will be activated in two days");
        else
            $results = structuredJson("You still need to verify your phone number to have your account activated!");
        return response()->json($results[0], $results[1], $results[2], $results[3]);

    }

    /*public function phoneVerification()
    {

    }

    public function enteredVerificationCode(UserRepository $repository)
    {

        if (($repository->find($id))[0]->phone_number_verified_at)
            $results = structuredJson("Your account will be activated in two days");
        else
            $results = structuredJson("You still need to verify your phone number to have your account activated!")
        return response()->json($results[0], $results[1], $results[2], $results[3]);

    }*/
}
