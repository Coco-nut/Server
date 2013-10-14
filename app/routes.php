<?php

/*
|--------------------------------------------------------------------------
| Application Routes
|--------------------------------------------------------------------------
|
| Here is where you can register all of the routes for an application.
| It's a breeze. Simply tell Laravel the URIs it should respond to
| and give it the Closure to execute when that URI is requested.
|
*/
use Illuminate\Support\Facades\Route;
use App\Controllers\AuthController;

Route::post(
    'auth/token',
    function () {
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
);
Route::get(
    'auth/token',
    function () {
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
);


Route::post(
    'groups/new',
    function () {
        // get token
        $token = Request::header('token');
        if (empty($token)) {
            return Response::make('empty token', 400);
        }
        // get Group name
        $groupName = Input::get('name');
        if (empty($groupName)) {
            return Response::make('empty group name', 400);
        }
        // get template id
        $templateId = Input::get('template-id');
        if (empty($templateId)) {
            return Response::make('empty template id', 400);
        }

        // validate token
        $auth = EmailAuthenticator::where('token', '=', $token)->first();
        if (is_null($auth)) {
            return Response::make('invalid token', 401);
        }

        $user = User::find($auth->user_id);

        // Create Group Collection
        $group = new Group();
        $group->name = $groupName;
        $group->template_id = $templateId;
        $group->save();


        // TODO: user - group (many to many) relation insert
        $myGroup = new MyGroup();
        $myGroup->user_id = $user->id;
        $myGroup->group_id = $group->id;
        $myGroup->save();

        // it's OK

        return Response::json(array('group-id' => $group->id));

    }
);

Route::get(
    'groups',
    function () {
        // get token
        $token = Request::header('token');
        if (empty($token)) {
            return Response::make('empty token', 400);
        }
        // validate token
        $auth = EmailAuthenticator::where('token', '=', $token)->first();
        if (is_null($auth)) {
            return Response::make('invalid token', 401);
        }

        $user = User::find($auth->user_id);
        $myGroups = MyGroup::where('user_id', '=', $user->id)->get();
        $groupList = array();
        foreach ($myGroups as $myGroup) {
            $group = Group::find($myGroup->group_id);
            array_push(
                $groupList,
                array(
                    'id' => $group->id,
                    'name' => $group->name,
                    'template-id' => $group->template_id,
                )
            );
        }
        return Response::json(array('groups' => $groupList));
    }
);


Route::get(
    'group/{number}/members',
    function ($number) {
        // TODO: check is my group?
        // TODO: get list of member on group {number}
        $members = MyGroup::where('group_id', '=', $number)->get();
        $users = array();
        foreach ($members as $member) {
            $user = User::find($member->user_id);
            array_push($users, $user->toArray());
        }
        // TODO: get properties of user
        return Response::json(array('members' => $users));
    }
);

Route::get(
    'group/{number}/cards',
    function ($number) {
        $group = Group::find($number)->first();
        // TODO: if not found 404
        $cards = Card::where('group_id', '=', $group->id)->get();
        $resultCards = array();
        foreach ($cards as $card) {
            array_push($resultCards, $card->toArray());
        }
        return Response::json(array('cards' => $resultCards));

    }
);
Route::post(
    'group/{number}/card/new',
    function ($number) {
        $title = Input::get('title');
        $body = Input::get('body');
        $rate = Input::get('rate');
        $labelType = Input::get('label-type');
        $labelText = Input::get('label-text');

        $group = Group::find($number)->first();
        $card = new Card();
        $card->group_id = $group->id;
        $card->title = $title;
        $card->body = $body;
        $card->rate = $rate;
        $card->label_type = $labelType;
        $card->label_text = $labelText;
        $card->save();

        return Response::json(array('card-id' => $card->id));
    }
);
