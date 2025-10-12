<?php

namespace app\Services;
use core\Response;
use app\Models\UserModel;

class JwtService
{
    // TODO: load secret key from .env file.
    private string $secretKey='';

    public function __construct()
    {
        $this->secretKey = 'unutma ki dunya fani!*#';
    }

    public function encode(UserModel $user): string
    {
        // hash found user
        // create header for encryption.
        $header = json_encode(['alg' => 'HS256','typ' => 'JWT']);

        // create token body   for encryption.
        // TODO: add iss (issuer) and aud(audience), iat (issued at), nbf to the token!
        $payload = json_encode(['exp'=> time()+3600, 'sub'=> $user->id,'username'=> $user->user_name]);

        // encrypt header and Url-Safe encrpted string.
        $base64UrlHeader = rtrim(strtr(base64_encode($header),'+/','-_'),'=');

        // encrypt data body as payload as above
        $base64UrlPayload = rtrim(strtr(base64_encode($payload),'+/','-_'),'=');

        $signature = hash_hmac('sha256',$base64UrlHeader.".".$base64UrlPayload,$this->secretKey,true);
        $base64UrlSignature = rtrim(strtr(base64_encode($signature),'/+','-_'),'=');

        $jwt = $base64UrlHeader. "." . $base64UrlPayload. "." .$base64UrlSignature;

        return $jwt;
    }

    public function decode(string $token)
    {
        if (empty($token))
        {
            return Response::json('token is empty!');
        }
        // divide token to three section.
        $token = explode('.',$token);

        // token must have 3 parts!
        if (count($token) !==3)
        {
            return false;
        }

        $base64UrlHeader = $token[0];
        $base64UrlPayload = $token[1];
        $providedSignature = $token[2];

        // 1. Verify the signature
        $signingInput = $base64UrlHeader . '.' . $base64UrlPayload;
        $expectedSignature = hash_hmac('sha256', $signingInput, $this->secretKey, true);
        $base64UrlExpectedSignature = rtrim(strtr(base64_encode($expectedSignature),'/+','-_'),'=');

        if (!hash_equals($base64UrlExpectedSignature, $providedSignature)) {
            return Response::json(['error'=>'token mismatch'],401); // Signature mismatch
        }

        //2. Decode and validate payload.
        // decode base64UrlDeocd in array with true option
        $decodedPayload = json_decode($this->base64UrlDecode($base64UrlPayload), true);

        // check expiration time to set
        if(!isset($decodedPayload['exp']))
        {
            return Response::json(['error'=>'Expiration time not set'],401);
        }
        // for testing purpose
        if($decodedPayload['exp']>time())
        {
            return Response::json(['error'=>'your token has been expired'], 401);
        }

        return ['sub' => $decodedPayload['sub'],'username'=> $decodedPayload['username'], 'exp' => $decodedPayload['exp']];
    }

    function base64UrlDecode(string $data): string
    {
        // Add padding back to make it standard base64
        $data = str_pad($data, strlen($data) % 4, '=', STR_PAD_RIGHT);
        // Reverse the character replacements
        $data = strtr($data, ['-' => '+', '_' => '/']);
        // Perform standard base64 decoding
        return base64_decode($data);
    }

    public function base64UrlEncode(string $data): string
    {
        // TODO: Add encode function to reduce code size! :)
    }

}