<?php

namespace UnzerSDK\Services;

class JwtService
{
    const Expiry_Buffer = 60;

    public static function validateExpireTime(string $jwt, int $expiryBufferSeconds = self::Expiry_Buffer): bool
    {
        $jwtData = self::extractPayload($jwt);
        $expireTime = $jwtData['exp'];
        $currentTime = time();
        return ($expireTime - $expiryBufferSeconds) > $currentTime;
    }

    private static function extractPayload(string $jwt)
    {
        $tokenSegments = explode('.', $jwt);
        return json_decode(base64_decode($tokenSegments[1]), true);
    }
}