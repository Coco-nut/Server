<?php
/**
 * Created by PhpStorm.
 * User: tael
 * Date: 13. 10. 15.
 * Time: 오후 5:31
 */

class CardController extends BaseController
{
    public static function postChecklist($cardId)
    {
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
    public static function deleteChecklist($checklistId)
    {
        // TODO: auth check
        Checklist::destroy($checklistId);
    }
    public static function deleteAssigned($cardId, $assignedId)
    {
        // TODO: auth check
        Assigned::destroy($assignedId);
    }

    public static function postAssigned($cardId)
    {
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

    public static function deleteComment($commentId)
    {
        // TODO: auth check
        Comment::destroy($commentId);
    }

    public static function postComment($cardId)
    {
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

    public static function deleteCard($cardId)
    {
        // TODO: auth check
        Card::destroy($cardId);

    }

    public static function getCards($groupId)
    {
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

    public static function postCard($groupId)
    {
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
} 