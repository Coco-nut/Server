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

// TOKEN NEW
Route::post('auth/token', 'AuthController@postAuthToken');
// TOKEN GET
Route::get('auth/token', 'AuthController@getAuthToken');


// GROUP NEW
Route::post('groups/new', 'GroupController@postGroup');
// GROUP LIST
Route::get('groups', 'GroupController@getList');
// TODO: GROUP EDIT
// DONE: GROUP DELETE
Route::delete('/group/{groupId}', 'GroupController@delete');

// TODO: MEMBER NEW (INVITE)
// MEMBER LIST
Route::get('group/{groupId}/members', 'GroupController@getMembers');
// TODO: MEMBER DELETE
// (DROP MYSELF - DO NOT DROP OTHER USER)

// DONE: CARD NEW
Route::post('group/{groupId}/card/new', 'CardController@postCard');
// DONE: CARD LIST
Route::get('group/{groupId}/cards', 'CardController@getCards');
// TODO: CARD MODIFY
// DONE: CARD DELETE
Route::delete('/card/{cardId}', 'CardController@deleteCard');

// DONE: COMMENT NEW
Route::post('card/{cardId}/comment/new', 'CardController@postComment');
// TODO: COMMENT LIST ?
// TODO: COMMENT EDIT ?
// DONE: COMMENT DELETE
Route::delete('comment/{commentId}', 'CardController@deleteComment');

// TODO: ASSIGN NEW
Route::post('card/{cardId}/assigned/new', 'CardController@postAssigned');
// TODO: ASSIGN LIST ?
// TODO: ASSIGN EDIT ?
// DONE: ASSIGN DELETE
Route::delete('card/{cardId}/assigned/{assignedId}', 'CardController@deleteAssigned');

// DONE: CHECKLIST NEW
Route::post('card/{cardId}/checklist/new', 'CardController@postChecklist');
// TODO: CHECKLIST LIST ?
// TODO: CHECKLIST EDIT ?
// DONE: CHECKLIST DELETE
Route::delete('checklist/{checklistId}', 'CardController@deleteChecklist');


