<?php

namespace Novanova\SocialHub\AppCenter;

use Exception;


/**
 * Class AppCenterException
 * @package Novanova\SocialHub\AppCenter
 */
class AppCenterException extends Exception {

    const APP_CENTER_API_ERROR = 4401;
    const NO_RND_FIELD = 4402;
    const CURL_ERROR = 4403;

    /**
     * @param int $errno
     * @throws AppCenterException
     */
    public static function throwException($errno)
    {
        throw new self(self::description($errno), $errno);
    }

    /**
     * @param int $errno
     * @return string
     */
    public static function description($errno)
    {
        $description = [];

        $description[self::APP_CENTER_API_ERROR] = 'appcenter api error';
        $description[self::NO_RND_FIELD] = 'no rnd field';
        $description[self::CURL_ERROR] = 'curl error';

        return isset($description[$errno]) ? $description[$errno] : 'unknown error';
    }
} 