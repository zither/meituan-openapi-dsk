<?php

namespace MeituanOpenApi\Protocol;
use MeituanOpenApi\Config\Config;
use MeituanOpenApi\Exception\BusinessException;
use Exception;

class RpcClient
{
    private $signKey;
    private $api_request_url;
    private $token;
    private $log;

    public function __construct($token, Config $config)
    {
        $this->signKey = $config->getSignKey();
        $this->api_request_url = $config->get_request_url();
        // $this->api_request_url = $config->get_request_url() . "/api/v1";
        $this->log = $config->get_log();
        $this->token = $token;
    }


    /**
     * 获取app_key
     * @return mixed
     */
    public function getSignKey()
    {
        return $this->signKey;
    }


    /**
     * 获取token
     * @return mixed
     */
    public function getToken()
    {
        return $this->token;
    }


    /** call server api with nop
     * @param $method
     * @param $action
     * @param array $parameters
     * @param array $header
     * @return mixed
     * @throws BusinessException
     * @throws Exception
     */
    public function call($method, $action, $parameters = [], $header = [])
    {
        //url
        $url = $this->api_request_url . $action;

        //系统参数
        $protocol = array(
            "appAuthToken" => $this->token,
            "charset" => 'UTF-8',
            "timestamp" => time(),
            "version" => '1',
        );

        //签名sign
        $signParams = array_merge($protocol, $parameters);
        $protocol['sign'] = $signParams['sign'] = $this->generateSignature($signParams);

        //系统级参数在 POST 请求中也需要以 URL 参数的方式传递（http://developer.meituan.com/openapi#3.3）
        $queryParams = $method == 'get' ? $signParams : $protocol;
        $url = sprintf('%s?%s', $url, $this->buildQuery($queryParams));

        if ($method == 'get') {
            $result = $this->get($url, $header);
        } else {
            $result = $this->post($url, $parameters, $header);
        }

        $response = json_decode($result, false, 512, JSON_BIGINT_AS_STRING);
        if (is_null($response)) {
            throw new Exception("invalid response.");
        }

        //抛出错误信息
        if (isset($response->error)) {
            if (isset($response->error->error_type) && isset($response->error->message)) {
                throw new BusinessException($response->error->error_type . ' : ' . $response->error->message);
            }

        }

        if (isset($response->msg)) {
            throw new BusinessException($response->code . ' : ' . $response->msg);
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
        $aliParams = [];
        foreach ($protocol as $key => $value) {
            $value = is_array($value) ? json_encode($value, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) : $value;
            $aliParams[] = $key . $value;
        }
        $signStr = $this->signKey . implode('', $aliParams);

        //sha1处理，字符串小写开头
        return strtolower(sha1($signStr));
    }


    /**
     * 参数url编码
     * @param $params
     * @return mixed
     */
    private function urlencodeParams($params)
    {
        foreach ($params as $key=>&$val) {
            urlencode($val);
        }
        return $params;
    }

    /**
     * http_build_query 简单实现
     * @param array $params
     * @param bool $urlencode
     * @return string
     * @see http://php.net/manual/en/function.http-build-query.php#60523
     */
    protected function buildQuery($params, $urlencode = true)
    {
        $queryParams = [];
        foreach ($params as $key => $value) {
            if ($urlencode) {
                $key = urlencode($key);
                $value = urlencode($value);
            }
            $queryParams[] = sprintf('%s=%s', $key, $value);
        }
        return implode('&', $queryParams);
    }


    /**
     * get请求
     * @param $url
     * @param $header
     * @return mixed
     * @throws Exception
     */
    private function get($url, $header)
    {
        //头部设置
        $header = !empty($header) ? $header : array("Content-type: x-www-form-urlencoded");

        //发起curl请求
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_POST, 0);

        //设置头部信息
        if (!empty($header)) {
            curl_setopt($ch, CURLOPT_HTTPHEADER, $header);
        }

        //设置获取的信息以文件流的形式返回，而不是直接输出
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);

        //设置头文件的信息,不作为数据流输出
        curl_setopt($ch, CURLOPT_HEADER, 0);

        //设置cURL允许执行的最长秒数(请求超时时间)
        curl_setopt($ch, CURLOPT_TIMEOUT, 30);

        $response = curl_exec($ch);

        $log = $this->log;
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


    /**
     * post请求
     * @param $url
     * @param $data
     * @param $header
     * @return mixed
     * @throws Exception
     */
    private function post($url, $data, $header)
    {
        $log = $this->log;
        if ($log != null) {
            $log->info("request data: " . json_encode($data));
        }

        //请求参数中有中文时，中文需要经过url编码
        $data = $this->urlencodeParams($data);

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
