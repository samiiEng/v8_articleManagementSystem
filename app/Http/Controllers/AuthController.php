<?php

namespace App\Http\Controllers;

use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;

class AuthController extends Controller
{
    public function login()
    {

    }

    public function logout()
    {

    }

    public function requestResetPassword(){

    }

    public function resetPassword()
    {
        if ($ifExist) {
            $token = Str::random(64);
            $now = Carbon::now();
            $baseRepository->store("password_resets", ['token, email, created_at', "$token, $email, $now"]);
            Mail::send('email.verificationEmail', ['type' => 'resetPassword', 'token' => $token], function ($message) use ($email) {
                $message->to($email);
                $message->subject('Password reset link');
            });
        } else{
            return "This email address doesn't exist in the database";
        }

    }

    public function changeEmail(){

    }
}
