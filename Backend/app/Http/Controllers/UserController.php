<?php

namespace App\Http\Controllers;

use App\Http\Services\UserServices;
use Illuminate\Http\Request;

class UserController extends Controller
{
    private $userServices;

    public function __construct(UserServices $userServices)
    {
        $this->userServices = $userServices;
    }

    public function createUser(Request $request){
        return $this->userServices->createUser($request);
    }

    public function login(Request $request){
        return $this->userServices->login($request);
    }

    public function updateUser(Request $request){
        return $this->userServices->updateUser($request);
    }

    public function isAuthenticated(){
        return $this->userServices->isAuthenticated();
    }

    public function logout(Request $request){
        return $this->userServices->logout($request);
    }

}
