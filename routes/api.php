<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use \App\Http\Controllers\ArticleController;
use \App\Http\Controllers\Filter\FilterController;
use \App\Http\Controllers\UserController;
use \App\Http\Controllers\VerificationController;
use \App\Http\Controllers\AuthController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});


//******************************************* Dashboard Group **********************************************************
Route::prefix('dashboard/')->middleware(['jwt.verify'])->name('dashboard.')->group(function () {

    //*******************define article
    Route::get('defineArticle', [ArticleController::class, 'create'])->name('defineArticle.create');
    Route::post('storeArticle', [ArticleController::class, 'store'])->name('storeArticle');

    //*******************filtering
    Route::post('filterUsernamesByDepartments', [FilterController::class, 'filterUsernamesByDepartments'])->name('filterUsernamesByDepartments');
    Route::post('filterCategoriesByDepartments', [FilterController::class, 'filterCategoriesByDepartments'])->name('filterCategoriesByDepartments');
    Route::post('filterArticlesByCategoriesDepartments', [FilterController::class, 'filterArticlesByCategoriesDepartments'])->name('filterArticlesByCategoriesDepartments');

    //*******************list articles then edit or delete them or restore them
    /* 1- Does filter by if it is or is not published
     * 2- For sending less data to the front-end we let the user check the isPublished filter checkbox and
     * get the data from the server By Ajax or ....
     */
    Route::get('listArticles/{isPublished}/{showDeleted}', [ArticleController::class, 'index'])->name('listArticles')->where(['isPublished' => '[01]']);
    Route::get('showArticle/{articleID}', [ArticleController::class, 'show'])->name('showArticle');
    Route::get('editArticle/{articleID}/{revisionNumber}', [ArticleController::class, 'edit'])->name('editArticle');
    Route::post("updateArticle/{articleID}", [ArticleController::class, 'update'])->name('updateArticle');
    Route::get('deleteArticle/{articleID}', [ArticleController::class, 'destroy'])->name('softDeleteArticle');
    Route::get('restoreArticle/{articleID}', [ArticleController::class, 'restore'])->name('restoreArticle');
    Route::get('forceDeleteArticle/{articleID}', [ArticleController::class, 'forceDelete'])->name('forceDeleteArticle');

    //*******************delete contributor

    Route::post('deleteContributor', [ArticleController::class, 'deleteContributor'])->name('deleteContributor');

    //*******************List all users/activate users
    Route::get('listUsers/{isNewlyRegistered}', [UserController::class, 'index'])->name('listUsers');
    Route::post('activateUser', [UserController::class, 'activateUsers'])->name('activateUser');

    //*******************logout
    Route::get('logout', [AuthController::class, 'logout']);

    //*******************Account settings
    Route::post("sendEmailVerificationAddress", [VerificationController::class, 'sendEmailVerification'])->name("sendEmailVerificationAddress");


});
//----------------------------------------------- End Of Dashboard Group -----------------------------------------------

//*******************login/register/email verification/change email/reset password
//Route::post('login', [AuthController::class, 'login']);
Route::post('register', [UserController::class, 'store'])->name('register');
Route::get('email/verify/{id}/{hash}', [VerificationController::class, 'clickedEmailVerificationLink'])->name('verification.verified')->middleware(['signed']);
Route::post('login', [AuthController::class, 'login'])->name('login');
Route::post('logout', [AuthController::class, 'logout'])->name('logout');

/*The first route is a form that gets the email and when the user clicks on the link then it goes to the second route and his/her email
 gets updated.*/
Route::post("changeEmail", [VerificationController::class, 'requestChangeEmail'])->name("changeEmail");
Route::get('email/verify/{id}/{newEmail}', [VerificationController::class, 'changeEmail'])->name('verifyChangedEmail');
/*The first one is the form to enter the email address then when the user clicks on the link it goes to the second route with a form
to enter his/her new password then the form would be submitted to the third route and get the user's info updated.*/
Route::post('requestResetPassword', [AuthController::class, 'requestResetPassword'])->name('requestResetPassword');
Route::get('resetPassword/{userID}', [AuthController::class, 'resetPassword'])->name('resetPasswordForm')->middleware(['signed']);
Route::post('resetPassword', [AuthController::class, 'resetPassword'])->name('resetPassword');



//*******************Accept/Reject the invitation (Because no message is going to be composed by the user so this route is out of the dashboard route group.)
Route::get('invitationResponse/{articleID}/{userID}/{parameter}', [ArticleController::class, 'invitationResponse'])->name('invitationResponse')->middleware(['signed']);



Route::get('hello', function (Request $request) {
print_r(\Illuminate\Support\Facades\URL::temporarySignedRoute('verifyChangedEmail', now()->addMinute()));

});
