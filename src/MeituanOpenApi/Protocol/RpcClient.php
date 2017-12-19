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
     * @param $action
     * @param array $parameters
     * @return mixed
     * @throws BusinessException
     * @throws Exception
     */
    public function call($action, array $parameters)
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

        //合并应用参数
        $protocol = array_merge($protocol, $parameters);

        //签名sign
        $protocol['sign'] = $this->generate_signature($protocol);

        $result = $this->post($url, $protocol);
        $response = json_decode($result, false, 512, JSON_BIGINT_AS_STRING);
        if (is_null($response)) {
            throw new Exception("invalid response.");
        }

        if (isset($response->code) && isset($response->msg)) {
            throw new BusinessException($response->code.' : '.$response->msg);
        }

        return $response->data;
    }


    private function generate_signature($protocol)
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


    private function post($url, $data)
    {
        $log = $this->log;
        if ($log != null) {
            $log->info("request data: " . json_encode($data));
        }

        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array("Content-type: x-www-form-urlencoded"));
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
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

        // //状态码
        // $status_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        // if ($status_code != 0) {

        // }    

        //关闭cURL资源，并且释放系统资源
        curl_close($ch);

        return $response;
    }


    function meituanCurlData($url, $params = [])
{
    // 创建一个cURL资源
    $ch = curl_init();

    // 设置URL和相应的选项
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_HEADER, 0);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);

    //判断是否是HTTPS请求
    if (strlen($url) > 5 && strtolower(substr($url, 0, 5)) == "https") {
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
    }

    if (!empty($params)) {
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $params);
    }
    curl_setopt($ch, CURLOPT_TIMEOUT, 30);

    // 抓取URL并把它传递给浏览器
    $curlInfo = curl_exec($ch);

    // 关闭cURL资源，并且释放系统资源
    curl_close($ch);

    return $curlInfo;
}

//生成签名
function generateSign($params)
{
    $aliParams =[];
    foreach ($params as $key => $val) {
        $aliParams[] = $key . $val;
    }

    sort($aliParams);
    $signStr =  meituan_getSecret() . join('', $aliParams);
    return strtolower(sha1($signStr));
}


}
