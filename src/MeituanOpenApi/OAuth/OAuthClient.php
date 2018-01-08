<?php

namespace MeituanOpenApi\OAuth;

use MeituanOpenApi\Config\Config;
use MeituanOpenApi\Exception\IllegalRequestException;
use Exception;

class OAuthClient
{
    private $developerId;
    private $businessId;   //1: 接入团购&闪惠业务 2: 接入外卖业务
    private $secret;
    private $token_url;
    private $authorize_url;
    private $log;
    const  STORE_MAP_API = 'https://open-erp.meituan.com/storemap';

    public function __construct(Config $config)
    {
        $this->config = $config;
        $this->developerId = $config->developerId;
        $this->businessId = $config->businessId;
        $this->secret = $config->get_app_secret();
        $this->token_url = $config->get_request_url() . "/token";
        $this->authorize_url = $config->get_request_url() . "/authorize";
        $this->log = $config->get_log();
    }


    /**
     * 生成授权url
     * @param $ePoiId 客户端唯一标识
     * @param $ePoiName ERP商家门店名
     * @return string
     */
    public function getAuthUrl($ePoiId, $ePoiName = '')
    {
        //获取登录的客户ID
        $query = [
            'developerId' => $this->developerId,
            'businessId' => $this->businessId,
            'ePoiId' => $ePoiId,
            'signKey' => $this->secret,
        ];

        //非必须
        if (!empty($ePoiName)) {
            $query['ePoiName'] = $ePoiName;
        }    

        $queryStr = http_build_query($query);
        return $this->authorizeUri . '?' . $queryStr;
    }


    /**
     * 数字签名.
     *
     * @param $params
     * @return string
     */
    public function signature(&$params)
    {
        $result = $this->secret;

        ksort($params);

        foreach ($params as $key => &$param) {
            $param = is_array($param) ? json_encode($param) : $param;
            $result .= $key.$param;
        }

        return strtolower(sha1($result));
    }


    /**
     * 门店映射
     * @param $ePoiId
     * @return mixed
     */
    public function storemap($ePoiId)
    {
        $query = [
            'developerId' => $this->developerId,
            'businessId' => $this->businessId,
            'ePoiId' => $ePoiId,
            'signKey' => $this->secret,
        ];
        return $this->request(STORE_MAP_API, $query);    
    }


    private function get_headers()
    {
        return array(
            "Content-Type: application/x-www-form-urlencoded; charset=utf-8",
        );
    }

    private function request($url, $body)
    {
        if ($this->log != null) {
            $this->log->info("request data: " . json_encode($body));
        }

        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $this->get_headers());
        curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($body));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        $request_response = curl_exec($ch);
        if (curl_errno($ch)) {
            if ($this->log != null) {
                $this->log->error("error: " . curl_error($ch));
            }
            throw new Exception(curl_error($ch));
        }
        $response = json_decode($request_response);
        if (is_null($response)) {
            throw new Exception("illegal response :" . $request_response);
        }
        if (isset($response->error)) {
            throw new IllegalRequestException(json_encode($response));
        }

        if ($this->log != null) {
            $this->log->info("response: " . json_encode($response));
        }
        return $response;
    }

}

