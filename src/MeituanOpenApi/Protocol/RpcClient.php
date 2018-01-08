<?php

namespace MeituanOpenApi\Protocol;

use MeituanOpenApi\Config\Config;
use MeituanOpenApi\Exception\BusinessException;
use MeituanOpenApi\Exception\ExceedLimitException;
use MeituanOpenApi\Exception\IllegalRequestException;
use MeituanOpenApi\Exception\InvalidSignatureException;
use MeituanOpenApi\Exception\InvalidTimestampException;
use MeituanOpenApi\Exception\PermissionDeniedException;
use MeituanOpenApi\Exception\ServerErrorException;
use MeituanOpenApi\Exception\UnauthorizedException;
use MeituanOpenApi\Exception\ValidationFailedException;
use Exception;

class RpcClient
{

    private $app_key;
    private $app_secret;
    private $api_request_url;
    private $token;
    private $log;

    public function __construct($token, Config $config)
    {
        $this->app_key = $config->get_app_key();
        $this->app_secret = $config->get_app_secret();
        $this->api_request_url = $config->get_request_url();
        // $this->api_request_url = $config->get_request_url() . "/api/v1";
        $this->log = $config->get_log();
        $this->token = $token;
    }

    /** call server api with nop
     * @param $method
     * @param $action
     * @param array $parameters
     * @param array $header
     * @param $is_merge  
     * @return mixed
     * @throws BusinessException
     * @throws Exception
     */
    public function call($method, $action, $parameters, $header = [], $is_merge = true)
    {
        //url
        $url = $this->api_request_url . $action;

        //系统参数
        $protocol = array(
            "appAuthToken" => $this->token->access_token,
            "charset" => 'UTF-8',
            "timestamp" => time(),
            "version" => '1',
        );

        //是否合并应用参数
        if ($is_merge) {
            $protocol = array_merge($protocol, $parameters);
        } 

        //签名sign
        $protocol['sign'] = $this->generateSignature($protocol);

        if ($method == 'get') { //get
            $result = $this->get($url, $protocol, $header);
        } else { //post
            $result = $this->post($url, $protocol, $header);
        } 

        $response = json_decode($result, false, 512, JSON_BIGINT_AS_STRING);
        if (is_null($response)) {
            throw new Exception("invalid response.");
        }

        //抛出错误信息
        if ($response->code != 0) {
            if (isset($response->error_type) && isset($response->message)) {
                throw new BusinessException($response->error_type.' : '.$response->message);
            }
        }
        return $response->data;
    }


    /**
     * 数字签名
     */
    private function generateSignature($protocol)
    {
        //键值字典排序
        ksort($protocol);

        //拼接字符成字符串
        $aliParams =[];
        foreach ($protocol as $key => $value) {
            $value = is_array($value) ? json_encode($value, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) : '';
            $aliParams[] = $key . $value;
        }
        $signStr =  $this->app_secret . implode('', $aliParams);

        //sha1处理，字符串小写开头
        return strtolower(sha1($signStr));
    }



    private function get($url, $data, $header)
    {
        $log = $this->log;
        if ($log != null) {
            $log->info("request data: " . json_encode($data));
        }

        //头部设置    
        $header = !empty($header) ? $header : array("Content-type: x-www-form-urlencoded");

        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_POST, 0);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $header);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
        curl_setopt($ch, CURLOPT_TIMEOUT, 30);

        $response = curl_exec($ch);

        //错误信息    
        if (curl_errno($ch)) {
            if ($log != null) {
                $log->error("error: " . curl_error($ch));
            }
            throw new Exception(curl_error($ch));
        }

        if ($log != null) {
            $log->info("response: " . $response);
        }

        //关闭cURL资源，并且释放系统资源
        curl_close($ch);

        return $response;
    }



    private function post($url, $data, $header)
    {
        $log = $this->log;
        if ($log != null) {
            $log->info("request data: " . json_encode($data));
        }

        //头部设置    
        $header = !empty($header) ? $header : array("Content-type: x-www-form-urlencoded");

        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $header);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
        curl_setopt($ch, CURLOPT_TIMEOUT, 30);

        $response = curl_exec($ch);

        //错误信息    
        if (curl_errno($ch)) {
            if ($log != null) {
                $log->error("error: " . curl_error($ch));
            }
            throw new Exception(curl_error($ch));
        }

        if ($log != null) {
            $log->info("response: " . $response);
        }

        //关闭cURL资源，并且释放系统资源
        curl_close($ch);

        return $response;
    }

}
