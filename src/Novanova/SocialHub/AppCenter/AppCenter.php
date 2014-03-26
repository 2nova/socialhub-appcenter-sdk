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
    private $api_url = "https://socialhub.dev/api/v1";

    /**
     * @param int $app_id
     * @param string $secret
     * @param string|null $api_url
     */
    public function __construct($app_id, $secret, $api_url = null)
    {
        $this->app_id = $app_id;
        $this->secret = $secret;

        if ($api_url) {
            $this->api_url = $api_url;
        }
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

        $response = file_get_contents($this->api_url . '?' . http_build_query($params));

        $response = json_decode($response);
        if (!$response || JSON_ERROR_NONE !== json_last_error()) {
            throw new AppCenterException('App Center API error');
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
            throw new AppCenterException('No rnd field');
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