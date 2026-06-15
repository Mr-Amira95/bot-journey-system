<?php

namespace App\Services;

// Manually require library files to ensure environment compatibility without autoloader issues
require_once base_path('vendor/peterujah/php-agora-tokens/src/Agora.php');
require_once base_path('vendor/peterujah/php-agora-tokens/src/Util.php');
require_once base_path('vendor/peterujah/php-agora-tokens/src/Roles.php');
require_once base_path('vendor/peterujah/php-agora-tokens/src/Privileges.php');
require_once base_path('vendor/peterujah/php-agora-tokens/src/User.php');
require_once base_path('vendor/peterujah/php-agora-tokens/src/BaseService.php');
require_once base_path('vendor/peterujah/php-agora-tokens/src/Message.php');
require_once base_path('vendor/peterujah/php-agora-tokens/src/Tokens/AccessToken.php');
require_once base_path('vendor/peterujah/php-agora-tokens/src/Services/Rtc.php');
require_once base_path('vendor/peterujah/php-agora-tokens/src/Builders/RtcToken.php');

use Peterujah\Agora\Agora;
use Peterujah\Agora\User;
use Peterujah\Agora\Roles;
use Peterujah\Agora\Builders\RtcToken;

class AgoraTokenService
{
    private ?string $appId;
    private ?string $appCertificate;

    public function __construct()
    {
        $this->appId          = config('services.agora.app_id');
        $this->appCertificate = config('services.agora.certificate');
    }

    /**
     * Build RTC Token using User UID
     *
     * @param string $channelName
     * @param int $uid
     * @param int $expireSeconds
     * @return string|null
     */
    public function buildTokenWithUid(
        string $channelName,
        int    $uid,
        int    $expireSeconds = 7200
    ): ?string {
        if (empty($this->appId) || empty($this->appCertificate)) {
            return null;
        }

        try {
            $client = new Agora($this->appId, $this->appCertificate);
            $client->setExpiration(time() + $expireSeconds);

            $user = new User($uid);
            $user->setChannel($channelName);
            $user->setRole(Roles::RTC_PUBLISHER);
            $user->setPrivilegeExpire(time() + $expireSeconds);

            return RtcToken::buildTokenWithUid($client, $user);
        } catch (\Exception $e) {
            \Log::error('[AgoraTokenService] Token generation failed: ' . $e->getMessage());
            return null;
        }
    }
}
