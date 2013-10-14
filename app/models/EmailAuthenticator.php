<?php
/**
 * Created by PhpStorm.
 * User: tael
 * Date: 13. 10. 11.
 * Time: 오전 1:56
 */
use Illuminate\Database\Eloquent\Model;

class EmailAuthenticator extends Model
{
    protected $table = 'auth_email';
    protected $hidden = array('pass_phrase');

}
