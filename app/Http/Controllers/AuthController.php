<?php

namespace App\Http\Controllers;

use App\Repositories\BaseRepository;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Str;
use \Illuminate\Support\Facades\Validator;


class AuthController extends Controller
{
    public function login(Request $request, BaseRepository $baseRepository)
    {
        $validated = Validator::make($request->all(), ["username" => "required|string", "password" => "required|string"]);

        if ($validated->fails()){
            $results = structuredJson("", "error", "Your credentials are invalidated!", 401);
            return response()->json($results[0], $results[1], $results[2], $results[3]);
        }

        $token = auth()->attempt($validated->validated());
        if (!$token) {
            $results = structuredJson("", "error", "Invalid Email or Password", 401);
            return response()->json($results[0], $results[1], $results[2], $results[3]);
        }

        $results = structuredJson($token);
        return response()->json($results[0], $results[1], $results[2], $results[3]);
    }

    public function logout(Request $request, BaseRepository $baseRepository)
    {
        auth()->logout();
        $results = structuredJson("You have successfully logged out!");
        return response()->json($results[0], $results[1], $results[2], $results[3]);

    }

    public function requestResetPassword(BaseRepository $baseRepository, Request $request)
    {
        $validated = Validator::make($request->input('email'), ["email" => "required|email"]);
        $email = $validated['email'];
        $ifExists = $baseRepository->find("users", ["WHERE email = ?", "$email"]);
        if ($ifExists) {
            $url = URL::temporarySignedRoute('resetPasswordForm', now()->addHour(), [auth()->id()]);
            Mail::send("email.verificationEmail", ['type' => 'resetPassword', 'url' => $url]);
            $results = structuredJson("The reset Password link is successfully sent to your mail box!");
            return response()->json($results[0], $results[1], $results[2], $results[3]);
        } else {
            $results = structuredJson("This email address doesn't exist in the database");
            return response()->json($results[0], $results[1], $results[2], $results[3]);
        }

    }

    public function resetPassword(BaseRepository $baseRepository, Request $request, $id)
    {
        $validated = Validator::make($request->all(), ['password' => 'required|string', 'newPassword' => "required|string"]);
        $password = Hash::make($validated['password']);
        $newPassword = Hash::make($validated['newPassword']);
        $ifExists = $baseRepository->find("users", ["WHERE user_id = ? and password = ?", "$id, $password"]);
        if ($ifExists) {
            $baseRepository->update("users", ["password = ? WHERE user_id = ?", [$newPassword, $id]]);
            $results = structuredJson("Your password is successfully changed!");
            return response()->json($results[0], $results[1], $results[2], $results[3]);
        } else {
            $results = structuredJson("The user's current password doesn't match the inserted password");
            return response()->json($results[0], $results[1], $results[2], $results[3]);
        }

    }


}
