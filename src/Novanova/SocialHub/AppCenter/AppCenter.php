<?php

namespace Novanova\SocialHub\AppCenter;


/**
 * Class AppCenter
 * @package Novanova\SocialHub\AppCenter
 */
class AppCenter
{

    /**
     * @var int
     */
    private $app_id;

    /**
     * @var string
     */
    private $secret;

    /**
     * @var string
     */
    private $api_url;

    /**
     * @var bool
     */
    private $verify_ssl;

    /**
     * @param int $app_id
     * @param string $secret
     * @param string $api_url
     * @param bool $verify_ssl
     */
    public function __construct($app_id, $secret, $api_url, $verify_ssl = true)
    {
        $this->app_id = $app_id;
        $this->secret = $secret;
        $this->api_url = $api_url;
        $this->verify_ssl = $verify_ssl;
    }


    /**
     * @param string $method
     * @param array $params
     * @return mixed|string
     * @throws AppCenterException
     */
    public function api($method, array $params)
    {
        $params['rnd'] = sha1(uniqid(microtime()));
        $params['app_id'] = $this->app_id;
        $params['method'] = $method;
        $params['sign'] = $this->sign($params);

        $ch = curl_init($this->api_url);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($params));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, $this->verify_ssl ? 2 : 0);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, $this->verify_ssl ? 1 : 0);
        $response = curl_exec($ch);

        if (false === $response) {
            throw new AppCenterException(curl_error($ch), AppCenterException::CURL_ERROR);
        }

        curl_close($ch);

        $response = json_decode($response);
        if (!$response || JSON_ERROR_NONE !== json_last_error()) {
            AppCenterException::throwException(AppCenterException::APP_CENTER_API_ERROR);
        }

        if (empty($response->success)) {
            throw new AppCenterException(
                empty($response->error) ? 'unknown error' : $response->error,
                empty($response->errno) ? 0 : (int)$response->errno
            );
        } else {
            $data = $response->data;
        }

        return $data;

    }


    /**
     * @param int $viewer_id
     * @return string
     */
    public function calculateAuthKey($viewer_id)
    {
        return sha1($this->app_id . '_' . $viewer_id . '_' . $this->secret);
    }


    /**
     * @param array $data
     * @return string
     * @throws AppCenterException
     */
    public function sign(array $data)
    {
        if (!isset($data['rnd']) || !$data['rnd']) {
            AppCenterException::throwException(AppCenterException::NO_RND_FIELD);
        }
        $sign = '';
        ksort($data);
        foreach ($data as $key => $value) {
            $sign .= $key . '=' . $value;
        }
        $sign .= $this->secret;
        return sha1($sign);
    }

} 