<?php

class AuthManager {
    public function getToken($username, $password){
        $userManager = new UserManager();
        if ($username != '' || $password != ''){               
            $userLogedIn = $userManager->loginNew($username, $password);
                
            if ($userLogedIn != false){
                // Create token header as a JSON string
                $header = json_encode(['typ' => 'JWT', 'alg' => 'HS256']);
                // Create token payload as a JSON string
                $payload = json_encode(['user_id' => $userLogedIn]);
                // Encode Header to Base64Url String
                $base64UrlHeader = str_replace(['+', '/', '='], ['-', '_', ''], base64_encode($header));
                // Encode Payload to Base64Url String
                $base64UrlPayload = str_replace(['+', '/', '='], ['-', '_', ''], base64_encode($payload));
                // Create Signature Hash
                $signature = hash_hmac('sha256', $base64UrlHeader . "." . $base64UrlPayload, 'abC123!', true);
                // Encode Signature to Base64Url String
                $base64UrlSignature = str_replace(['+', '/', '='], ['-', '_', ''], base64_encode($signature));
                // Create JWT
                $jwt = $base64UrlHeader . "." . $base64UrlPayload . "." . $base64UrlSignature;
                
                return $jwt;
            }
        }
        return false;
    }

    public function deleteToken($token){
        Db::command ('DELETE FROM tokens WHERE token=?', array ($token));
        return true;
    }

    public function validateToken($token){
        $tokens = Db::loadAll('SELECT * FROM tokens WHERE token = ? AND expire >= CURRENT_TIMESTAMP AND blocked = 0;', array($token));
        if (count($tokens) == 1) {
			return true;
        } else if (count($tokens) == 0) {
            return false;
        };
        return false;
    }
}
