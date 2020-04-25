<?php
class AuthApi {
    static function login(){
        $token = (new ApiManager)->getToken($this->input->username,$this->input->password);
        if (!$token) {
            throw new Exception("Auth failed", 401);
        }
        $this->response(['token' => $token]);
    }

    static function logout(){
        $authenticationBearrer = $_SERVER['HTTP_AUTHORIZATION'];
        if (!(new ApiManager)->deleteToken($authenticationBearrer)) {
            throw new Exception("logout Failed", 401);
        }
    }

    static function registration(){
        
    }

    static function restartPassword(){
        
    }
} 