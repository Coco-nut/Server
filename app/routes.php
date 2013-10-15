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

// TOKEN NEW
Route::post('auth/token', 'AuthController@postAuthToken');
// TOKEN GET
Route::get('auth/token', 'AuthController@getAuthToken');


// GROUP NEW
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


        // user - group (many to many) relation insert
        $myGroup = new MyGroup();
        $myGroup->user_id = $user->id;
        $myGroup->group_id = $group->id;
        $myGroup->save();

        // it's OK

        return Response::json(array('group-id' => $group->id));

    }
);

// GROUP LIST
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
// TODO: GROUP EDIT
// TODO: GROUP DELETE

// TODO: MEMBER NEW (INVITE)
// MEMBER LIST
Route::get(
    'group/{groupId}/members',
    function ($groupId) {
        // TODO: check is my group?
        // #: get list of member on group {number}
        $members = MyGroup::where('group_id', '=', $groupId)->get();

        $users = array();
        // #: get properties of user
        foreach ($members as $member) {
            $user = User::find($member->user_id);
            array_push($users, $user->toArray());
        }
        return Response::json(array('members' => $users));
    }
);
// TODO: MEMBER DELETE
// (DROP MYSELF - DO NOT DROP OTHER USER)

// CARD NEW
Route::post(
    'group/{number}/card/new',
    function ($groupId) {
        $title = Input::get('title');
        $body = Input::get('body');
        $rate = Input::get('rate');
        $labelType = Input::get('label-type');
        $labelText = Input::get('label-text');

        $group = Group::find($groupId)->first();
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
// CARD LIST
Route::get(
    'group/{groupId}/cards',
    function ($groupId) {
        $group = Group::find($groupId)->first();
        // TODO: if not found 404
        $cards = Card::where('group_id', '=', $group->id)->get();
        $resultCards = array();
        foreach ($cards as $card) {
            // TODO: add assigned
            // TODO: add checklists
            // TODO: add comments
            array_push($resultCards, $card->toArray());
        }
        return Response::json(array('cards' => $resultCards));

    }
);

// TODO: CARD MODIFY
// DONE: CARD DELETE
Route::delete(
    '/card/{cardId}',
    function ($cardId) {
        // TODO: auth check
        Comment::destroy($cardId);
    }
);

// DONE: COMMENT NEW
Route::post(
    'card/{cardId}/comment/new',
    function ($cardId) {
        // TODO: check auth
        // TODO: get user and check user is member of card on.
        $token = Request::header('token');
        if (empty($token)) {
            return Response::make('empty token', 400);
        }

        // validate token
        $auth = EmailAuthenticator::where('token', '=', $token)->first();
        if (is_null($auth)) {
            return Response::make('invalid token', 401);
        }

        $message = Input::get('message');
        if (empty($message)) {
            return Response::make('empty message', 400);
        }

        $user = User::find($auth->user_id);

        // TODO: get card
        $card = Card::find($cardId)->first();
        $comment = new Comment();
        $comment->message = $message;
        $comment->user_id = $user->id;
        $comment->card_id = $card->id;
        $comment->save();

        return Response::json(array("comment-id" => $comment->id));
    }
);

// TODO: COMMENT LIST ?
// TODO: COMMENT EDIT ?
// DONE: COMMENT DELETE
Route::delete(
    'comment/{commentId}',
    function ($commentId) {
        // TODO: auth check
        Comment::destroy($commentId);
    }
);

// TODO: ASSIGN NEW
Route::post(
    'card/{cardId}/assigned/new',
    function ($cardId) {
        // TODO : auth check
        // TODO: is user member of my group?
        $userId = Input::get('user-id');

        $card = Group::find($cardId)->first();
        $assigned = new Assigned();
        $assigned->user_id = $userId;
        $assigned->card_id = $card->id;
        $assigned->save();

        return Response::json(array('assigned-id' => $assigned->id));
    }
);

// TODO: ASSIGN LIST ?
// TODO: ASSIGN EDIT ?
// DONE: ASSIGN DELETE
Route::delete(
    'card/{cardId}/assigned/{assignedId}',
    function ($cardId, $assignedId) {
        // TODO: auth check
        Assigned::destroy($assignedId);
    }
);

// DONE: CHECKLIST NEW
Route::post(
    'card/{cardId}/checklist/new',
    function ($cardId) {
        // TODO : auth check
        // TODO: is user member of my group?
        $description = Input::get('description');

        $card = Card::find($cardId)->first();
        $checklist = new Checklist();
        $checklist->card_id = $card->id;
        $checklist->description = $description;
        $checklist->save();

        return Response::json(array('checklist-id' => $checklist->id));
    }
);

// TODO: CHECKLIST LIST ?
// TODO: CHECKLIST EDIT ?
// DONE: CHECKLIST DELETE
Route::delete(
    'checklist/{checklistId}',
    function ($checklistId) {
        // TODO: auth check
        Checklist::destroy($checklistId);
    }
);


