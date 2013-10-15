<?php
/**
 * Created by PhpStorm.
 * User: tael
 * Date: 13. 10. 11.
 * Time: 오후 2:55
 */

class AuthController extends BaseController
{
    public static function getAuthToken()
    {
        $email = Request::header('email');
        $passPhrase = Request::header('pass-phrase');
        if (empty($email)) {
            return Response::make('empty email', 400);
        }
        if (empty($passPhrase)) {
            return Response::make('empty pass', 400);
        }
        $auth = EmailAuthenticator::where('email', '=', $email)->first();

        if (is_null($auth)) {
            return Response::make('need to create', 404);
        }
        // if already exists
        // if not valid
        if ($auth->pass_phrase !== AuthController::getHashedPassPhrase($passPhrase, $auth->salt)) {
            return Response::make(' | password mismatched', 401);
        }
        // it's OK
        return Response::json(array('token' => $auth->token));

    }
    public static function postAuthToken()
    {
        $email = Request::header('email');
        $passPhrase = Request::header('pass-phrase');
        if (empty($email)) {
            return Response::make('empty email', 400);
        }
        if (empty($passPhrase)) {
            return Response::make('empty pass', 400);
        }
        $name = Input::get('name');
        if (empty($name)) {
            return Response::make('empty name', 400);
        }
        $phoneNumber = Input::get('phone-number');
        if (empty($phoneNumber)) {
            return Response::make('empty phone number', 400);
        }
        $isAuthExists = EmailAuthenticator::where('email', '=', $email)->exists();

        // if already exists
        if ($isAuthExists) {
            return Response::make('already exists', 403);
        }
        // if not exists, INSERT User
//        $user = User::create(array('name' => $name));
//        $user->push();
        $user = new User();
        $user->name = $name;
        $user->phone_number = $phoneNumber;
        $user->save();

        $newSalt = AuthController::getNewSalt();
        $hashedPassPhrase = AuthController::getHashedPassPhrase($passPhrase, $newSalt);
        $newToken = AuthController::getNewToken($email, $hashedPassPhrase, $newSalt);
        $auth = new EmailAuthenticator();
        $auth->email = $email;
        $auth->pass_phrase = $hashedPassPhrase;
        $auth->salt = $newSalt;
        $auth->token = $newToken;
        $auth->user_id = $user->id;
        $auth->save();

        // it's OK
        return Response::json(array('token' => $auth->token));

    }

    public static function getNewSalt()
    {
        return base_convert(rand(100000, 1000000), 10, 36);
    }

    public static function getNewToken($email, $passPhrase, $salt)
    {
        return sha1($email . $passPhrase . $salt);
    }

    public static function getHashedPassPhrase($passPhrase, $salt)
    {
        return md5($passPhrase . $salt);
    }
}