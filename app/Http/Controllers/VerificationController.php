<?php

namespace App\Http\Controllers;

use App\Repositories\BaseRepository;
use App\Repositories\UserRepository;
use Carbon\Carbon;
use Illuminate\Foundation\Auth\EmailVerificationRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Facades\Validator;
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

    public function requestChangeEmail(Request $request, BaseRepository $baseRepository)
    {
        $validated = Validator::make($request->input('email'), ['email' => 'required|email']);
        $email = $validated['email'];
        $ifExist = $baseRepository->find("users", ['WHERE email = ?', $email]);
        if ($ifExist) {
            $url = URL::temporarySignedRoute('verifyChangedEmail', now()->addHour(), ["id" => auth()->id(), "newEmail" => $email]);
            Mail::send('email.verificationEmail', ['url' => $url, 'type' => 'emailVerification'], function ($message) use ($email) {
                $message->to($email);
                $message->subject('Password reset link');
            });
        } else {
            $results = structuredJson("This email does not exist in the database.");
            return response()->json($results[0],$results[1],$results[2],$results[3]);
        }
    }

    public function changeEmail(BaseRepository $baseRepository, $id, $newEmail)
    {
        $now = Carbon::now();
        $baseRepository->update("users", ["email = ? and email_verified_at = ? WHERE user_id = ?", "$newEmail, $now, $id"]);
        $results = structuredJson("Your email address is successfully changed.");
        return response()->json($results[0],$results[1],$results[2],$results[3]);
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
