<?php
/**
 * Created by PhpStorm.
 * User: tael
 * Date: 13. 10. 15.
 * Time: 오후 4:14
 */

class GroupController extends BaseController
{
    public static function getMembers($groupId)
    {
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

    public static function delete($groupId)
    {
        // TODO: auth check
        Group::destroy($groupId);

    }

    public static function getList()
    {
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

    public static function postGroup()
    {
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
} 