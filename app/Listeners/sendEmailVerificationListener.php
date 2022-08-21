<?php

namespace App\Listeners;

use App\Repositories\BaseRepository;
use Carbon\Carbon;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Str;

class sendEmailVerificationListener
{
    public $baseRepository;

    /**
     * Create the event listener.
     *
     * @return void
     */
    public function __construct(BaseRepository $baseRepository)
    {
        $this->baseRepository = $baseRepository;
    }

    /**
     * Handle the event.
     *
     * @param object $event
     * @return void
     */
    public function handle($event)
    {
        $email = $event->email;
        $ifExist = $this->baseRepository->find("users", ['WHERE email = ?', $email]);

        if ($ifExist) {
            if ($event->type == "resetPassword") {
                $token = Str::random(64);
                $now = Carbon::now();
                $this->baseRepository->store("password_resets", ['token, email, created_at', "$token, $email, $now"]);
                Mail::send('email.verificationEmail', ['type' => 'resetPassword', 'token' => $token], function ($message) use ($email) {
                    $message->to($email);
                    $message->subject('Password reset link');
                   });
            }else{
                Mail::send('email.verificationEmail', ['type' => 'emailVerification'], function ($message) use ($email) {
                    $message->to($email);
                    $message->subject('Password reset link');
                });

            }


        }
    }
}
