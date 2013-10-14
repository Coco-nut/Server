<?php
/**
 * Created by PhpStorm.
 * User: tael
 * Date: 13. 10. 11.
 * Time: 오후 2:55
 */
namespace App\Controllers;
use Illuminate\Support\Facades\Hash;

class AuthController extends BaseController
{
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