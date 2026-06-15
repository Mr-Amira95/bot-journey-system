<?php

namespace App\Services;

/**
 * Agora RTC Token Builder (AccessToken v1 — "006" prefix).
 * Implements the algorithm from https://github.com/AgoraIO/Tools/tree/master/DynamicKey/AgoraDynamicKey/php
 */
class AgoraTokenService
{
    const VERSION = '006';

    // Privilege keys
    const PRIV_JOIN_CHANNEL  = 1;
    const PRIV_PUBLISH_AUDIO = 2;
    const PRIV_PUBLISH_VIDEO = 3;
    const PRIV_PUBLISH_DATA  = 4;

    // Roles
    const ROLE_PUBLISHER  = 1;
    const ROLE_SUBSCRIBER = 2;

    private string $appId;
    private string $appCertificate;

    public function __construct()
    {
        $this->appId          = config('services.agora.app_id');
        $this->appCertificate = config('services.agora.certificate');
    }

    public function buildTokenWithUid(
        string $channelName,
        int    $uid,
        int    $role          = self::ROLE_PUBLISHER,
        int    $expireSeconds = 7200
    ): string {
        $expireTs = time() + $expireSeconds;
        $salt     = rand(1, 99999999);
        $ts       = time();
        $userStr  = strval($uid);

        // Build privilege map
        $privileges = [
            self::PRIV_JOIN_CHANNEL  => $expireTs,
            self::PRIV_PUBLISH_AUDIO => $expireTs,
            self::PRIV_PUBLISH_VIDEO => $expireTs,
            self::PRIV_PUBLISH_DATA  => $expireTs,
        ];
        ksort($privileges);

        // Pack the message (little-endian)
        $msg  = pack('V', $salt);                   // uint32 LE
        $msg .= pack('V', $ts);                     // uint32 LE
        $msg .= pack('v', count($privileges));      // uint16 LE
        foreach ($privileges as $key => $value) {
            $msg .= pack('v', $key);                // uint16 LE
            $msg .= pack('V', $value);              // uint32 LE
        }

        // Signature = HMAC-SHA256(appId + channelName + uid + msg, appCertificate)
        $val = $this->appId . $channelName . $userStr . $msg;
        $sig = hash_hmac('sha256', $val, $this->appCertificate, true); // raw 32 bytes

        // CRC values
        $crcChannel = crc32($channelName) & 0xffffffff;
        $crcUser    = crc32($userStr)     & 0xffffffff;

        // Content = packString(sig) + uint32(crcChannel) + uint32(crcUser) + msg
        $content  = pack('v', strlen($sig)) . $sig;   // uint16-prefixed string
        $content .= pack('V', $crcChannel);
        $content .= pack('V', $crcUser);
        $content .= $msg;

        return self::VERSION . $this->appId . base64_encode($content);
    }
}
